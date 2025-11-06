@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">
            Entreprises /
            <a href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}" class="text-muted text-decoration-none">{{ $entreprise->name }}</a> /
            <a href="{{ route('platform-admin.entreprises.boutiques', $entreprise->id) }}" class="text-muted text-decoration-none">Boutiques</a> /
            <a href="{{ route('platform-admin.entreprises.boutique.show', [$entreprise->id, $boutique->id]) }}" class="text-muted text-decoration-none">{{ $boutique->libelle }}</a> /
        </span> Colis
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
                <h5 class="mb-0">Colis de la boutique</h5>
                <p class="text-muted mb-0">{{ $boutique->libelle }}</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label for="per_page" class="small text-muted mb-0">Par page:</label>
                <select id="per_page" class="form-select form-select-sm" style="width: auto;" onchange="updatePerPage(this.value)">
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                </select>
                <a href="{{ route('platform-admin.entreprises.boutique.show', [$entreprise->id, $boutique->id]) }}" class="btn btn-secondary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i> Retour
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Recherche et filtres -->
            <form method="GET" action="{{ route('platform-admin.entreprises.boutique.colis', [$entreprise->id, $boutique->id]) }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Rechercher..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>En attente</option>
                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>En cours</option>
                            <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Terminé</option>
                            <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>Annulé (client)</option>
                            <option value="4" {{ request('status') == '4' ? 'selected' : '' }}>Annulé (livreur)</option>
                            <option value="5" {{ request('status') == '5' ? 'selected' : '' }}>Annulé (marchand)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('platform-admin.entreprises.boutique.colis', [$entreprise->id, $boutique->id]) }}" class="btn btn-secondary w-100">Réinitialiser</a>
                    </div>
                </div>
                @if(request('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
            </form>

            @if($colis->count() > 0)
                <div class="mb-3">
                    <small class="text-muted">
                        Affichage de {{ $colis->firstItem() }} à {{ $colis->lastItem() }} sur {{ $colis->total() }} colis
                    </small>
                </div>
            @endif

            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Client</th>
                            <th>Téléphone</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Date création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($colis as $coli)
                            <tr>
                                <td>{{ $coli->id }}</td>
                                <td><strong>{{ $coli->code }}</strong></td>
                                <td>{{ $coli->nom_client ?? 'N/A' }}</td>
                                <td>{{ $coli->telephone_client ?? 'N/A' }}</td>
                                <td>{{ $coli->prix_de_vente ? number_format($coli->prix_de_vente, 2) . ' FCFA' : 'N/A' }}</td>
                                <td>
                                    @if($coli->status == 0)
                                        <span class="badge bg-label-secondary">En attente</span>
                                    @elseif($coli->status == 1)
                                        <span class="badge bg-label-info">En cours</span>
                                    @elseif($coli->status == 2)
                                        <span class="badge bg-label-success">Terminé</span>
                                    @elseif($coli->status == 3)
                                        <span class="badge bg-label-warning">Annulé (client)</span>
                                    @elseif($coli->status == 4)
                                        <span class="badge bg-label-warning">Annulé (livreur)</span>
                                    @elseif($coli->status == 5)
                                        <span class="badge bg-label-warning">Annulé (marchand)</span>
                                    @else
                                        <span class="badge bg-label-secondary">Inconnu</span>
                                    @endif
                                </td>
                                <td>{{ $coli->created_at ? $coli->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('platform-admin.entreprises.boutique.colis.show', [$entreprise->id, $boutique->id, $coli->id]) }}" class="btn btn-sm btn-primary">
                                        <i class="ti ti-eye me-1"></i> Détails
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Aucun colis trouvé</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($colis->hasPages())
                <div class="mt-4">
                    {{ $colis->links() }}
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

