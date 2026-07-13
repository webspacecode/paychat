<?php

namespace App\Http\Controllers\Api\Tenant\Registration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Registration\StoreProgramRegistrationRequest;
use App\Http\Requests\Tenant\Registration\UpdateProgramRegistrationRequest;
use App\Http\Resources\Tenant\OrderResource;
use App\Models\Tenant\Registration\ProgramRegistration;
use App\Services\Registration\ProgramRegistrationService;
use App\Services\Registration\RegistrationIdempotencyService;
use App\Support\Observability;
use Illuminate\Http\Request;

class ProgramRegistrationController extends Controller
{
    public function overview(ProgramRegistrationService $service): array
    {
        return ['data' => $service->overview()];
    }

    public function index(Request $request, ProgramRegistrationService $service)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', 'string', 'max:30'],
            'program_id' => ['nullable', 'integer'],
            'program_batch_id' => ['nullable', 'integer'],
            'participant_profile_id' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return response()->json($service->paginate($filters));
    }

    public function store(
        StoreProgramRegistrationRequest $request,
        ProgramRegistrationService $service,
        RegistrationIdempotencyService $idempotency,
    ) {
        $key = (string) $request->header('X-Idempotency-Key');
        if ($key === '') abort(422, 'X-Idempotency-Key is required.');

        $result = $idempotency->run(
            'registration.enrollment.create',
            $key,
            $request->validated(),
            fn () => $service->create($request->validated(), $request->user()),
        );

        Observability::logInfo('registration.enrollment.created', [
            'resource_id' => $result['body']['id'] ?? null,
        ], $request);

        return response()->json($result['body'], $result['status']);
    }

    public function show(string $tenantSlug, ProgramRegistration $registration)
    {
        return response()->json($registration->load([
            'participant.customer',
            'participant.registrations.program.product',
            'program.product',
            'program.batches' => fn ($query) => $query->whereNull('archived_at')->orderBy('name'),
            'batch.location',
            'order.items.product',
            'order.customer',
            'order.location',
            'order.payments',
        ]));
    }

    public function update(
        string $tenantSlug,
        ProgramRegistration $registration,
        UpdateProgramRegistrationRequest $request,
        ProgramRegistrationService $service,
    ) {
        $updated = $service->update($registration, $request->validated(), $request->user());
        Observability::logInfo('registration.enrollment.updated', ['resource_id' => $updated->id], $request);

        return response()->json($updated);
    }

    public function cancel(
        string $tenantSlug,
        ProgramRegistration $registration,
        Request $request,
        ProgramRegistrationService $service,
    ) {
        $cancelled = $service->cancel($registration, $request->user());
        Observability::logInfo('registration.enrollment.cancelled', ['resource_id' => $cancelled->id], $request);

        return response()->json($cancelled);
    }

    public function generateOrder(
        string $tenantSlug,
        ProgramRegistration $registration,
        Request $request,
        ProgramRegistrationService $service,
    ) {
        $order = $service->generateOrder($registration, $request->user());

        Observability::logInfo('registration.enrollment.order_generated', [
            'resource_id' => $registration->id,
            'order_id' => $order->id,
        ], $request);

        return new OrderResource($order);
    }
}
