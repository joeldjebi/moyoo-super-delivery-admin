@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Gestion /</span> Administrateurs
    </h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Liste des administrateurs</h5>
                <p class="text-muted mb-0">Gestion des administrateurs de la plateforme</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('platform-admin.admin-users.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Nouvel administrateur
                </a>
                <label for="per_page" class="small text-muted mb-0">Par page:</label>
                <select id="per_page" class="form-select form-select-sm" style="width: auto;" onchange="updatePerPage(this.value)">
                    <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                    <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page', 15) == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <!-- Recherche et filtres -->
            <form method="GET" action="{{ route('platform-admin.admin-users.index') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Rechercher..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="role" class="form-select">
                            <option value="">Tous les rôles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspendu</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('platform-admin.admin-users.index') }}" class="btn btn-secondary w-100">Réinitialiser</a>
                    </div>
                </div>
                <input type="hidden" name="per_page" value="{{ request('per_page', 15) }}">
            </form>

            <!-- Tableau -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom d'utilisateur</th>
                            <th>Nom complet</th>
                            <th>Email</th>
                            <th>Rôles</th>
                            <th>Statut</th>
                            <th>Créé par</th>
                            <th>Date création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                            <tr>
                                <td>{{ $admin->id }}</td>
                                <td>{{ $admin->username }}</td>
                                <td>{{ $admin->full_name }}</td>
                                <td>{{ $admin->email ?? '-' }}</td>
                                <td>
                                    @foreach($admin->roles as $role)
                                        <span class="badge bg-label-primary me-1">{{ $role->name }}</span>
                                    @endforeach
                                    @if($admin->roles->isEmpty())
                                        <span class="text-muted">Aucun rôle</span>
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
                                <td>{{ $admin->creator->full_name ?? '-' }}</td>
                                <td>{{ $admin->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="{{ route('platform-admin.admin-users.show', $admin->id) }}"><i class="ti ti-eye me-2"></i> Voir</a></li>
                                            <li><a class="dropdown-item" href="{{ route('platform-admin.admin-users.edit', $admin->id) }}"><i class="ti ti-edit me-2"></i> Modifier</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('platform-admin.admin-users.toggle-status', $admin->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="ti ti-toggle-{{ $admin->status === 'active' ? 'left' : 'right' }} me-2"></i>
                                                        {{ $admin->status === 'active' ? 'Désactiver' : 'Activer' }}
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="{{ route('platform-admin.admin-users.destroy', $admin->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet administrateur ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="ti ti-trash me-2"></i> Supprimer
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Aucun administrateur trouvé</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <p class="text-muted mb-0">
                        Affichage de {{ $admins->firstItem() ?? 0 }} à {{ $admins->lastItem() ?? 0 }} sur {{ $admins->total() }} administrateurs
                    </p>
                </div>
                <div>
                    {{ $admins->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updatePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
}
</script>

@include('platform-admin.layouts.footer')

