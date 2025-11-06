@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">
            Entreprises /
            <a href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}" class="text-muted text-decoration-none">{{ $entreprise->name }}</a> /
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
            <h5 class="mb-0">Historique des livraisons</h5>
            <a href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}" class="btn btn-secondary btn-sm">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
        </div>
        <div class="card-body">
            <!-- Filtres -->
            <form method="GET" action="{{ route('platform-admin.entreprises.livraisons', $entreprise->id) }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Rechercher (statut, code colis, client, boutique)" value="{{ request('search') }}">
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
                        <select name="per_page" class="form-select" onchange="this.form.submit()">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 par page</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 par page</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 par page</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 par page</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-search me-1"></i> Rechercher
                            </button>
                            <a href="{{ route('platform-admin.entreprises.livraisons', $entreprise->id) }}" class="btn btn-secondary">
                                <i class="ti ti-x me-1"></i> Réinitialiser
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            @if($livraisons->total() > 0)
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
                            <th>Code colis</th>
                            <th>Client</th>
                            <th>Boutique</th>
                            <th>Statut</th>
                            <th>Montant livraison</th>
                            <th>Date livraison</th>
                            <th>Date création</th>
                            <th>Actions</th>
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
                                <td>{{ $livraison->id }}</td>
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
                                <td>{{ $livraison->montant_de_la_livraison ? number_format($livraison->montant_de_la_livraison, 2) . ' FCFA' : 'N/A' }}</td>
                                <td>{{ $livraison->date_livraison_effective ? \Carbon\Carbon::parse($livraison->date_livraison_effective)->format('d/m/Y H:i') : 'N/A' }}</td>
                                <td>{{ $livraison->created_at ? $livraison->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                <td>
                                    @if($boutiqueId)
                                        <a href="{{ route('platform-admin.entreprises.boutique.livraison.show', [$entreprise->id, $boutiqueId, $livraison->id]) }}" class="btn btn-sm btn-primary">
                                            <i class="ti ti-eye me-1"></i> Détails
                                        </a>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Aucune livraison trouvée</td>
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

@include('platform-admin.layouts.footer')
