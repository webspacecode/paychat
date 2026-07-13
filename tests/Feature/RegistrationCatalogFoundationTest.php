<?php
namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Location;
use App\Models\Tenant\Order;
use App\Models\Tenant\Product;
use App\Models\Tenant\Registration\ParticipantProfile;
use App\Models\User;
use App\Services\Registration\ParticipantService;
use App\Services\Registration\ProgramDurationService;
use App\Services\Registration\ProgramRegistrationService;
use App\Services\Registration\ProgramService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RegistrationCatalogFoundationTest extends TestCase
{
 protected User $actor;
 protected function setUp():void{parent::setUp();foreach(['tenant','mysql'] as $c){Config::set("database.connections.$c",['driver'=>'sqlite','database'=>':memory:','prefix'=>'','foreign_key_constraints'=>false]);DB::purge($c);DB::connection($c)->reconnect();}DB::setDefaultConnection('tenant');$this->baseSchema();(include database_path('migrations/tenant/2026_07_13_000001_create_registration_catalog_tables.php'))->up();(include database_path('migrations/tenant/2026_07_13_000002_create_program_registrations_table.php'))->up();(include database_path('migrations/tenant/2026_07_13_000003_add_order_id_to_program_registrations_table.php'))->up();app()->instance('currentTenant',(new Tenant)->forceFill(['id'=>7,'industry'=>'other']));$this->actor=(new User)->forceFill(['id'=>10,'tenant_id'=>7,'role'=>'owner']);}
 public function test_program_duration_definitions_are_strict():void{$s=app(ProgramDurationService::class);$this->assertNull($s->normalizeDefinition(['duration_type'=>'no_expiry','duration_value'=>2,'end_date'=>'2030-01-01'])['end_date']);$this->assertSame('2026-08-01',$s->normalizeDefinition(['duration_type'=>'single_day','start_date'=>'2026-08-01'])['end_date']);$this->expectException(ValidationException::class);$s->normalizeDefinition(['duration_type'=>'fixed_dates','start_date'=>'2026-09-01','end_date'=>'2026-08-01']);}
 public function test_program_can_create_atomic_basic_non_inventory_product():void{$p=app(ProgramService::class)->create(['product_mode'=>'new','product'=>['name'=>'Yoga 2026','sku'=>'YOGA-26','price'=>1200],'duration_type'=>'months_from_registration','duration_value'=>12,'status'=>'active'],$this->actor);$this->assertSame('basic',$p->product->type);$this->assertFalse($p->product->track_inventory);$this->assertSame(1,DB::table('programs')->count());}
 public function test_compatible_existing_product_can_link_only_once():void{$product=Product::create(['name'=>'Music','sku'=>'MUSIC','price'=>500,'type'=>'basic','track_inventory'=>false,'is_active'=>true]);$service=app(ProgramService::class);$service->create(['product_mode'=>'existing','product_id'=>$product->id,'duration_type'=>'no_expiry'],$this->actor);$this->expectException(ValidationException::class);$service->create(['product_mode'=>'existing','product_id'=>$product->id,'duration_type'=>'no_expiry'],$this->actor);}
 public function test_inventory_product_is_rejected():void{$product=Product::create(['name'=>'Stock','sku'=>'STOCK','price'=>50,'type'=>'basic','track_inventory'=>true,'is_active'=>true]);$this->expectException(ValidationException::class);app(ProgramService::class)->create(['product_mode'=>'existing','product_id'=>$product->id,'duration_type'=>'no_expiry'],$this->actor);}
 public function test_one_customer_can_have_multiple_participants_and_phone_is_optional():void{$customer=Customer::create(['name'=>'Parent','phone'=>'9999999999']);$service=app(ParticipantService::class);foreach(['Rahul','Priya'] as $name)$service->create(['customer_mode'=>'existing','customer_id'=>$customer->id,'participant'=>['first_name'=>$name]],$this->actor);$this->assertSame(2,ParticipantProfile::where('customer_id',$customer->id)->count());$this->assertNull(ParticipantProfile::first()->participant_phone);$this->assertNotSame(ParticipantProfile::first()->participant_code,ParticipantProfile::latest('id')->first()->participant_code);}
 public function test_new_customer_match_requires_explicit_confirmation():void{Customer::create(['name'=>'Rajesh','phone'=>'9876543210']);$this->expectException(ValidationException::class);app(ParticipantService::class)->create(['customer_mode'=>'new','customer'=>['name'=>'Rajesh','phone'=>'9876543210'],'participant'=>['first_name'=>'Child']],$this->actor);}
 public function test_archiving_one_participant_preserves_contact_and_sibling():void{$c=Customer::create(['name'=>'Parent','phone'=>'1']);$s=app(ParticipantService::class);$a=$s->create(['customer_mode'=>'existing','customer_id'=>$c->id,'participant'=>['first_name'=>'A']],$this->actor);$b=$s->create(['customer_mode'=>'existing','customer_id'=>$c->id,'participant'=>['first_name'=>'B']],$this->actor);$s->archive($a,$this->actor);$this->assertSame('archived',$a->fresh()->status);$this->assertSame('active',$b->fresh()->status);$this->assertTrue(Customer::whereKey($c->id)->exists());}
 public function test_registration_links_participant_program_batch_and_snapshots_fee():void
 {
  $program=app(ProgramService::class)->create(['product_mode'=>'new','product'=>['name'=>'Annual Music','sku'=>'MUSIC-YEAR','price'=>1200],'duration_type'=>'months_from_registration','duration_value'=>12,'status'=>'active'],$this->actor);
  $batch=$program->batches()->create(['name'=>'Morning','status'=>'active']);
  $customer=Customer::create(['name'=>'Parent','phone'=>'999']);
  $participant=app(ParticipantService::class)->create(['customer_mode'=>'existing','customer_id'=>$customer->id,'participant'=>['first_name'=>'Student']],$this->actor);
  $registration=app(ProgramRegistrationService::class)->create(['participant_profile_id'=>$participant->id,'program_id'=>$program->id,'program_batch_id'=>$batch->id,'registered_on'=>'2026-07-13','discount_amount'=>200],$this->actor);
  $this->assertSame($participant->id,$registration->participant_profile_id);
  $this->assertSame($program->id,$registration->program_id);
  $this->assertSame($batch->id,$registration->program_batch_id);
  $this->assertSame('1200.00',$registration->fee_amount);
  $this->assertSame('1000.00',$registration->final_amount);
  $this->assertSame('2027-07-12',$registration->ends_on->toDateString());
 }
 public function test_duplicate_open_registration_is_rejected():void
 {
  $program=app(ProgramService::class)->create(['product_mode'=>'new','product'=>['name'=>'Course','sku'=>'COURSE','price'=>500],'duration_type'=>'no_expiry','status'=>'active'],$this->actor);
  $customer=Customer::create(['name'=>'Parent']);
  $participant=app(ParticipantService::class)->create(['customer_mode'=>'existing','customer_id'=>$customer->id,'participant'=>['first_name'=>'Student']],$this->actor);
  $service=app(ProgramRegistrationService::class);
  $payload=['participant_profile_id'=>$participant->id,'program_id'=>$program->id];
  $service->create($payload,$this->actor);
  $this->expectException(ValidationException::class);
  $service->create($payload,$this->actor);
 }
 public function test_registration_generates_reusable_pos_order_for_reporting():void
 {
  $location=Location::create(['name'=>'Main']);
  $program=app(ProgramService::class)->create(['product_mode'=>'new','product'=>['name'=>'Robotics Camp','sku'=>'ROBO','price'=>2500],'duration_type'=>'no_expiry','status'=>'active'],$this->actor);
  $customer=Customer::create(['name'=>'Parent','phone'=>'999']);
  $participant=app(ParticipantService::class)->create(['customer_mode'=>'existing','customer_id'=>$customer->id,'participant'=>['first_name'=>'Student']],$this->actor);
  $service=app(ProgramRegistrationService::class);
  $registration=$service->create(['participant_profile_id'=>$participant->id,'program_id'=>$program->id,'discount_amount'=>500],$this->actor);
  $order=$service->generateOrder($registration,$this->actor);
  $again=$service->generateOrder($registration->fresh(),$this->actor);
  $this->assertSame($order->id,$again->id);
  $this->assertSame(1,Order::count());
  $this->assertSame($order->id,$registration->fresh()->order_id);
  $this->assertSame('pending_payment',$order->status);
  $this->assertSame('unpaid',$order->payment_status);
  $this->assertSame('registration',$order->source);
  $this->assertSame($location->id,$order->location_id);
  $this->assertEquals(2500.00,$order->subtotal);
  $this->assertEquals(500.00,$order->discount);
  $this->assertEquals(2000.00,$order->total);
  $this->assertSame($program->product_id,$order->items->first()->product_id);
 }
 private function baseSchema():void{Schema::connection('mysql')->create('users',function(Blueprint$t){$t->id();$t->unsignedBigInteger('tenant_id')->nullable();$t->string('name');$t->string('email')->nullable();$t->string('password')->nullable();$t->string('role')->nullable();$t->timestamps();});Schema::create('products',function(Blueprint$t){$t->id();$t->string('name');$t->string('sku')->unique();$t->string('barcode')->nullable();$t->string('type')->nullable();$t->decimal('price',10,2)->nullable();$t->string('unit')->nullable();$t->boolean('track_inventory')->default(true);$t->integer('low_stock_threshold')->nullable();$t->boolean('is_active')->default(true);$t->timestamps();});Schema::create('locations',function(Blueprint$t){$t->id();$t->string('name');$t->timestamps();});Schema::create('settings',function(Blueprint$t){$t->id();$t->string('setting_key')->unique();$t->text('setting_value')->nullable();$t->timestamps();});Schema::create('pos_customers',function(Blueprint$t){$t->id();$t->string('name')->nullable();$t->string('phone')->nullable();$t->string('email')->nullable();$t->timestamps();});Schema::create('pos_orders',function(Blueprint$t){$t->id();$t->string('order_no')->nullable();$t->unsignedBigInteger('customer_id')->nullable();$t->unsignedBigInteger('location_id')->nullable();$t->unsignedBigInteger('table_id')->nullable();$t->unsignedBigInteger('table_session_id')->nullable();$t->integer('guest_count')->nullable();$t->string('dining_flow')->nullable();$t->string('delivery_channel')->nullable();$t->string('delivery_channel_label')->nullable();$t->string('external_order_reference')->nullable();$t->string('customer_name')->nullable();$t->string('customer_email')->nullable();$t->string('customer_phone')->nullable();$t->string('order_type')->nullable();$t->string('source')->nullable();$t->string('status')->nullable();$t->string('payment_status')->nullable();$t->decimal('subtotal',12,2)->default(0);$t->decimal('discount',12,2)->default(0);$t->decimal('tax',12,2)->default(0);$t->decimal('total',12,2)->default(0);$t->decimal('paid_amount',12,2)->default(0);$t->decimal('balance_due',12,2)->default(0);$t->text('notes')->nullable();$t->json('meta')->nullable();$t->unsignedBigInteger('created_by')->nullable();$t->date('business_date')->nullable();$t->timestamps();});Schema::create('pos_order_items',function(Blueprint$t){$t->id();$t->unsignedBigInteger('order_id');$t->unsignedBigInteger('product_id');$t->integer('quantity');$t->decimal('price',12,2)->default(0);$t->decimal('discount',12,2)->default(0);$t->decimal('tax',12,2)->default(0);$t->decimal('total',12,2)->default(0);$t->string('item_status')->nullable();$t->timestamps();});Schema::create('pos_payments',function(Blueprint$t){$t->id();$t->unsignedBigInteger('order_id')->nullable();$t->decimal('amount',12,2)->default(0);$t->string('status')->nullable();$t->timestamps();});Schema::create('product_images',function(Blueprint$t){$t->id();$t->unsignedBigInteger('product_id');$t->string('image_path');$t->timestamps();});Schema::create('product_inventories',function(Blueprint$t){$t->id();$t->unsignedBigInteger('product_id');$t->unsignedBigInteger('location_id')->nullable();$t->integer('quantity')->default(0);$t->timestamps();});Schema::create('categories',function(Blueprint$t){$t->id();$t->string('name');$t->text('description')->nullable();$t->timestamps();});Schema::create('category_product',function(Blueprint$t){$t->unsignedBigInteger('category_id');$t->unsignedBigInteger('product_id');});Schema::create('recipes',function(Blueprint$t){$t->id();$t->unsignedBigInteger('product_id');$t->unsignedBigInteger('location_id')->nullable();$t->text('description')->nullable();$t->timestamps();});Schema::create('recipe_items',function(Blueprint$t){$t->id();$t->unsignedBigInteger('recipe_id');$t->unsignedBigInteger('raw_product_id');$t->integer('quantity');$t->string('unit')->nullable();$t->timestamps();});}
}
