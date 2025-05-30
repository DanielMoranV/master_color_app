<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'description' => 'Administrador del sistema'],
            ['name' => 'user', 'description' => 'Usuario normal'],
            ['name' => 'dev', 'description' => 'Desarrollador'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
