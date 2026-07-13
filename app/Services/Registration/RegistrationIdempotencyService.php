<?php
namespace App\Services\Registration;

use App\Services\IdempotencyService;

class RegistrationIdempotencyService
{
    public function __construct(private IdempotencyService $service){}
    public function run(string $scope,string $key,array $payload,callable $callback):array{$state=$this->service->acquire($scope,$key,$payload);if(!$state['execute'])return ['status'=>$state['record']->response_code,'body'=>$state['response'],'replay'=>true];try{$result=$callback();$body=$result->toArray();$this->service->complete($state['record'],201,$body,$result::class,$result->id);return ['status'=>201,'body'=>$body,'replay'=>false];}catch(\Throwable $e){$this->service->fail($state['record']);throw $e;}}
}
