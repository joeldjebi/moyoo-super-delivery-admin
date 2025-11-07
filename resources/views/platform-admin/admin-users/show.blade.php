@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Gestion /</span> <a href="{{ route('platform-admin.admin-users.index') }}">Administrateurs</a> / Détails
    </h4>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations de l'administrateur</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Nom d'utilisateur:</th>
                            <td>{{ $admin->username }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $admin->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Nom complet:</th>
                            <td>{{ $admin->full_name }}</td>
                        </tr>
                        <tr>
                            <th>Statut:</th>
                            <td>
                                @if($admin->status === 'active')
                                    <span class="badge bg-label-success">Actif</span>
                                @elseif($admin->status === 'inactive')
                                    <span class="badge bg-label-secondary">Inactif</span>
                                @else
                                    <span class="badge bg-label-danger">Suspendu</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Rôles:</th>
                            <td>
                                @foreach($admin->roles as $role)
                                    <span class="badge bg-label-primary me-1">{{ $role->name }}</span>
                                @endforeach
                                @if($admin->roles->isEmpty())
                                    <span class="text-muted">Aucun rôle</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Créé par:</th>
                            <td>{{ $admin->creator->full_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Date de création:</th>
                            <td>{{ $admin->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Dernière connexion:</th>
                            <td>{{ $admin->last_login_at ? $admin->last_login_at->format('d/m/Y H:i') : '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Historique des activités -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Historique des activités</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($admin->activityLogs as $log)
                                    <tr>
                                        <td><span class="badge bg-label-info">{{ $log->action }}</span></td>
                                        <td>{{ $log->description }}</td>
                                        <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">Aucune activité enregistrée</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    @php
                        $currentAdmin = Auth::guard('platform_admin')->user();
                        $isFirstSuperAdmin = $admin->isFirstSuperAdmin();
                        $isCurrentUser = $currentAdmin && $admin->id === $currentAdmin->id;

                        $canEdit = !$isFirstSuperAdmin;
                        $canToggleStatus = !$isFirstSuperAdmin;
                        $canDelete = !$isFirstSuperAdmin && !$isCurrentUser; // Ne peut pas se supprimer lui-même

                        // Seul le premier super admin peut supprimer d'autres super admins qu'il a créés
                        if ($admin->isSuperAdmin() && !$isFirstSuperAdmin && !$isCurrentUser) {
                            $firstSuperAdmin = \App\Models\PlatformAdmin::getFirstSuperAdmin();
                            $canDelete = $firstSuperAdmin &&
                                         $currentAdmin->id === $firstSuperAdmin->id &&
                                         $admin->created_by === $firstSuperAdmin->id;
                        }
                    @endphp

                    @if($canEdit)
                        <a href="{{ route('platform-admin.admin-users.edit', $admin->id) }}" class="btn btn-primary w-100 mb-2">
                            <i class="ti ti-edit me-1"></i> Modifier
                        </a>
                    @else
                        <button type="button" class="btn btn-secondary w-100 mb-2" disabled>
                            <i class="ti ti-lock me-1"></i> Modification interdite
                        </button>
                    @endif

                    @if($canToggleStatus)
                        <form action="{{ route('platform-admin.admin-users.toggle-status', $admin->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-{{ $admin->status === 'active' ? 'warning' : 'success' }} w-100">
                                <i class="ti ti-toggle-{{ $admin->status === 'active' ? 'left' : 'right' }} me-1"></i>
                                {{ $admin->status === 'active' ? 'Désactiver' : 'Activer' }}
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn btn-secondary w-100 mb-2" disabled>
                            <i class="ti ti-lock me-1"></i> Désactivation interdite
                        </button>
                    @endif

                    @if($canDelete)
                        <form action="{{ route('platform-admin.admin-users.destroy', $admin->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet administrateur ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="ti ti-trash me-1"></i> Supprimer
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn btn-secondary w-100" disabled>
                            <i class="ti ti-lock me-1"></i> Suppression interdite
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('platform-admin.layouts.footer')

