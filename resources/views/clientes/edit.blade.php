@extends('layouts.app')
@section('title','Editar Cliente')
@section('content')
<form method="post" action="{{ route('clientes.update',$cliente) }}" class="space-y-3 bg-white p-4 rounded shadow">
@csrf @method('put')
<select name="tipo_id" class="border p-2 w-full">
  <option value="NIT" {{ $cliente->tipo_id=='NIT'?'selected':'' }}>NIT</option>
  <option value="CC"  {{ $cliente->tipo_id=='CC'?'selected':'' }}>CC</option>
</select>
<input name="documento" value="{{ $cliente->documento }}" class="border p-2 w-full" required />
<input name="razon_social" value="{{ $cliente->razon_social }}" class="border p-2 w-full" required />
<input name="email" value="{{ $cliente->email }}" type="email" class="border p-2 w-full" />
<input name="telefono" value="{{ $cliente->telefono }}" class="border p-2 w-full" />
<input name="direccion" value="{{ $cliente->direccion }}" class="border p-2 w-full" />
<input name="plazo_dias" type="number" value="{{ $cliente->plazo_dias }}" class="border p-2 w-full" />
<input name="lista_precios" value="{{ $cliente->lista_precios }}" class="border p-2 w-full" />
<select name="estado" class="border p-2 w-full">
  <option value="activo" {{ $cliente->estado=='activo'?'selected':'' }}>Activo</option>
  <option value="bloqueado" {{ $cliente->estado=='bloqueado'?'selected':'' }}>Bloqueado</option>
</select>
<label>EDS asignadas</label>
<select name="eds_ids[]" class="border p-2 w-full" multiple>
  @foreach($eds as $e)<option value="{{ $e->id }}" {{ $cliente->eds->contains($e->id)?'selected':'' }}>{{ $e->nombre }}</option>@endforeach
</select>
<textarea name="notas" class="border p-2 w-full">{{ $cliente->notas }}</textarea>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Actualizar</button>
</form>
@endsection
