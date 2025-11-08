@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Gestion /</span> Rôles
        </h4>
        <div>
            <a href="{{ route('platform-admin.roles.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Nouveau rôle
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">
                    <i class="ti ti-user-circle me-2"></i>Liste des rôles
                </h5>
                <p class="text-muted mb-0">Gestion des rôles et permissions</p>
            </div>
        </div>
        <div class="card-body">
            <!-- Recherche -->
            <form method="GET" action="{{ route('platform-admin.roles.index') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-10">
                        <input type="text" class="form-control" name="search" placeholder="Rechercher par nom, slug ou description..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-search me-1"></i> Rechercher
                        </button>
                    </div>
                </div>
            </form>

            <!-- Tableau -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Admins</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td><span class="badge bg-label-secondary">#{{ $role->id }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="ti ti-user-circle"></i>
                                            </span>
                                        </div>
                                        <span class="fw-semibold">{{ $role->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <code class="bg-label-primary px-2 py-1 rounded">{{ $role->slug }}</code>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $role->description ? \Illuminate\Support\Str::limit($role->description, 50) : '-' }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="ti ti-users me-1 text-primary"></i>
                                        <span class="fw-semibold">{{ $role->admins_count ?? 0 }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($role->is_system_role)
                                        <span class="badge bg-label-warning">
                                            <i class="ti ti-lock me-1"></i>Système
                                        </span>
                                    @else
                                        <span class="badge bg-label-info">
                                            <i class="ti ti-key me-1"></i>Personnalisé
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('platform-admin.roles.show', $role->id) }}">
                                                    <i class="ti ti-eye me-2"></i> Voir les détails
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('platform-admin.roles.edit', $role->id) }}">
                                                    <i class="ti ti-edit me-2"></i> Modifier
                                                </a>
                                            </li>
                                            @if(!$role->is_system_role)
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('platform-admin.roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce rôle ? Cette action est irréversible.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="ti ti-trash me-2"></i> Supprimer
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="ti ti-inbox text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-3 mb-0">Aucun rôle trouvé</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($roles->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <p class="text-muted mb-0">
                            Affichage de <strong>{{ $roles->firstItem() ?? 0 }}</strong> à <strong>{{ $roles->lastItem() ?? 0 }}</strong> sur <strong>{{ $roles->total() }}</strong> rôle(s)
                        </p>
                    </div>
                    <div>
                        {{ $roles->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

@include('platform-admin.layouts.footer')
