@extends('layouts.app')
@section('title','Nuevo Cliente')
@section('content')
<form method="post" action="{{ route('clientes.store') }}" class="space-y-3 bg-white p-4 rounded shadow">
@csrf
<select name="tipo_id" class="border p-2 w-full">
  <option value="NIT">NIT</option>
  <option value="CC">CC</option>
</select>
<input name="documento" placeholder="Documento" class="border p-2 w-full" required />
<input name="razon_social" placeholder="Razón social" class="border p-2 w-full" required />
<input name="email" type="email" placeholder="Email" class="border p-2 w-full" />
<input name="telefono" placeholder="Teléfono" class="border p-2 w-full" />
<input name="direccion" placeholder="Dirección" class="border p-2 w-full" />
<input name="plazo_dias" type="number" value="0" class="border p-2 w-full" />
<input name="lista_precios" placeholder="Lista de precios" class="border p-2 w-full" />
<select name="estado" class="border p-2 w-full">
  <option value="activo">Activo</option>
  <option value="bloqueado">Bloqueado</option>
</select>
<label>EDS asignadas</label>
<select name="eds_ids[]" class="border p-2 w-full" multiple>
  @foreach($eds as $e)<option value="{{ $e->id }}">{{ $e->nombre }}</option>@endforeach
</select>
<textarea name="notas" class="border p-2 w-full" placeholder="Notas"></textarea>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Guardar</button>
</form>
@endsection
