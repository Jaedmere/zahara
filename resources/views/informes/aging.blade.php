@extends('layouts.app')
@section('title','Aging')
@section('content')
<table class="mt-4 w-full bg-white rounded shadow">
  <thead><tr><th class="p-2 text-left">EDS</th><th class="p-2">Cliente</th><th class="p-2">0</th><th class="p-2">0-30</th><th class="p-2">31-60</th><th class="p-2">61-90</th><th class="p-2">90+</th></tr></thead>
  <tbody>
  @foreach($rows as $r)
    <tr class="border-t">
      <td class="p-2 text-left">{{ $r->eds_id }}</td>
      <td class="p-2">{{ $r->cliente_id }}</td>
      <td class="p-2">{{ number_format($r->d0,0,',','.') }}</td>
      <td class="p-2">{{ number_format($r->d30,0,',','.') }}</td>
      <td class="p-2">{{ number_format($r->d60,0,',','.') }}</td>
      <td class="p-2">{{ number_format($r->d90,0,',','.') }}</td>
      <td class="p-2">{{ number_format($r->dmas,0,',','.') }}</td>
    </tr>
  @endforeach
  </tbody>
</table>
@endsection
