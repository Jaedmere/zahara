@extends('layouts.app')
@section('title','Editar Factura')
@section('content')
<form method="post" action="{{ route('facturas.update',$factura) }}" class="space-y-3 bg-white p-4 rounded shadow">
@csrf @method('put')
<input name="prefijo" value="{{ $factura->prefijo }}" class="border p-2 w-full" />
<input name="consecutivo" type="number" value="{{ $factura->consecutivo }}" class="border p-2 w-full" required />
<select name="cliente_id" class="border p-2 w-full">
  @foreach($clientes as $c)<option value="{{ $c->id }}" {{ $c->id==$factura->cliente_id?'selected':'' }}>{{ $c->razon_social }}</option>@endforeach
</select>
<select name="eds_id" class="border p-2 w-full">
  @foreach($eds as $e)<option value="{{ $e->id }}" {{ $e->id==$factura->eds_id?'selected':'' }}>{{ $e->nombre }}</option>@endforeach
</select>
<input name="fecha_emision" type="date" value="{{ $factura->fecha_emision->format('Y-m-d') }}" class="border p-2 w-full" required />
<input name="fecha_vencimiento" type="date" value="{{ $factura->fecha_vencimiento->format('Y-m-d') }}" class="border p-2 w-full" required />
<input name="subtotal" type="number" step="0.01" value="{{ $factura->subtotal }}" class="border p-2 w-full" required />
<input name="iva" type="number" step="0.01" value="{{ $factura->iva }}" class="border p-2 w-full" required />
<input name="retenciones" type="number" step="0.01" value="{{ $factura->retenciones }}" class="border p-2 w-full" required />
<input name="total" type="number" step="0.01" value="{{ $factura->total }}" class="border p-2 w-full" required />
<textarea name="notas" class="border p-2 w-full">{{ $factura->notas }}</textarea>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Actualizar</button>
</form>
@endsection
