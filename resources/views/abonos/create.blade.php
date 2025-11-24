@extends('layouts.app')
@section('title','Nuevo Abono')
@section('content')
<form method="post" action="{{ route('abonos.store') }}" class="space-y-3 bg-white p-4 rounded shadow">
@csrf
<select name="cliente_id" class="border p-2 w-full">
  @foreach($clientes as $c)<option value="{{ $c->id }}">{{ $c->razon_social }}</option>@endforeach
</select>
<select name="eds_id" class="border p-2 w-full">
  @foreach($eds as $e)<option value="{{ $e->id }}">{{ $e->nombre }}</option>@endforeach
</select>
<input name="fecha" type="date" class="border p-2 w-full" required />
<input name="valor" type="number" step="0.01" class="border p-2 w-full" required />
<input name="medio_pago" placeholder="Medio de pago" class="border p-2 w-full" />
<input name="referencia_bancaria" placeholder="Referencia bancaria" class="border p-2 w-full" />
<input name="banco" placeholder="Banco" class="border p-2 w-full" />
<input name="descuento" type="number" step="0.01" value="0" class="border p-2 w-full" />
<select name="aplicacion" class="border p-2 w-full">
  <option value="fifo">FIFO</option>
  <option value="manual">Manual</option>
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Guardar</button>
</form>
@endsection
