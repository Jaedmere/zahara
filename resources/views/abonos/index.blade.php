@extends('layouts.app')
@section('title','Abonos')
@section('content')
<a class="underline" href="{{ route('abonos.create') }}">Nuevo Abono</a>
<table class="mt-4 w-full bg-white rounded shadow">
  <thead><tr><th class="p-2 text-left">Fecha</th><th class="p-2">Cliente</th><th class="p-2">EDS</th><th class="p-2">Valor</th><th class="p-2">Ref</th></tr></thead>
  <tbody>
    @foreach($abonos as $a)
      <tr class="border-t">
        <td class="p-2 text-left">{{ $a->fecha->format('Y-m-d') }}</td>
        <td class="p-2">{{ $a->cliente->razon_social }}</td>
        <td class="p-2">{{ $a->eds->nombre }}</td>
        <td class="p-2">${{ number_format($a->valor,0,',','.') }}</td>
        <td class="p-2">{{ $a->referencia_bancaria }}</td>
      </tr>
    @endforeach
  </tbody>
</table>
{{ $abonos->links() }}
@endsection
