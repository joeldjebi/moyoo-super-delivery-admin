<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PlatformAdmin;

class PlatformAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $username = env('PLATFORM_ADMIN_USERNAME', 'admin');

        // Vérifier si un admin existe déjà avec ce username
        $existingAdmin = PlatformAdmin::where('username', $username)->first();

        if ($existingAdmin) {
            // Si l'admin existe, mettre à jour le mot de passe si fourni
            if (env('PLATFORM_ADMIN_PASSWORD')) {
                $existingAdmin->password = env('PLATFORM_ADMIN_PASSWORD'); // Le cast 'hashed' va le hasher automatiquement
                $existingAdmin->status = 'active';
                $existingAdmin->failed_login_attempts = 0;
                $existingAdmin->locked_until = null;
                $existingAdmin->save();

                $this->command->info('Mot de passe du super administrateur mis à jour !');
                $this->command->info('Username: ' . $existingAdmin->username);
            } else {
                $this->command->warn('Un super administrateur avec le username "' . $username . '" existe déjà.');
            }
            return;
        }

        // Créer le premier super administrateur
        // Note: Le mot de passe sera automatiquement hashé par le cast 'hashed' du modèle
        $admin = PlatformAdmin::create([
            'username' => $username,
            'email' => env('PLATFORM_ADMIN_EMAIL', 'admin@moyoo.com'),
            'first_name' => env('PLATFORM_ADMIN_FIRST_NAME', 'Super'),
            'last_name' => env('PLATFORM_ADMIN_LAST_NAME', 'Administrator'),
            'password' => env('PLATFORM_ADMIN_PASSWORD', 'admin123456'), // Le cast 'hashed' va le hasher automatiquement
            'status' => 'active',
            'two_factor_enabled' => false,
            'failed_login_attempts' => 0,
        ]);

        $this->command->info('Super administrateur créé avec succès !');
        $this->command->info('Username: ' . $admin->username);
        $this->command->warn('Mot de passe par défaut: admin123456');
        $this->command->warn('IMPORTANT: Changez le mot de passe après la première connexion !');
    }
}
