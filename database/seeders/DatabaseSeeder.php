<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ne pas créer d'utilisateur de test car la table users existe déjà avec une structure différente
        // User::factory(10)->create();
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Seeders pour la plateforme admin
        $this->call([
            PlatformAdminSeeder::class,
            RBACSeeder::class,
            AssignAllPermissionsToSuperAdminSeeder::class,
            ModuleSeeder::class,
        ]);
    }
}
