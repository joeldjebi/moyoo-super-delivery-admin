<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('role_permissions')) {
            return;
        }

        $hasRoleId = Schema::hasColumn('role_permissions', 'role_id');
        $hasPermissionId = Schema::hasColumn('role_permissions', 'permission_id');

        // Déjà au bon format (pivot normalisé)
        if ($hasRoleId && $hasPermissionId) {
            return;
        }

        // Ancien format détecté: role (varchar) + permissions (json)
        $hasRole = Schema::hasColumn('role_permissions', 'role');
        $hasPermissionsJson = Schema::hasColumn('role_permissions', 'permissions');

        $rowsToInsert = [];

        if ($hasRole && $hasPermissionsJson) {
            $legacyRows = DB::table('role_permissions')->get(['role', 'permissions', 'created_at', 'updated_at']);

            foreach ($legacyRows as $row) {
                $roleValue = $row->role;
                $roleId = DB::table('roles')
                    ->where('slug', $roleValue)
                    ->orWhere('name', $roleValue)
                    ->value('id');

                if (!$roleId) {
                    continue;
                }

                $permissionIds = json_decode($row->permissions ?? '[]', true);

                if (!is_array($permissionIds)) {
                    continue;
                }

                foreach ($permissionIds as $permissionId) {
                    if (!is_int($permissionId) && !(is_string($permissionId) && ctype_digit($permissionId))) {
                        continue;
                    }

                    $rowsToInsert[] = [
                        'role_id' => (int) $roleId,
                        'permission_id' => (int) $permissionId,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ];
                }
            }
        }

        // Recréer la table au format attendu par Laravel (role_id / permission_id)
        Schema::drop('role_permissions');

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->timestamps();

            // Nom court pour éviter les limites MySQL
            $table->unique(['role_id', 'permission_id'], 'role_perm_unique');
            $table->index('role_id');
            $table->index('permission_id');
        });

        if (!empty($rowsToInsert)) {
            // Éviter les doublons si l'ancien JSON contenait des répétitions
            $unique = [];
            foreach ($rowsToInsert as $r) {
                $key = $r['role_id'] . ':' . $r['permission_id'];
                $unique[$key] = $r;
            }

            DB::table('role_permissions')->insert(array_values($unique));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir à l'ancien format (role + permissions JSON) si besoin.
        if (!Schema::hasTable('role_permissions')) {
            return;
        }

        $hasRoleId = Schema::hasColumn('role_permissions', 'role_id');
        $hasPermissionId = Schema::hasColumn('role_permissions', 'permission_id');

        if (!$hasRoleId || !$hasPermissionId) {
            return;
        }

        // Conversion inverse non fiable (perte d'info possible), on recrée juste la structure legacy vide.
        Schema::drop('role_permissions');

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->json('permissions');
            $table->unsignedBigInteger('entreprise_id')->nullable();
            $table->timestamps();

            $table->index('role');
            $table->index('entreprise_id');
        });
    }
};
