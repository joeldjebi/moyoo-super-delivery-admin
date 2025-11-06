@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')


            <h4 class="fw-bold py-3 mb-4">
                <span class="text-muted fw-light">Gestion /</span> Utilisateurs
            </h4>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Liste des utilisateurs</h5>
                        <p class="text-muted mb-0">Vous pouvez consulter et supprimer les utilisateurs</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label for="per_page" class="small text-muted mb-0">Par page:</label>
                        <select id="per_page" class="form-select form-select-sm" style="width: auto;" onchange="updatePerPage(this.value)">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Recherche et filtres -->
                    <form method="GET" action="{{ route('platform-admin.users.index') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="search" placeholder="Rechercher..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="entreprise_id" class="form-select">
                                    <option value="">Toutes les entreprises</option>
                                    @foreach($entreprises as $entreprise)
                                        <option value="{{ $entreprise->id }}" {{ request('entreprise_id') == $entreprise->id ? 'selected' : '' }}>
                                            {{ $entreprise->name }}
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
                                <select name="user_type" class="form-select">
                                    <option value="entreprise_admin" {{ !request('user_type') || request('user_type') == 'entreprise_admin' ? 'selected' : '' }}>Admin Entreprise</option>
                                    <option value="entreprise_user" {{ request('user_type') == 'entreprise_user' ? 'selected' : '' }}>Utilisateur</option>
                                    <option value="super_admin" {{ request('user_type') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                    <option value="">Tous les types</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <a href="{{ route('platform-admin.users.index') }}" class="btn btn-secondary btn-sm">Réinitialiser</a>
                            </div>
                        </div>
                        @if(request('per_page'))
                            <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                        @endif
                    </form>

                    @if($users->count() > 0)
                        <div class="mb-3">
                            <small class="text-muted">
                                Affichage de {{ $users->firstItem() }} à {{ $users->lastItem() }} sur {{ $users->total() }} utilisateurs
                            </small>
                        </div>
                    @endif

                    <div class="table-responsive text-nowrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Entreprise</th>
                                    <th>Statut</th>
                                    <th>Rôle</th>
                                    <th>Type</th>
                                    <th>Créé le</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @forelse($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>
                                            <strong>{{ $user->first_name }} {{ $user->last_name }}</strong>
                                        </td>
                                        <td>{{ $user->email ?? 'N/A' }}</td>
                                        <td>{{ $user->mobile ?? 'N/A' }}</td>
                                        <td>
                                            @if($user->entreprise_name)
                                                <a href="{{ route('platform-admin.entreprises.show', $user->entreprise_id) }}" class="text-primary">
                                                    {{ $user->entreprise_name }}
                                                </a>
                                                @if($user->entreprise_statut != 1)
                                                    <span class="badge bg-label-warning ms-1">Entreprise inactive</span>
                                                @endif
                                            @else
                                                <span class="text-muted">Aucune</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(($user->status ?? 'active') == 'active')
                                                <span class="badge bg-label-success">Actif</span>
                                            @elseif(($user->status ?? '') == 'inactive')
                                                <span class="badge bg-label-danger">Inactif</span>
                                            @elseif(($user->status ?? '') == 'suspended')
                                                <span class="badge bg-label-warning">Suspendu</span>
                                            @else
                                                <span class="badge bg-label-secondary">{{ $user->status ?? 'N/A' }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-label-info">{{ $user->role ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @if(($user->user_type ?? 'entreprise_user') == 'super_admin')
                                                <span class="badge bg-label-warning">Super Admin</span>
                                            @elseif(($user->user_type ?? '') == 'entreprise_admin')
                                                <span class="badge bg-label-primary">Admin Entreprise</span>
                                            @else
                                                <span class="badge bg-label-secondary">Utilisateur</span>
                                            @endif
                                        </td>
                                        <td>{{ isset($user->created_at) ? \Carbon\Carbon::parse($user->created_at)->format('d/m/Y') : 'N/A' }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="{{ route('platform-admin.users.show', $user->id) }}">
                                                        <i class="ti ti-eye me-1"></i> Voir les détails
                                                    </a>
                                                    <form action="{{ route('platform-admin.users.destroy', $user->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                                            <i class="ti ti-trash me-1"></i> Supprimer
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">Aucun utilisateur trouvé</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($users->hasPages())
                        <div class="mt-4">
                            {{ $users->links() }}
                        </div>
                    @endif
                </div>
            </div>

<script>
function updatePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('page', '1'); // Reset to first page
    window.location.href = url.toString();
}
</script>

@include('platform-admin.layouts.footer')
