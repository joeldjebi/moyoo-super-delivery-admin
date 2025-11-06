@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Gestion /</span> Toutes les livraisons
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
                <h5 class="mb-0">Toutes les livraisons</h5>
                <p class="text-muted mb-0">Liste de toutes les livraisons (toutes entreprises confondues)</p>
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
            <!-- Filtres -->
            <form method="GET" action="{{ route('platform-admin.global-data.livraisons') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Rechercher (statut, code colis, client, boutique, entreprise)" value="{{ request('search') }}">
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
                            <option value="livre" {{ request('status') == 'livre' ? 'selected' : '' }}>Livré</option>
                            <option value="en_cours" {{ request('status') == 'en_cours' ? 'selected' : '' }}>En cours</option>
                            <option value="annule" {{ request('status') == 'annule' ? 'selected' : '' }}>Annulé</option>
                            <option value="echoue" {{ request('status') == 'echoue' ? 'selected' : '' }}>Échoué</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-search me-1"></i> Rechercher
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('platform-admin.global-data.livraisons') }}" class="btn btn-secondary w-100">
                            <i class="ti ti-x me-1"></i> Réinitialiser
                        </a>
                    </div>
                </div>
                @if(request('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
            </form>

            @if($livraisons->total() > 0)
                <div class="mb-3">
                    <small class="text-muted">
                        Affichage de {{ $livraisons->firstItem() }} à {{ $livraisons->lastItem() }} sur {{ $livraisons->total() }} livraisons
                    </small>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Entreprise</th>
                            <th>Code colis</th>
                            <th>Client</th>
                            <th>Boutique</th>
                            <th>Statut</th>
                            <th style="width: 120px;">Montant livraison</th>
                            <th style="width: 130px;">Date livraison</th>
                            <th style="width: 130px;">Date création</th>
                            <th style="width: 100px;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($livraisons as $livraison)
                            @php
                                $boutiqueId = $livraison->colis && $livraison->colis->packageColis && $livraison->colis->packageColis->boutique_id
                                    ? $livraison->colis->packageColis->boutique_id
                                    : null;
                            @endphp
                            <tr>
                                <td><strong>#{{ $livraison->id }}</strong></td>
                                <td>
                                    @if($livraison->entreprise)
                                        <a href="{{ route('platform-admin.entreprises.show', $livraison->entreprise_id) }}" class="text-primary text-decoration-none">
                                            <strong>{{ $livraison->entreprise->name }}</strong>
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($livraison->colis)
                                        <strong>{{ $livraison->colis->code }}</strong>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($livraison->colis)
                                        {{ $livraison->colis->nom_client ?? 'N/A' }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($livraison->colis && $livraison->colis->packageColis && $livraison->colis->packageColis->boutique)
                                        {{ $livraison->colis->packageColis->boutique->libelle }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($livraison->status == 'livre')
                                        <span class="badge bg-label-success">Livré</span>
                                    @elseif($livraison->status == 'en_cours')
                                        <span class="badge bg-label-primary">En cours</span>
                                    @elseif($livraison->status == 'annule')
                                        <span class="badge bg-label-warning">Annulé</span>
                                    @elseif($livraison->status == 'echoue')
                                        <span class="badge bg-label-danger">Échoué</span>
                                    @else
                                        <span class="badge bg-label-secondary">{{ $livraison->status == 'en_attente' ? 'En attente' : $livraison->status }}</span>
                                    @endif
                                </td>
                                <td>{{ $livraison->montant_de_la_livraison ? number_format($livraison->montant_de_la_livraison, 0, ',', ' ') . ' FCFA' : 'N/A' }}</td>
                                <td>
                                    <small>{{ $livraison->date_livraison_effective ? \Carbon\Carbon::parse($livraison->date_livraison_effective)->format('d/m/Y') : 'N/A' }}</small>
                                    <br><small class="text-muted">{{ $livraison->date_livraison_effective ? \Carbon\Carbon::parse($livraison->date_livraison_effective)->format('H:i') : '' }}</small>
                                </td>
                                <td>
                                    <small>{{ $livraison->created_at ? $livraison->created_at->format('d/m/Y') : 'N/A' }}</small>
                                    <br><small class="text-muted">{{ $livraison->created_at ? $livraison->created_at->format('H:i') : '' }}</small>
                                </td>
                                <td class="text-center">
                                    @if($boutiqueId && $livraison->entreprise_id)
                                        <a href="{{ route('platform-admin.entreprises.boutique.livraison.show', [$livraison->entreprise_id, $boutiqueId, $livraison->id]) }}" class="btn btn-sm btn-info" title="Voir les détails">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <span class="text-muted">Aucune livraison trouvée</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($livraisons->hasPages())
                <div class="mt-4">
                    {{ $livraisons->links() }}
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

