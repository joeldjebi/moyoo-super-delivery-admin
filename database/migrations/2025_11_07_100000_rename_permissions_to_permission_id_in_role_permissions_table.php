<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Vérifier si la colonne 'permissions' existe et la renommer en 'permission_id'
        if (Schema::hasColumn('role_permissions', 'permissions')) {
            DB::statement('ALTER TABLE role_permissions RENAME COLUMN permissions TO permission_id');
        }

        // Vérifier le type de la colonne permission_id
        $columnType = DB::selectOne("
            SELECT data_type
            FROM information_schema.columns
            WHERE table_name = 'role_permissions'
            AND column_name = 'permission_id'
        ");

        if ($columnType && ($columnType->data_type === 'json' || $columnType->data_type === 'jsonb')) {
            // Supprimer toutes les contraintes de clé étrangère
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
            DB::statement('DROP INDEX IF EXISTS role_permissions_permission_id_index');
            DB::statement('DROP INDEX IF EXISTS role_permissions_role_id_index');

            // Sauvegarder et convertir les données JSON en bigint
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
            DB::statement('
                ALTER TABLE role_permissions
                ADD CONSTRAINT role_permissions_permission_id_foreign
                FOREIGN KEY (permission_id)
                REFERENCES permissions(id)
                ON DELETE CASCADE
            ');

            // Recréer la contrainte de clé étrangère pour role_id si elle n\'existe pas
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
            DB::statement('CREATE INDEX IF NOT EXISTS role_permissions_permission_id_index ON role_permissions(permission_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS role_permissions_role_id_index ON role_permissions(role_id)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Renommer 'permission_id' en 'permissions' si nécessaire
        if (Schema::hasColumn('role_permissions', 'permission_id')) {
            DB::statement('ALTER TABLE role_permissions RENAME COLUMN permission_id TO permissions');
        }
    }
};
