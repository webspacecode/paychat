<?php
namespace App\Services\Registration;

use App\Models\Tenant\Product;
use App\Models\Tenant\Registration\Program;
use App\Models\User;
use App\Services\ProductManagement\ProductApplicationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProgramService
{
    public function __construct(private ProductApplicationService $products, private ProgramDurationService $durations) {}

    public function paginate(array $filters) {
        $perPage = max(1, min(100, (int)($filters['per_page'] ?? 20)));
        $allowedSorts = ['created_at','status','program_type','start_date','end_date'];
        $sort = in_array($filters['sort'] ?? '', $allowedSorts, true) ? $filters['sort'] : 'created_at';
        $direction = ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        return Program::query()->select(['id','product_id','program_type','duration_type','duration_value','start_date','end_date','capacity','renewable','status','archived_at','created_at'])
            ->with(['product:id,name,sku,price,type,track_inventory,is_active'])->withCount('batches')
            ->when(!($filters['include_archived'] ?? false), fn($q)=>$q->whereNull('archived_at'))
            ->when($filters['status'] ?? null, fn($q,$v)=>$q->where('status',$v))
            ->when($filters['program_type'] ?? null, fn($q,$v)=>$q->where('program_type',$v))
            ->when($filters['duration_type'] ?? null, fn($q,$v)=>$q->where('duration_type',$v))
            ->when($filters['search'] ?? null, fn($q,$v)=>$q->where(function($x) use($v){$x->where('program_type','like',"{$v}%")->orWhereHas('product',fn($p)=>$p->where('name','like',"{$v}%")->orWhere('sku','like',"{$v}%"));}))
            ->orderBy($sort,$direction)->paginate($perPage);
    }

    public function create(array $data, User $actor): Program
    {
        return DB::connection('tenant')->transaction(function() use($data,$actor) {
            $data = $this->durations->normalizeDefinition($data);
            if (($data['product_mode'] ?? 'new') === 'existing') $product = $this->compatibleProduct((int)$data['product_id']);
            else $product = $this->products->createBasicNonInventory([...($data['product'] ?? []), 'industry'=>app('currentTenant')->industry]);
            return Program::create([...$this->programData($data), 'product_id'=>$product->id, 'created_by'=>$actor->id, 'updated_by'=>$actor->id])->load('product');
        });
    }

    public function update(Program $program, array $data, User $actor): Program
    {
        return DB::connection('tenant')->transaction(function() use($program,$data,$actor) {
            if (array_key_exists('product_id',$data)) throw ValidationException::withMessages(['product_id'=>'Linked product cannot be changed.']);
            $merged = array_merge($program->toArray(), $data);
            $normalized = $this->durations->normalizeDefinition($merged);
            if (! empty($data['product'])) $this->products->update($program->product, [...$data['product'],'industry'=>app('currentTenant')->industry]);
            $program->update([...$this->programData($normalized), 'updated_by'=>$actor->id]);
            return $program->fresh()->load('product');
        });
    }

    public function archive(Program $program, User $actor, bool $disableProduct=false): Program
    {
        return DB::connection('tenant')->transaction(function() use($program,$actor,$disableProduct) {
            $program->update(['status'=>'archived','archived_at'=>$program->archived_at ?: now(),'updated_by'=>$actor->id]);
            if ($disableProduct) $this->products->update($program->product, ['industry'=>app('currentTenant')->industry,'is_active'=>false]);
            return $program->fresh()->load('product');
        });
    }

    private function compatibleProduct(int $id): Product
    {
        $product = Product::findOrFail($id);
        if (!$product->is_active || $product->type !== 'basic' || $product->track_inventory || Program::where('product_id',$id)->exists()) throw ValidationException::withMessages(['product_id'=>'Selected product is not compatible.']);
        return $product;
    }

    private function programData(array $d): array { return collect($d)->only(['program_type','description','duration_type','duration_value','start_date','end_date','registration_open_date','registration_close_date','capacity','renewable','renewal_frequency','status','settings'])->all(); }
}
