@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Gestion /</span> <a href="{{ route('platform-admin.roles.index') }}" class="text-decoration-none">Rôles</a> / Détails
        </h4>
        <div>
            <a href="{{ route('platform-admin.roles.index') }}" class="btn btn-label-secondary">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
            <a href="{{ route('platform-admin.roles.edit', $role->id) }}" class="btn btn-primary">
                <i class="ti ti-edit me-1"></i> Modifier
            </a>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-users"></i>
                            </span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <h6 class="mb-0">Administrateurs</h6>
                                <small class="text-muted">Avec ce rôle</small>
                            </div>
                            <div class="user-progress d-flex align-items-center gap-1">
                                <h6 class="mb-0">{{ $role->admins->count() }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ti ti-shield-check"></i>
                            </span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <h6 class="mb-0">Permissions</h6>
                                <small class="text-muted">Attribuées</small>
                            </div>
                            <div class="user-progress d-flex align-items-center gap-1">
                                <h6 class="mb-0">{{ $role->permissions->count() }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-{{ $role->is_system_role ? 'warning' : 'info' }}">
                                <i class="ti ti-{{ $role->is_system_role ? 'lock' : 'key' }}"></i>
                            </span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <h6 class="mb-0">Type</h6>
                                <small class="text-muted">Rôle</small>
                            </div>
                            <div class="user-progress d-flex align-items-center gap-1">
                                @if($role->is_system_role)
                                    <span class="badge bg-label-warning">Système</span>
                                @else
                                    <span class="badge bg-label-info">Personnalisé</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-secondary">
                                <i class="ti ti-calendar"></i>
                            </span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <h6 class="mb-0">Créé le</h6>
                                <small class="text-muted">Date</small>
                            </div>
                            <div class="user-progress d-flex align-items-center gap-1">
                                <small class="text-muted">{{ $role->created_at->format('d/m/Y') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Informations du rôle -->
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">
                        <i class="ti ti-info-circle me-2"></i>Informations du rôle
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Nom du rôle</label>
                            <div class="d-flex align-items-center">
                                <i class="ti ti-user-circle me-2 text-primary"></i>
                                <span class="fw-semibold">{{ $role->name }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Slug</label>
                            <div class="d-flex align-items-center">
                                <i class="ti ti-code me-2 text-primary"></i>
                                <code class="bg-label-primary px-2 py-1 rounded">{{ $role->slug }}</code>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label text-muted">Description</label>
                            <div class="d-flex align-items-start">
                                <i class="ti ti-file-text me-2 text-primary mt-1"></i>
                                <p class="mb-0">{{ $role->description ?? '<span class="text-muted">Aucune description</span>' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions -->
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">
                        <i class="ti ti-shield-check me-2"></i>Permissions du rôle
                        <span class="badge bg-label-primary ms-2">{{ $role->permissions->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if($role->permissions->count() > 0)
                        <div class="accordion" id="permissionsAccordion">
                            @foreach($role->permissions->groupBy('resource') as $resource => $permissions)
                                @php
                                    $index = $loop->index;
                                    $resourceId = 'resource_' . $index;
                                    $isFirst = $loop->first;
                                @endphp
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading{{ $index }}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $resourceId }}" aria-expanded="{{ $isFirst ? 'true' : 'false' }}" aria-controls="{{ $resourceId }}">
                                            <div class="d-flex align-items-center w-100">
                                                <i class="ti ti-folder me-2 text-primary"></i>
                                                <span class="text-capitalize fw-semibold me-2">{{ $resource }}</span>
                                                <span class="badge bg-label-secondary">{{ $permissions->count() }} permission(s)</span>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="{{ $resourceId }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $index }}" data-bs-parent="#permissionsAccordion">
                                        <div class="accordion-body">
                                            <div class="row g-3">
                                                @foreach($permissions as $permission)
                                                    <div class="col-md-6">
                                                        <div class="card border h-100">
                                                            <div class="card-body p-3">
                                                                <div class="d-flex align-items-start">
                                                                    <i class="ti ti-check-circle text-success me-2 mt-1"></i>
                                                                    <div class="flex-grow-1">
                                                                        <h6 class="mb-1 fw-semibold">{{ $permission->name }}</h6>
                                                                        <div class="mb-2">
                                                                            <code class="bg-label-primary px-2 py-1 rounded">{{ $permission->action }}</code>
                                                                        </div>
                                                                        @if($permission->description)
                                                                            <p class="text-muted small mb-0">{{ $permission->description }}</p>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ti ti-shield-off text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3 mb-0">Aucune permission attribuée à ce rôle.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Administrateurs avec ce rôle -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">
                        <i class="ti ti-users me-2"></i>Administrateurs avec ce rôle
                        <span class="badge bg-label-primary ms-2">{{ $role->admins->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if($role->admins->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom d'utilisateur</th>
                                        <th>Nom complet</th>
                                        <th>Email</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($role->admins as $admin)
                                        <tr>
                                            <td><span class="badge bg-label-secondary">#{{ $admin->id }}</span></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2">
                                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                                            {{ strtoupper(substr($admin->username, 0, 1)) }}
                                                        </span>
                                                    </div>
                                                    <span class="fw-semibold">{{ $admin->username }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $admin->full_name }}</td>
                                            <td>
                                                @if($admin->email)
                                                    <a href="mailto:{{ $admin->email }}" class="text-decoration-none">{{ $admin->email }}</a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($admin->status === 'active')
                                                    <span class="badge bg-label-success">Actif</span>
                                                @elseif($admin->status === 'inactive')
                                                    <span class="badge bg-label-secondary">Inactif</span>
                                                @else
                                                    <span class="badge bg-label-danger">Suspendu</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('platform-admin.admin-users.show', $admin->id) }}" class="btn btn-sm btn-label-primary">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ti ti-user-off text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3 mb-0">Aucun administrateur n'a ce rôle.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-settings me-2"></i>Actions
                    </h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('platform-admin.roles.edit', $role->id) }}" class="btn btn-primary w-100 mb-2">
                        <i class="ti ti-edit me-1"></i> Modifier le rôle
                    </a>
                    @if(!$role->is_system_role)
                        <form action="{{ route('platform-admin.roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce rôle ? Cette action est irréversible.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="ti ti-trash me-1"></i> Supprimer le rôle
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn btn-label-secondary w-100" disabled>
                            <i class="ti ti-lock me-1"></i> Rôle système (non supprimable)
                        </button>
                    @endif
                </div>
            </div>

            <!-- Informations supplémentaires -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-info-circle me-2"></i>Informations
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Date de création</label>
                        <div class="d-flex align-items-center">
                            <i class="ti ti-calendar me-2 text-primary"></i>
                            <span>{{ $role->created_at->format('d/m/Y à H:i') }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Dernière modification</label>
                        <div class="d-flex align-items-center">
                            <i class="ti ti-clock me-2 text-primary"></i>
                            <span>{{ $role->updated_at->format('d/m/Y à H:i') }}</span>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-0">
                        <label class="form-label text-muted small">Nombre d'administrateurs</label>
                        <div class="d-flex align-items-center">
                            <i class="ti ti-users me-2 text-primary"></i>
                            <span class="fw-semibold">{{ $role->admins->count() }} administrateur(s)</span>
                        </div>
                    </div>
                    <div class="mb-0 mt-3">
                        <label class="form-label text-muted small">Nombre de permissions</label>
                        <div class="d-flex align-items-center">
                            <i class="ti ti-shield-check me-2 text-primary"></i>
                            <span class="fw-semibold">{{ $role->permissions->count() }} permission(s)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('platform-admin.layouts.footer')
