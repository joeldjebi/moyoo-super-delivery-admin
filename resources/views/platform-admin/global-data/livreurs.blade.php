@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Gestion /</span> Tous les livreurs
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
                <h5 class="mb-0">Tous les livreurs</h5>
                <p class="text-muted mb-0">Liste de tous les livreurs (toutes entreprises confondues)</p>
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
            <form method="GET" action="{{ route('platform-admin.global-data.livreurs') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Rechercher (nom, prénom, mobile, email, adresse, entreprise)" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
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
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-search me-1"></i> Rechercher
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('platform-admin.global-data.livreurs') }}" class="btn btn-secondary w-100">
                            <i class="ti ti-x me-1"></i> Réinitialiser
                        </a>
                    </div>
                </div>
                @if(request('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
            </form>

            @if($livreurs->count() > 0)
                <div class="mb-3">
                    <small class="text-muted">
                        Affichage de {{ $livreurs->firstItem() }} à {{ $livreurs->lastItem() }} sur {{ $livreurs->total() }} livreurs
                    </small>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Entreprise</th>
                            <th>Nom complet</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Adresse</th>
                            <th>Statut</th>
                            <th style="width: 130px;">Créé le</th>
                            <th style="width: 100px;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($livreurs as $livreur)
                            <tr>
                                <td><strong>#{{ $livreur->id }}</strong></td>
                                <td>
                                    @if($livreur->entreprise)
                                        <a href="{{ route('platform-admin.entreprises.show', $livreur->entreprise_id) }}" class="text-primary text-decoration-none">
                                            <strong>{{ $livreur->entreprise->name }}</strong>
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $livreur->first_name ?? '' }} {{ $livreur->last_name ?? '' }}</strong>
                                </td>
                                <td>{{ $livreur->email ?? 'N/A' }}</td>
                                <td>{{ $livreur->mobile ?? 'N/A' }}</td>
                                <td>{{ $livreur->adresse ?? 'N/A' }}</td>
                                <td>
                                    @if(($livreur->status ?? 'active') == 'active')
                                        <span class="badge bg-label-success">Actif</span>
                                    @elseif(($livreur->status ?? '') == 'inactive')
                                        <span class="badge bg-label-secondary">Inactif</span>
                                    @elseif(($livreur->status ?? '') == 'suspended')
                                        <span class="badge bg-label-warning">Suspendu</span>
                                    @else
                                        <span class="badge bg-label-secondary">{{ $livreur->status ?? 'N/A' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $livreur->created_at ? $livreur->created_at->format('d/m/Y') : 'N/A' }}</small>
                                    <br><small class="text-muted">{{ $livreur->created_at ? $livreur->created_at->format('H:i') : '' }}</small>
                                </td>
                                <td class="text-center">
                                    @if($livreur->entreprise_id)
                                        <a href="{{ route('platform-admin.entreprises.livreur.show', [$livreur->entreprise_id, $livreur->id]) }}" class="btn btn-sm btn-info" title="Voir les détails">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <span class="text-muted">Aucun livreur trouvé</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($livreurs->hasPages())
                <div class="mt-4">
                    {{ $livreurs->links() }}
                </div>
            @endif
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

