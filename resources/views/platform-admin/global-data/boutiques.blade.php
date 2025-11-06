@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Gestion /</span> Toutes les boutiques
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
                <h5 class="mb-0">Toutes les boutiques</h5>
                <p class="text-muted mb-0">Liste de toutes les boutiques (toutes entreprises confondues)</p>
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
            <form method="GET" action="{{ route('platform-admin.global-data.boutiques') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Rechercher (libellé, mobile, adresse, marchand, entreprise)" value="{{ request('search') }}">
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
                        <a href="{{ route('platform-admin.global-data.boutiques') }}" class="btn btn-secondary w-100">
                            <i class="ti ti-x me-1"></i> Réinitialiser
                        </a>
                    </div>
                </div>
                @if(request('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
            </form>

            @if($boutiques->count() > 0)
                <div class="mb-3">
                    <small class="text-muted">
                        Affichage de {{ $boutiques->firstItem() }} à {{ $boutiques->lastItem() }} sur {{ $boutiques->total() }} boutiques
                    </small>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Entreprise</th>
                            <th>Marchand</th>
                            <th>Boutique</th>
                            <th>Mobile</th>
                            <th>Adresse</th>
                            <th>Statut</th>
                            <th style="width: 130px;">Créé le</th>
                            <th style="width: 100px;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($boutiques as $boutique)
                            <tr>
                                <td><strong>#{{ $boutique->id }}</strong></td>
                                <td>
                                    @if($boutique->entreprise)
                                        <a href="{{ route('platform-admin.entreprises.show', $boutique->entreprise_id) }}" class="text-primary text-decoration-none">
                                            <strong>{{ $boutique->entreprise->name }}</strong>
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($boutique->marchand)
                                        {{ $boutique->marchand->first_name ?? '' }} {{ $boutique->marchand->last_name ?? '' }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td><strong>{{ $boutique->libelle }}</strong></td>
                                <td>{{ $boutique->mobile ?? 'N/A' }}</td>
                                <td>{{ $boutique->adresse ?? 'N/A' }}</td>
                                <td>
                                    @if($boutique->status == 'active')
                                        <span class="badge bg-label-success">Actif</span>
                                    @elseif($boutique->status == 'inactive')
                                        <span class="badge bg-label-danger">Inactif</span>
                                    @else
                                        <span class="badge bg-label-warning">Suspendu</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $boutique->created_at ? $boutique->created_at->format('d/m/Y') : 'N/A' }}</small>
                                    <br><small class="text-muted">{{ $boutique->created_at ? $boutique->created_at->format('H:i') : '' }}</small>
                                </td>
                                <td class="text-center">
                                    @if($boutique->entreprise_id)
                                        <a href="{{ route('platform-admin.entreprises.boutique.show', [$boutique->entreprise_id, $boutique->id]) }}" class="btn btn-sm btn-info" title="Voir les détails">
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
                                    <span class="text-muted">Aucune boutique trouvée</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($boutiques->hasPages())
                <div class="mt-4">
                    {{ $boutiques->links() }}
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

