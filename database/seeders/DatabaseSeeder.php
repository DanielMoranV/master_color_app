<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed the roles first
        $this->call(RoleSeeder::class);

        // Create admin user
        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role_id' => Role::where('name', 'admin')->first()->id,
            'dni' => '12345678A',
        ]);

        // Create regular user
        User::factory()->create([
            'name' => 'Usuario Normal',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role_id' => Role::where('name', 'user')->first()->id,
            'dni' => '87654321B',
        ]);
    }
}
