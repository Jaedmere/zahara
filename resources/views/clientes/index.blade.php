@extends('layouts.app')
@section('title','Clientes')
@section('content')
<a class="underline" href="{{ route('clientes.create') }}">Nuevo Cliente</a>
<table class="mt-4 w-full bg-white rounded shadow">
  <thead><tr><th class="p-2 text-left">Documento</th><th class="p-2">Razón social</th><th class="p-2">Estado</th><th></th></tr></thead>
  <tbody>
    @foreach($clientes as $c)
      <tr class="border-t">
        <td class="p-2 text-left">{{ $c->documento }}</td>
        <td class="p-2">{{ $c->razon_social }}</td>
        <td class="p-2">{{ $c->estado }}</td>
        <td class="p-2">
          <a class="underline" href="{{ route('clientes.edit',$c) }}">Editar</a>
          <form class="inline" method="post" action="{{ route('clientes.destroy',$c) }}">@csrf @method('delete') <button class="underline text-red-600">Eliminar</button></form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
{{ $clientes->links() }}
@endsection
