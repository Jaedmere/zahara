@extends('layouts.app')
@section('title','Nueva Factura')
@section('content')
<form method="post" action="{{ route('facturas.store') }}" class="space-y-3 bg-white p-4 rounded shadow">
@csrf
<input name="prefijo" placeholder="Prefijo" class="border p-2 w-full" />
<input name="consecutivo" type="number" placeholder="Consecutivo" class="border p-2 w-full" required />
<select name="cliente_id" class="border p-2 w-full">
  @foreach($clientes as $c)<option value="{{ $c->id }}">{{ $c->razon_social }}</option>@endforeach
</select>
<select name="eds_id" class="border p-2 w-full">
  @foreach($eds as $e)<option value="{{ $e->id }}">{{ $e->nombre }}</option>@endforeach
</select>
<input name="fecha_emision" type="date" class="border p-2 w-full" required />
<input name="fecha_vencimiento" type="date" class="border p-2 w-full" required />
<input name="subtotal" type="number" step="0.01" placeholder="Subtotal" class="border p-2 w-full" required />
<input name="iva" type="number" step="0.01" placeholder="IVA" class="border p-2 w-full" required />
<input name="retenciones" type="number" step="0.01" placeholder="Retenciones" class="border p-2 w-full" required />
<input name="total" type="number" step="0.01" placeholder="Total" class="border p-2 w-full" required />
<textarea name="notas" class="border p-2 w-full" placeholder="Notas"></textarea>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Guardar</button>
</form>
@endsection
