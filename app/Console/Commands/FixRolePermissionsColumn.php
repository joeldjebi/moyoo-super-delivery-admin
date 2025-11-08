<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixRolePermissionsColumn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:role-permissions-column';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige le type de la colonne permission_id dans la table role_permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Inspection de la structure de la table role_permissions...');

        // Vérifier toutes les colonnes de la table
        $columns = DB::select("
            SELECT column_name, data_type, is_nullable
            FROM information_schema.columns
            WHERE table_name = 'role_permissions'
            ORDER BY ordinal_position
        ");

        $this->info('Colonnes trouvées dans role_permissions:');
        foreach ($columns as $column) {
            $this->line("  - {$column->column_name} ({$column->data_type})");
        }

        // Vérifier si la colonne 'permissions' existe et la renommer en 'permission_id'
        if (Schema::hasColumn('role_permissions', 'permissions')) {
            $this->info('Renommage de la colonne permissions en permission_id...');
            DB::statement('ALTER TABLE role_permissions RENAME COLUMN permissions TO permission_id');
        }

        // Vérifier le type de la colonne permission_id
        $columnType = DB::selectOne("
            SELECT data_type
            FROM information_schema.columns
            WHERE table_name = 'role_permissions'
            AND column_name = 'permission_id'
        ");

        if (!$columnType) {
            $this->error('La colonne permission_id n\'existe pas dans la table role_permissions.');
            return 1;
        }

        $this->info("Type de colonne actuel : {$columnType->data_type}");

        // Vérifier si role_id existe et trouver toutes les colonnes
        $hasRoleId = Schema::hasColumn('role_permissions', 'role_id');
        $hasId = Schema::hasColumn('role_permissions', 'id');
        $hasCreatedAt = Schema::hasColumn('role_permissions', 'created_at');
        $hasUpdatedAt = Schema::hasColumn('role_permissions', 'updated_at');

        if (!$hasRoleId) {
            $this->warn('⚠️  La colonne role_id n\'existe pas dans la table role_permissions.');
            $this->info('La table semble avoir une structure différente de celle attendue.');
            $this->info('Tentative de conversion uniquement de la colonne permission_id...');

            // Si role_id n'existe pas, on ne peut pas faire la conversion complète
            // On va juste convertir le type de colonne sans préserver les données
            if ($columnType->data_type === 'json' || $columnType->data_type === 'jsonb') {
                $this->info('Conversion directe du type de colonne (les données seront perdues)...');

                // Supprimer les contraintes
                DB::statement('
                    DO $$
                    DECLARE
                        r RECORD;
                    BEGIN
                        FOR r IN (
                            SELECT constraint_name
                            FROM information_schema.table_constraints
                            WHERE table_name = \'role_permissions\'
                            AND constraint_type = \'FOREIGN KEY\'
                        ) LOOP
                            EXECUTE \'ALTER TABLE role_permissions DROP CONSTRAINT IF EXISTS \' || quote_ident(r.constraint_name);
                        END LOOP;
                    END $$;
                ');

                // Vider la table
                DB::statement('TRUNCATE TABLE role_permissions');

                // Convertir la colonne
                DB::statement('ALTER TABLE role_permissions ALTER COLUMN permission_id TYPE bigint USING 0::bigint');

                $this->info('✅ Type de colonne converti. Vous devrez recréer les données.');
                return 0;
            }

            return 1;
        }

        if ($columnType->data_type === 'json' || $columnType->data_type === 'jsonb') {
            $this->info('Conversion de JSON en bigint...');

            // Supprimer toutes les contraintes de clé étrangère
            $this->info('Suppression des contraintes de clé étrangère...');
            DB::statement('
                DO $$
                DECLARE
                    r RECORD;
                BEGIN
                    FOR r IN (
                        SELECT constraint_name
                        FROM information_schema.table_constraints
                        WHERE table_name = \'role_permissions\'
                        AND constraint_type = \'FOREIGN KEY\'
                    ) LOOP
                        EXECUTE \'ALTER TABLE role_permissions DROP CONSTRAINT IF EXISTS \' || quote_ident(r.constraint_name);
                    END LOOP;
                END $$;
            ');

            // Supprimer les index
            $this->info('Suppression des index...');
            DB::statement('DROP INDEX IF EXISTS role_permissions_permission_id_index');
            DB::statement('DROP INDEX IF EXISTS role_permissions_role_id_index');

            // Sauvegarder et convertir les données JSON en bigint
            $this->info('Conversion des données JSON...');
            DB::statement('
                DO $$
                DECLARE
                    rec RECORD;
                    perm_id bigint;
                BEGIN
                    -- Créer une table temporaire pour stocker les nouvelles données
                    CREATE TEMP TABLE temp_role_permissions (
                        role_id bigint,
                        permission_id bigint,
                        created_at timestamp,
                        updated_at timestamp
                    );

                    -- Traiter chaque ligne
                    FOR rec IN SELECT * FROM role_permissions LOOP
                        IF jsonb_typeof(rec.permission_id::jsonb) = \'array\' THEN
                            -- Si c\'est un tableau, créer une ligne pour chaque ID
                            FOR perm_id IN
                                SELECT (value::text)::bigint
                                FROM jsonb_array_elements(rec.permission_id::jsonb)
                                WHERE jsonb_typeof(value) = \'number\'
                            LOOP
                                INSERT INTO temp_role_permissions (role_id, permission_id, created_at, updated_at)
                                VALUES (rec.role_id, perm_id, rec.created_at, rec.updated_at);
                            END LOOP;
                        ELSIF jsonb_typeof(rec.permission_id::jsonb) = \'number\' THEN
                            -- Si c\'est un nombre simple
                            INSERT INTO temp_role_permissions (role_id, permission_id, created_at, updated_at)
                            VALUES (rec.role_id, (rec.permission_id::jsonb)::text::bigint, rec.created_at, rec.updated_at);
                        ELSIF rec.permission_id::text ~ \'^[0-9]+$\' THEN
                            -- Si c\'est une chaîne numérique
                            INSERT INTO temp_role_permissions (role_id, permission_id, created_at, updated_at)
                            VALUES (rec.role_id, (rec.permission_id::text)::bigint, rec.created_at, rec.updated_at);
                        END IF;
                    END LOOP;

                    -- Supprimer toutes les données de la table
                    TRUNCATE TABLE role_permissions;

                    -- Convertir la colonne JSON en bigint
                    EXECUTE \'ALTER TABLE role_permissions ALTER COLUMN permission_id TYPE bigint USING 0::bigint\';

                    -- Insérer les nouvelles données converties
                    INSERT INTO role_permissions (role_id, permission_id, created_at, updated_at)
                    SELECT role_id, permission_id, created_at, updated_at
                    FROM temp_role_permissions;

                    -- Supprimer la table temporaire
                    DROP TABLE temp_role_permissions;
                END $$;
            ');

            // Recréer la contrainte de clé étrangère pour permission_id
            $this->info('Recréation de la contrainte de clé étrangère...');
            DB::statement('
                ALTER TABLE role_permissions
                ADD CONSTRAINT role_permissions_permission_id_foreign
                FOREIGN KEY (permission_id)
                REFERENCES permissions(id)
                ON DELETE CASCADE
            ');

            // Recréer la contrainte de clé étrangère pour role_id si elle n'existe pas
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

            // Recréer les index
            $this->info('Recréation des index...');
            DB::statement('CREATE INDEX IF NOT EXISTS role_permissions_permission_id_index ON role_permissions(permission_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS role_permissions_role_id_index ON role_permissions(role_id)');

            $this->info('✅ Conversion terminée avec succès !');
        } else {
            $this->info('✅ La colonne permission_id est déjà du bon type (bigint).');
        }

        return 0;
    }
}

