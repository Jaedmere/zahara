@extends('layouts.app')
@section('title','Auditoría')
@section('content')
<table class="mt-4 w-full bg-white rounded shadow">
  <thead><tr><th class="p-2 text-left">Fecha</th><th class="p-2">Usuario</th><th class="p-2">Acción</th><th class="p-2">Tabla</th><th class="p-2">ID</th></tr></thead>
  <tbody>
  @foreach($logs as $l)
    <tr class="border-t">
      <td class="p-2 text-left">{{ $l->created_at }}</td>
      <td class="p-2">{{ $l->user_id }}</td>
      <td class="p-2">{{ $l->accion }}</td>
      <td class="p-2">{{ $l->tabla }}</td>
      <td class="p-2">{{ $l->registro_id }}</td>
    </tr>
  @endforeach
  </tbody>
</table>
{{ $logs->links() }}
@endsection
