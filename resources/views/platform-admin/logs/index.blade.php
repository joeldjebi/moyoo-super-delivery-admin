@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')


    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Système /</span> Logs système
    </h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Logs système</h5>
            <div class="d-flex align-items-center gap-2">
                <label for="per_page" class="small text-muted mb-0">Lignes par page:</label>
                <select id="per_page" class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='?per_page=' + this.value">
                    <option value="25" {{ request('per_page', 50) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page', 50) == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ request('per_page', 50) == 200 ? 'selected' : '' }}>200</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            @if($logs->count() > 0)
                <div class="mb-3">
                    <small class="text-muted">
                        Affichage de {{ $logs->firstItem() }} à {{ $logs->lastItem() }} sur {{ $logs->total() }} lignes
                    </small>
                </div>
            @endif
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 180px;">Date et heure</th>
                            <th style="width: 100px;">Niveau</th>
                            <th>Ligne</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $index => $log)
                            @php
                                $level = '';
                                $levelClass = 'secondary';
                                $message = $log['message'] ?? $log['raw'] ?? '';

                                // Détecter le niveau de log
                                if (preg_match('/\.(ERROR|WARNING|INFO|DEBUG|CRITICAL):/i', $message, $matches)) {
                                    $level = strtoupper($matches[1]);
                                    $levelClass = strtolower($matches[1]);
                                    if ($levelClass === 'error') $levelClass = 'danger';
                                    elseif ($levelClass === 'warning') $levelClass = 'warning';
                                    elseif ($levelClass === 'info') $levelClass = 'info';
                                    elseif ($levelClass === 'debug') $levelClass = 'secondary';
                                    elseif ($levelClass === 'critical') $levelClass = 'danger';
                                }
                            @endphp
                            <tr>
                                <td style="vertical-align: top;">
                                    @if($log['datetime'])
                                        <div class="text-muted small">
                                            <strong>{{ \Carbon\Carbon::parse($log['datetime'])->format('d/m/Y') }}</strong><br>
                                            <span>{{ \Carbon\Carbon::parse($log['datetime'])->format('H:i:s') }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td style="vertical-align: top;">
                                    @if($level)
                                        <span class="badge bg-label-{{ $levelClass }}">{{ $level }}</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td style="vertical-align: top;">
                                    <code class="text-wrap" style="white-space: pre-wrap; word-break: break-word; font-size: 0.875rem; font-family: 'Courier New', monospace;">{{ $message }}</code>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">Aucun log disponible</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>


@include('platform-admin.layouts.footer')

