@extends('layouts.app')
@section('title','Facturas')
@section('content')
<a class="underline" href="{{ route('facturas.create') }}">Nueva Factura</a>
<table class="mt-4 w-full bg-white rounded shadow">
  <thead><tr><th class="p-2 text-left">Factura</th><th class="p-2">Cliente</th><th class="p-2">EDS</th><th class="p-2">Emisión</th><th class="p-2">Vence</th><th class="p-2">Total</th><th class="p-2">Estado</th><th></th></tr></thead>
  <tbody>
    @foreach($facturas as $f)
      <tr class="border-t">
        <td class="p-2 text-left">{{ $f->prefijo }}-{{ $f->consecutivo }}</td>
        <td class="p-2">{{ $f->cliente->razon_social }}</td>
        <td class="p-2">{{ $f->eds->nombre }}</td>
        <td class="p-2">{{ $f->fecha_emision->format('Y-m-d') }}</td>
        <td class="p-2">{{ $f->fecha_vencimiento->format('Y-m-d') }}</td>
        <td class="p-2">${{ number_format($f->total,0,',','.') }}</td>
        <td class="p-2">{{ $f->estado }}</td>
        <td class="p-2">
          <a class="underline" href="{{ route('facturas.edit',$f) }}">Editar</a>
          <form class="inline" method="post" action="{{ route('facturas.destroy',$f) }}">@csrf @method('delete') <button class="underline text-red-600">Eliminar</button></form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
{{ $facturas->links() }}
@endsection
