@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-2 mb-2">
        <span class="text-muted fw-light">
            Entreprises /
            <a href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}" class="text-muted text-decoration-none">{{ $entreprise->name }}</a> /
            <a href="{{ route('platform-admin.entreprises.boutiques', $entreprise->id) }}" class="text-muted text-decoration-none">Boutiques</a> /
            <a href="{{ route('platform-admin.entreprises.boutique.show', [$entreprise->id, $boutique->id]) }}" class="text-muted text-decoration-none">{{ $boutique->libelle }}</a> /
        </span> Historique Livraisons
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
                <h5 class="mb-0">Historique Livraisons de la boutique</h5>
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
            <form method="GET" action="{{ route('platform-admin.entreprises.boutique.livraisons', [$entreprise->id, $boutique->id]) }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Rechercher..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="livre" {{ request('status') == 'livre' ? 'selected' : '' }}>Livré</option>
                            <option value="en_cours" {{ request('status') == 'en_cours' ? 'selected' : '' }}>En cours</option>
                            <option value="annule" {{ request('status') == 'annule' ? 'selected' : '' }}>Annulé</option>
                            <option value="echoue" {{ request('status') == 'echoue' ? 'selected' : '' }}>Échoué</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('platform-admin.entreprises.boutique.livraisons', [$entreprise->id, $boutique->id]) }}" class="btn btn-secondary w-100">Réinitialiser</a>
                    </div>
                </div>
                @if(request('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
            </form>

            @if($livraisons->count() > 0)
                <div class="mb-3">
                    <small class="text-muted">
                        Affichage de {{ $livraisons->firstItem() }} à {{ $livraisons->lastItem() }} sur {{ $livraisons->total() }} livraisons
                    </small>
                </div>
            @endif

            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Colis</th>
                            <th>Statut</th>
                            <th>Code validation</th>
                            <th>Montant livraison</th>
                            <th>Date livraison</th>
                            <th>Motif annulation</th>
                            <th>Note</th>
                            <th>Créé le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($livraisons as $livraison)
                            <tr>
                                <td>{{ $livraison->id }}</td>
                                <td>
                                    @if($livraison->colis)
                                        <strong>{{ $livraison->colis->code }}</strong>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($livraison->status == 'livre')
                                        <span class="badge bg-label-success">Livré</span>
                                    @elseif($livraison->status == 'en_cours')
                                        <span class="badge bg-label-info">En cours</span>
                                    @elseif($livraison->status == 'annule')
                                        <span class="badge bg-label-warning">Annulé</span>
                                    @elseif($livraison->status == 'echoue')
                                        <span class="badge bg-label-danger">Échoué</span>
                                    @else
                                        <span class="badge bg-label-secondary">{{ $livraison->status == 'en_attente' ? 'En attente' : $livraison->status }}</span>
                                    @endif
                                </td>
                                <td>{{ $livraison->code_validation_utilise ?? 'N/A' }}</td>
                                <td>{{ $livraison->montant_de_la_livraison ? number_format($livraison->montant_de_la_livraison, 0) . ' FCFA' : 'N/A' }}</td>
                                <td>{{ $livraison->date_livraison_effective ? $livraison->date_livraison_effective->format('d/m/Y H:i') : 'N/A' }}</td>
                                <td>{{ $livraison->motif_annulation ?? 'N/A' }}</td>
                                <td>{{ $livraison->note_livraison ? \Illuminate\Support\Str::limit($livraison->note_livraison, 50) : 'N/A' }}</td>
                                <td>{{ $livraison->created_at ? $livraison->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('platform-admin.entreprises.boutique.livraison.show', [$entreprise->id, $boutique->id, $livraison->id]) }}" class="btn btn-sm btn-primary">
                                        <i class="ti ti-eye me-1"></i> Détails
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">Aucune livraison trouvée</td>
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

