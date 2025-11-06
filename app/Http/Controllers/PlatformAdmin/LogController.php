<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $data['title'] = 'Logs système';
        $data['menu'] = 'logs';
        $logFile = storage_path('logs/laravel.log');
        $logs = [];

        if (File::exists($logFile)) {
            $content = File::get($logFile);
            $lines = explode("\n", $content);

            // Parser les logs ligne par ligne
            $parsedLogs = [];
            $lastDateTime = null;
            $totalLineNumber = 0;

            // Traiter toutes les lignes (pas seulement les 500 dernières)
            foreach ($lines as $line) {
                $line = rtrim($line); // Retirer seulement le retour à la ligne à droite

                // Ignorer les lignes vides
                if (empty($line)) {
                    continue;
                }

                $datetime = null;
                $message = $line;

                // Format Laravel: [2025-11-02 23:00:00] local.ERROR: Message...
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+(.+)$/', $line, $matches)) {
                    $datetime = $matches[1];
                    $message = $matches[2];
                    $lastDateTime = $datetime; // Garder en mémoire pour les lignes suivantes
                } else {
                    // Si la ligne ne commence pas par [date], utiliser la date de la ligne précédente
                    $datetime = $lastDateTime;
                }

                $parsedLogs[] = [
                    'datetime' => $datetime,
                    'message' => $message,
                    'raw' => $line,
                    'line_number' => ++$totalLineNumber,
                ];
            }

            // Inverser pour avoir les plus récents en premier
            $parsedLogs = array_reverse($parsedLogs);

            // Pagination
            $perPage = $request->get('per_page', 50); // 50 lignes par page par défaut
            $currentPage = Paginator::resolveCurrentPage('page');
            $currentItems = array_slice($parsedLogs, ($currentPage - 1) * $perPage, $perPage);

            $logs = new LengthAwarePaginator(
                $currentItems,
                count($parsedLogs),
                $perPage,
                $currentPage,
                [
                    'path' => request()->url(),
                    'pageName' => 'page',
                ]
            );

            // Ajouter les paramètres de requête à la pagination
            $logs->appends(request()->query());
        } else {
            $logs = new LengthAwarePaginator([], 0, 50, 1);
        }

        return view('platform-admin.logs.index', array_merge($data, compact('logs')));
    }

    public function show(string $id)
    {
        $logFile = storage_path('logs/laravel.log');

        if (!File::exists($logFile)) {
            abort(404);
        }

        $content = File::get($logFile);
        $lines = explode("\n", $content);

        return view('platform-admin.logs.show', [
            'logs' => $lines,
            'selected_line' => $id,
        ]);
    }
}
