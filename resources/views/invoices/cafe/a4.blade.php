@extends('invoices.cafe.layouts.base')

@section('content')

<div style="display:flex;justify-content:space-between">

<div>
<h2 style="color:{{ $branding->primary_color }}">
{{ $branding->company_name }}
</h2>

<p>{{ $branding->address }}</p>
<p>{{ $branding->phone }}</p>
<p>GST: {{ $tax->gst_number }}</p>
</div>

@if($branding->logo)
<img src="{{ $branding->logo }}" class="logo">
@endif

</div>

<div class="divider"></div>

<table width="100%" border="1" cellspacing="0" cellpadding="6">
<tr>
<th>Item</th>
<th>Qty</th>
<th>Price</th>
<th>Total</th>
</tr>

@foreach($order['items'] as $item)
<tr>
<td>{{ $item['name'] }}</td>
<td>{{ $item['qty'] }}</td>
<td>{{ $item['price'] }}</td>
<td>{{ $item['qty'] * $item['price'] }}</td>
</tr>
@endforeach

</table>

<div style="text-align:right;margin-top:20px">

<p>Subtotal: ₹ {{ $totals['subtotal'] }}</p>
<p>CGST: ₹ {{ $totals['cgst'] }}</p>
<p>SGST: ₹ {{ $totals['sgst'] }}</p>

<h2>Total: ₹ {{ $totals['total'] }}</h2>

</div>

@endsection