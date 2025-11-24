@extends('layouts.app')
@section('title','Dashboard')
@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
  <div class="p-4 bg-white rounded shadow">
    <div class="text-sm text-gray-600">Saldo total (demo)</div>
    <div class="text-2xl font-bold">$ 1.790.000</div>
  </div>
  <div class="p-4 bg-white rounded shadow">
    <div class="text-sm text-gray-600">% Vencido (demo)</div>
    <div class="text-2xl font-bold">56%</div>
  </div>
  <div class="p-4 bg-white rounded shadow">
    <div class="text-sm text-gray-600">DSO (demo)</div>
    <div class="text-2xl font-bold">38 días</div>
  </div>
</div>
@endsection
