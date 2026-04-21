@extends('invoices.cafe.layouts.base')

@section('content')

<div class="header">

@if($branding->logo)
<img src="{{ $branding->logo }}" class="logo">
@endif

<div class="company" style="color:{{ $branding->primary_color }}">
{{ $branding->company_name }}
</div>

<div class="meta">
Token #: {{ $order['order_no'] }} <br>
Time: {{ now()->format('h:i A') }}
</div>

</div>

<div class="divider"></div>

<div style="text-align:center;font-size:22px;font-weight:bold">
#{{ substr($order['order_no'], -4) }}
</div>

<div class="divider"></div>

<div class="meta" style="text-align:center">
Items: {{ count($order['items']) }} <br>
@if(isset($order['table']))
Table: {{ $order['table']['id'] }}
@endif
</div>

<div class="divider"></div>

<div class="footer">
Please collect your order when called
</div>

@endsection