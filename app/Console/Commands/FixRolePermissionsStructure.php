<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixRolePermissionsStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:role-permissions-structure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige la structure de la table role_permissions pour correspondre aux attentes de Laravel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Correction de la structure de la table role_permissions...');

        // Vérifier si la colonne 'role' existe
        if (Schema::hasColumn('role_permissions', 'role')) {
            $this->info('Colonne "role" trouvée. Conversion en "role_id"...');

            // Vérifier le type de la colonne 'role'
            $roleColumnType = DB::selectOne("
                SELECT data_type
                FROM information_schema.columns
                WHERE table_name = 'role_permissions'
                AND column_name = 'role'
            ");

            if ($roleColumnType->data_type === 'character varying' || $roleColumnType->data_type === 'varchar' || $roleColumnType->data_type === 'text') {
                $this->info('La colonne "role" contient des valeurs textuelles. Conversion en IDs...');

                // Vérifier si role_id existe déjà
                if (!Schema::hasColumn('role_permissions', 'role_id')) {
                    // Ajouter la colonne role_id
                    DB::statement('ALTER TABLE role_permissions ADD COLUMN role_id bigint');
                }

                // Remplir role_id en fonction des valeurs de 'role'
                // Si 'role' contient des slugs, on les convertit en IDs
                $this->info('Conversion des valeurs de "role" en IDs...');
                DB::statement('
                    UPDATE role_permissions rp
                    SET role_id = r.id
                    FROM roles r
                    WHERE r.slug = rp.role OR r.name = rp.role
                ');

                // Vérifier combien de lignes ont été mises à jour
                $updated = DB::selectOne("
                    SELECT COUNT(*) as count
                    FROM role_permissions
                    WHERE role_id IS NOT NULL
                ");

                $this->info("{$updated->count} lignes mises à jour avec role_id.");

                // Supprimer la colonne 'role' si toutes les lignes ont été converties
                $nullCount = DB::selectOne("
                    SELECT COUNT(*) as count
                    FROM role_permissions
                    WHERE role_id IS NULL
                ");

                if ($nullCount->count == 0) {
                    $this->info('Suppression de la colonne "role"...');
                    DB::statement('ALTER TABLE role_permissions DROP COLUMN IF EXISTS role');
                } else {
                    $this->warn("⚠️  {$nullCount->count} lignes n'ont pas pu être converties. Vérifiez les données.");
                }
            } else {
                // Si c'est déjà un type numérique, renommer directement
                $this->info('Renommage de la colonne "role" en "role_id"...');
                DB::statement('ALTER TABLE role_permissions RENAME COLUMN role TO role_id');
            }
        }

        // Vérifier si role_id existe maintenant
        if (Schema::hasColumn('role_permissions', 'role_id')) {
            // Vérifier le type de role_id
            $roleIdType = DB::selectOne("
                SELECT data_type
                FROM information_schema.columns
                WHERE table_name = 'role_permissions'
                AND column_name = 'role_id'
            ");

            if ($roleIdType->data_type !== 'bigint') {
                $this->info('Conversion de role_id en bigint...');
                DB::statement('ALTER TABLE role_permissions ALTER COLUMN role_id TYPE bigint USING role_id::bigint');
            }

            // Recréer la contrainte de clé étrangère pour role_id
            $this->info('Recréation de la contrainte de clé étrangère pour role_id...');
            DB::statement('
                DO $$
                BEGIN
                    IF NOT EXISTS (
                        SELECT 1 FROM information_schema.table_constraints
                        WHERE table_name = \'role_permissions\'
                        AND constraint_name = \'role_permissions_role_id_foreign\'
                    ) THEN
                        ALTER TABLE role_permissions
                        ADD CONSTRAINT role_permissions_role_id_foreign
                        FOREIGN KEY (role_id)
                        REFERENCES roles(id)
                        ON DELETE CASCADE;
                    END IF;
                END $$;
            ');

            // Recréer l'index
            DB::statement('CREATE INDEX IF NOT EXISTS role_permissions_role_id_index ON role_permissions(role_id)');
        }

        // Recréer la contrainte de clé étrangère pour permission_id si elle n'existe pas
        $this->info('Vérification de la contrainte de clé étrangère pour permission_id...');
        DB::statement('
            DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1 FROM information_schema.table_constraints
                    WHERE table_name = \'role_permissions\'
                    AND constraint_name = \'role_permissions_permission_id_foreign\'
                ) THEN
                    ALTER TABLE role_permissions
                    ADD CONSTRAINT role_permissions_permission_id_foreign
                    FOREIGN KEY (permission_id)
                    REFERENCES permissions(id)
                    ON DELETE CASCADE;
                END IF;
            END $$;
        ');

        // Recréer l'index pour permission_id
        DB::statement('CREATE INDEX IF NOT EXISTS role_permissions_permission_id_index ON role_permissions(permission_id)');

        // Recréer l'index unique sur role_id et permission_id
        $this->info('Recréation de l\'index unique...');
        DB::statement('
            DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1 FROM pg_indexes
                    WHERE tablename = \'role_permissions\'
                    AND indexname = \'role_permissions_role_id_permission_id_unique\'
                ) THEN
                    CREATE UNIQUE INDEX role_permissions_role_id_permission_id_unique
                    ON role_permissions(role_id, permission_id);
                END IF;
            END $$;
        ');

        $this->info('✅ Structure de la table corrigée avec succès !');
        return 0;
    }
}

