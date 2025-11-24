<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Role, User, EDS, Cliente, Factura, Abono, AbonoDetalle};
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstWhere('nombre','Administrador');
        $jefeRole = Role::firstWhere('nombre','Jefe de Estación');
        $analistaRole = Role::firstWhere('nombre','Analista Cartera');

        $admin = User::firstOrCreate(['email'=>'admin@example.com'], [
            'name' => 'Admin',
            'password' => Hash::make('password'),
            'rol_id' => $adminRole->id,
            'activo' => true,
        ]);

        $bog = EDS::firstOrCreate(['codigo'=>'BOG-01'], ['nombre'=>'EDS Bogotá 01', 'nit'=>'800123456-1']);
        $med = EDS::firstOrCreate(['codigo'=>'MED-01'], ['nombre'=>'EDS Medellín 01', 'nit'=>'900987654-2']);
        $admin->eds()->syncWithoutDetaching([$bog->id, $med->id]);

        $cli = Cliente::firstOrCreate(['documento'=>'900111222'], [
            'tipo_id' => 'NIT',
            'razon_social' => 'Cliente Demo SAS',
            'email' => 'cliente@demo.com',
            'plazo_dias' => 30,
            'estado' => 'activo',
        ]);
        $cli->eds()->syncWithoutDetaching([$bog->id]);

        // Facturas demo
        $f1 = Factura::create([
            'prefijo'=>'FCT','consecutivo'=>1,'cliente_id'=>$cli->id,'eds_id'=>$bog->id,
            'fecha_emision'=>now()->subDays(40)->toDateString(),
            'fecha_vencimiento'=>now()->subDays(10)->toDateString(),
            'subtotal'=>1000000,'iva'=>190000,'retenciones'=>0,'total'=>1190000,'estado'=>'vencido'
        ]);
        $f2 = Factura::create([
            'prefijo'=>'FCT','consecutivo'=>2,'cliente_id'=>$cli->id,'eds_id'=>$bog->id,
            'fecha_emision'=>now()->subDays(15)->toDateString(),
            'fecha_vencimiento'=>now()->addDays(15)->toDateString(),
            'subtotal'=>500000,'iva'=>95000,'retenciones'=>0,'total'=>595000,'estado'=>'pendiente'
        ]);

        // Abono parcial a f1
        $ab = Abono::create([
            'cliente_id'=>$cli->id,'eds_id'=>$bog->id,'fecha'=>now()->toDateString(),'valor'=>400000,
            'medio_pago'=>'Transferencia','referencia_bancaria'=>'ABC123','banco'=>'Bancolombia'
        ]);
        AbonoDetalle::create(['abono_id'=>$ab->id,'factura_id'=>$f1->id,'valor_aplicado'=>400000,'descuento_aplicado'=>0]);

        // Ajuste de estados
        if (method_exists(\App\Http\Controllers\AbonoController::class, 'recalcularEstadoFactura')) {
            \App\Http\Controllers\AbonoController::recalcularEstadoFactura($f1->id);
            \App\Http\Controllers\AbonoController::recalcularEstadoFactura($f2->id);
        }
    }
}
