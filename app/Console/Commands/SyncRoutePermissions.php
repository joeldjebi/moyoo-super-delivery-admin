<?php

namespace App\Console\Commands;

use App\Services\RoutePermissionSyncService;
use Illuminate\Console\Command;

class SyncRoutePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync-routes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchroniser les routes platform-admin avec les permissions et les attribuer au rôle super-admin';

    /**
     * Execute the console command.
     */
    public function handle(RoutePermissionSyncService $service)
    {
        $this->info('Synchronisation des routes avec les permissions...');

        $result = $service->sync();

        $this->info("Total de routes traitées : {$result['total']}");
        $this->info("Nouvelles permissions créées : " . count($result['created']));
        $this->info("Permissions mises à jour : " . count($result['updated']));

        if (count($result['created']) > 0) {
            $this->newLine();
            $this->info('Nouvelles permissions créées :');
            foreach ($result['created'] as $permission) {
                $this->line("  - {$permission->name} ({$permission->resource}.{$permission->action})");
            }
        }

        if (count($result['updated']) > 0) {
            $this->newLine();
            $this->info('Permissions mises à jour :');
            foreach ($result['updated'] as $permission) {
                $this->line("  - {$permission->name} ({$permission->resource}.{$permission->action})");
            }
        }

        $this->newLine();
        $this->info('✓ Toutes les permissions ont été attribuées au rôle super-admin.');

        return Command::SUCCESS;
    }
}

