<?php

namespace App\Services\Registration;

use App\Models\Tenant\Registration\ParticipantProfile;
use App\Models\Tenant\Registration\Program;
use App\Models\Tenant\Registration\ProgramBatch;
use App\Models\Tenant\Registration\ProgramRegistration;
use App\Models\Tenant\Location;
use App\Models\Tenant\Order;
use App\Models\User;
use App\Services\Orders\OrderService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProgramRegistrationService
{
    private const OPEN_STATUSES = ['active', 'on_hold'];

    public function __construct(private OrderService $orders) {}

    public function paginate(array $filters)
    {
        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 20)));

        return ProgramRegistration::query()
            ->with([
                'participant:id,customer_id,participant_code,display_name,first_name,last_name,status',
                'participant.customer:id,name,phone,email',
                'program:id,product_id,program_type,status',
                'program.product:id,name,sku,price',
                'batch:id,program_id,name,start_time,end_time,status',
                'order:id,order_no,invoice_no,status,payment_status,total,paid_amount,balance_due',
            ])
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($filters['program_id'] ?? null, fn ($query, $value) => $query->where('program_id', $value))
            ->when($filters['program_batch_id'] ?? null, fn ($query, $value) => $query->where('program_batch_id', $value))
            ->when($filters['participant_profile_id'] ?? null, fn ($query, $value) => $query->where('participant_profile_id', $value))
            ->when($filters['search'] ?? null, function ($query, $value) {
                $query->where(function ($search) use ($value) {
                    $search->where('registration_number', 'like', "%{$value}%")
                        ->orWhereHas('participant', fn ($participant) => $participant
                            ->where('display_name', 'like', "%{$value}%")
                            ->orWhere('participant_code', 'like', "%{$value}%"))
                        ->orWhereHas('program.product', fn ($product) => $product
                            ->where('name', 'like', "%{$value}%")
                            ->orWhere('sku', 'like', "%{$value}%"));
                });
            })
            ->latest('registered_on')
            ->latest('id')
            ->paginate($perPage);
    }

    public function overview(): array
    {
        return [
            'counts' => [
                'active_registrations' => ProgramRegistration::where('status', 'active')->count(),
                'participants' => ParticipantProfile::whereNull('archived_at')->count(),
                'active_programs' => Program::where('status', 'active')->whereNull('archived_at')->count(),
                'active_batches' => ProgramBatch::where('status', 'active')->whereNull('archived_at')->count(),
            ],
            'recent_registrations' => ProgramRegistration::query()
                ->with([
                    'participant:id,participant_code,display_name',
                    'program:id,product_id',
                    'program.product:id,name,sku',
                    'batch:id,name',
                    'order:id,order_no,invoice_no,status,payment_status,total',
                ])
                ->latest('registered_on')
                ->latest('id')
                ->limit(8)
                ->get(),
        ];
    }

    public function create(array $data, User $actor): ProgramRegistration
    {
        return DB::connection('tenant')->transaction(function () use ($data, $actor) {
            $participant = ParticipantProfile::findOrFail((int) $data['participant_profile_id']);
            $program = Program::with('product')->findOrFail((int) $data['program_id']);
            $batch = $this->resolveBatch($program, $data['program_batch_id'] ?? null);

            if ($participant->archived_at || $participant->status !== 'active') {
                throw ValidationException::withMessages(['participant_profile_id' => 'Select an active participant.']);
            }
            if ($program->archived_at || $program->status !== 'active') {
                throw ValidationException::withMessages(['program_id' => 'Select an active program.']);
            }
            if (ProgramRegistration::where('participant_profile_id', $participant->id)
                ->where('program_id', $program->id)
                ->whereIn('status', self::OPEN_STATUSES)
                ->exists()) {
                throw ValidationException::withMessages(['participant_profile_id' => 'This participant already has an active registration for the selected program.']);
            }

            $dates = $this->dates($program, $data);
            $amounts = $this->amounts($program, $data);

            return ProgramRegistration::create([
                'registration_number' => $this->nextNumber(),
                'participant_profile_id' => $participant->id,
                'program_id' => $program->id,
                'program_batch_id' => $batch?->id,
                ...$dates,
                ...$amounts,
                'status' => $data['status'] ?? 'active',
                'notes' => $data['notes'] ?? null,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ])->load(['participant.customer', 'program.product', 'batch']);
        });
    }

    public function update(ProgramRegistration $registration, array $data, User $actor): ProgramRegistration
    {
        return DB::connection('tenant')->transaction(function () use ($registration, $data, $actor) {
            $registration->loadMissing('program.product');
            if (array_key_exists('program_batch_id', $data)) {
                $batch = $this->resolveBatch($registration->program, $data['program_batch_id']);
                $data['program_batch_id'] = $batch?->id;
            }

            $merged = array_merge($registration->toArray(), $data);
            $startsOn = ! empty($merged['starts_on']) ? Carbon::parse($merged['starts_on']) : null;
            $endsOn = ! empty($merged['ends_on']) ? Carbon::parse($merged['ends_on']) : null;
            if ($startsOn && $endsOn && $endsOn->lt($startsOn)) {
                throw ValidationException::withMessages(['ends_on' => 'End date must be on or after start date.']);
            }

            $fee = round((float) ($merged['fee_amount'] ?? 0), 2);
            $discount = round((float) ($merged['discount_amount'] ?? 0), 2);
            if ($discount > $fee) {
                throw ValidationException::withMessages(['discount_amount' => 'Discount cannot exceed the fee amount.']);
            }

            $registration->update([
                ...collect($data)->only(['program_batch_id', 'registered_on', 'starts_on', 'ends_on', 'fee_amount', 'discount_amount', 'status', 'notes'])->all(),
                'final_amount' => round($fee - $discount, 2),
                'updated_by' => $actor->id,
            ]);

            return $registration->fresh()->load(['participant.customer', 'program.product', 'batch']);
        });
    }

    public function cancel(ProgramRegistration $registration, User $actor): ProgramRegistration
    {
        if ($registration->status === 'cancelled') {
            return $registration->load(['participant.customer', 'program.product', 'batch']);
        }

        $registration->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'updated_by' => $actor->id,
        ]);

        return $registration->fresh()->load(['participant.customer', 'program.product', 'batch']);
    }

    public function generateOrder(ProgramRegistration $registration, User $actor): Order
    {
        return DB::connection('tenant')->transaction(function () use ($registration, $actor) {
            $locked = ProgramRegistration::query()
                ->with(['participant.customer', 'program.product', 'batch.location'])
                ->lockForUpdate()
                ->findOrFail($registration->id);

            if ($locked->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'registration' => 'Cancelled registrations cannot be converted to orders.',
                ]);
            }

            if ($locked->order_id) {
                $existing = Order::with(['items.product', 'customer', 'location', 'payments'])
                    ->find($locked->order_id);

                if ($existing) {
                    return $existing;
                }

                $locked->update(['order_id' => null]);
            }

            if (! $locked->program?->product) {
                throw ValidationException::withMessages([
                    'program_id' => 'This program is not linked to a product.',
                ]);
            }

            $customer = $locked->participant?->customer;
            $fee = round((float) $locked->fee_amount, 2);
            $discount = round((float) $locked->discount_amount, 2);
            $total = round(max(0, $fee - $discount), 2);
            $locationId = $locked->batch?->location_id ?: Location::query()->oldest('id')->value('id');

            if (! $locationId) {
                throw ValidationException::withMessages([
                    'location_id' => 'Create at least one POS location before generating a registration order.',
                ]);
            }

            $order = $this->orders->createDraft(
                $locationId,
                $customer?->id,
                'service',
                null,
                null,
                null,
                null,
                [],
                $actor->id
            );

            $order->items()->create([
                'product_id' => $locked->program->product->id,
                'quantity' => 1,
                'price' => $fee,
                'discount' => 0,
                'tax' => 0,
                'total' => $fee,
                'item_status' => 'active',
            ]);

            $order->update($this->orderAttributes([
                'customer_name' => $customer?->name ?? $locked->participant?->display_name,
                'customer_email' => $customer?->email,
                'customer_phone' => $customer?->phone,
                'source' => 'registration',
                'notes' => trim(implode("\n", array_filter([
                    'Registration '.$locked->registration_number,
                    $locked->batch?->name ? 'Batch: '.$locked->batch->name : null,
                ]))),
                'status' => 'pending_payment',
                'payment_status' => 'unpaid',
                'subtotal' => $fee,
                'discount' => $discount,
                'tax' => 0,
                'total' => $total,
                'paid_amount' => 0,
                'balance_due' => $total,
                'meta' => array_filter([
                    'source' => 'registration',
                    'registration_id' => $locked->id,
                    'registration_number' => $locked->registration_number,
                    'participant_profile_id' => $locked->participant_profile_id,
                    'program_id' => $locked->program_id,
                    'program_batch_id' => $locked->program_batch_id,
                ], fn ($value) => $value !== null),
            ]));

            $locked->update([
                'order_id' => $order->id,
                'updated_by' => $actor->id,
            ]);

            return $order->fresh(['items.product', 'customer', 'location', 'payments']);
        });
    }

    private function orderAttributes(array $attributes): array
    {
        return collect($attributes)
            ->filter(fn ($value, $column) => Schema::hasColumn('pos_orders', $column))
            ->all();
    }

    private function resolveBatch(Program $program, mixed $batchId): ?ProgramBatch
    {
        if (! $batchId) return null;

        $batch = ProgramBatch::findOrFail((int) $batchId);
        if ($batch->program_id !== $program->id) {
            throw ValidationException::withMessages(['program_batch_id' => 'The selected batch does not belong to this program.']);
        }
        if ($batch->archived_at || $batch->status !== 'active') {
            throw ValidationException::withMessages(['program_batch_id' => 'Select an active batch.']);
        }

        return $batch;
    }

    private function dates(Program $program, array $data): array
    {
        $registered = Carbon::parse($data['registered_on'] ?? now()->toDateString())->startOfDay();
        $starts = ! empty($data['starts_on']) ? Carbon::parse($data['starts_on'])->startOfDay() : null;
        $ends = ! empty($data['ends_on']) ? Carbon::parse($data['ends_on'])->startOfDay() : null;

        if (! $starts) {
            $starts = in_array($program->duration_type, ['fixed_dates', 'single_day'], true) && $program->start_date
                ? Carbon::parse($program->start_date)
                : $registered->copy();
        }
        if (! $ends) {
            $ends = match ($program->duration_type) {
                'fixed_dates', 'single_day' => $program->end_date ? Carbon::parse($program->end_date) : null,
                'days_from_registration' => $starts->copy()->addDays((int) $program->duration_value)->subDay(),
                'months_from_registration' => $starts->copy()->addMonthsNoOverflow((int) $program->duration_value)->subDay(),
                'years_from_registration' => $starts->copy()->addYearsNoOverflow((int) $program->duration_value)->subDay(),
                default => null,
            };
        }
        if ($ends && $ends->lt($starts)) {
            throw ValidationException::withMessages(['ends_on' => 'End date must be on or after start date.']);
        }

        return [
            'registered_on' => $registered->toDateString(),
            'starts_on' => $starts->toDateString(),
            'ends_on' => $ends?->toDateString(),
        ];
    }

    private function amounts(Program $program, array $data): array
    {
        $fee = round((float) ($data['fee_amount'] ?? $program->product->price ?? 0), 2);
        $discount = round((float) ($data['discount_amount'] ?? 0), 2);
        if ($discount > $fee) {
            throw ValidationException::withMessages(['discount_amount' => 'Discount cannot exceed the fee amount.']);
        }

        return [
            'fee_amount' => $fee,
            'discount_amount' => $discount,
            'final_amount' => round($fee - $discount, 2),
        ];
    }

    private function nextNumber(): string
    {
        do {
            $number = 'REG-'.now()->format('Y').'-'.Str::upper(Str::random(6));
        } while (ProgramRegistration::where('registration_number', $number)->exists());

        return $number;
    }
}
