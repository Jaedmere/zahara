<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(['nombre'=>'Administrador'], ['permisos_json'=>['*'=>true]]);
        Role::updateOrCreate(['nombre'=>'Jefe de Estación'], ['permisos_json'=>['facturas'=>'manage','abonos'=>'manage','informes'=>'view']]);
        Role::updateOrCreate(['nombre'=>'Analista Cartera'], ['permisos_json'=>['abonos'=>'manage','informes'=>'view']]);
    }
}
