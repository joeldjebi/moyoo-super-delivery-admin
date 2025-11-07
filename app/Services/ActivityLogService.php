<?php

namespace App\Services;

use App\Models\AdminActivityLog;
use App\Models\PlatformAdmin;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Enregistrer une action dans les logs
     */
    public function log(
        PlatformAdmin $admin,
        string $action,
        ?string $modelType = null,
        ?int $modelId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): AdminActivityLog {
        return AdminActivityLog::create([
            'platform_admin_id' => $admin->id,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Obtenir les logs d'un admin
     */
    public function getAdminLogs(PlatformAdmin $admin, int $limit = 50)
    {
        return AdminActivityLog::where('platform_admin_id', $admin->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtenir les logs pour un modèle spécifique
     */
    public function getModelLogs(string $modelType, int $modelId, int $limit = 50)
    {
        return AdminActivityLog::where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtenir tous les logs récents
     */
    public function getRecentLogs(int $limit = 100)
    {
        return AdminActivityLog::with('admin')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}

