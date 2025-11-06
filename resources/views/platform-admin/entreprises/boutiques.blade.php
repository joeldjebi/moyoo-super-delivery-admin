@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Entreprises / <a href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}" class="text-muted text-decoration-none">{{ $entreprise->name }}</a> /</span> Boutiques
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
                <h5 class="mb-0">Boutiques</h5>
                <p class="text-muted mb-0">Liste des boutiques de l'entreprise</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label for="per_page" class="small text-muted mb-0">Par page:</label>
                <select id="per_page" class="form-select form-select-sm" style="width: auto;" onchange="updatePerPage(this.value)">
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                </select>
                <a href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}" class="btn btn-secondary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i> Retour
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Recherche et filtres -->
            <form method="GET" action="{{ route('platform-admin.entreprises.boutiques', $entreprise->id) }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Rechercher..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspendu</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="marchand_id" class="form-select">
                            <option value="">Tous les marchands</option>
                            @foreach($marchands as $marchand)
                                <option value="{{ $marchand->id }}" {{ request('marchand_id') == $marchand->id ? 'selected' : '' }}>
                                    {{ $marchand->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('platform-admin.entreprises.boutiques', $entreprise->id) }}" class="btn btn-secondary w-100">Réinitialiser</a>
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

            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Marchand</th>
                            <th>Mobile</th>
                            <th>Boutique</th>
                            <th>Adresse</th>
                            <th>Adresse GPS</th>
                            <th>Statut</th>
                            <th>Créé le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($boutiques as $index => $boutique)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if($boutique->marchand)
                                        <a href="#" class="text-decoration-none">
                                            {{ $boutique->marchand->full_name }}
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $boutique->mobile ?? 'N/A' }}</td>
                                <td><strong>{{ $boutique->libelle }}</strong></td>
                                <td>{{ $boutique->adresse ?? 'N/A' }}</td>
                                <td>
                                    @if($boutique->adresse_gps)
                                        <a href="https://www.google.com/maps?q={{ $boutique->adresse_gps }}" target="_blank" class="text-primary">
                                            <i class="ti ti-map me-1"></i> Voir sur Google Maps
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($boutique->status == 'active')
                                        <span class="badge bg-label-success">Actif</span>
                                    @elseif($boutique->status == 'inactive')
                                        <span class="badge bg-label-danger">Inactif</span>
                                    @else
                                        <span class="badge bg-label-warning">Suspendu</span>
                                    @endif
                                </td>
                                <td>{{ $boutique->created_at ? $boutique->created_at->format('d/m/Y') : 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('platform-admin.entreprises.boutique.show', [$entreprise->id, $boutique->id]) }}" class="btn btn-sm btn-primary">
                                        <i class="ti ti-eye me-1"></i> Détails
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Aucune boutique trouvée</td>
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
    url.searchParams.set('page', '1'); // Reset to first page
    window.location.href = url.toString();
}
</script>

@include('platform-admin.layouts.footer')

