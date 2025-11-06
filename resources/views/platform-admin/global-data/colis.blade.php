@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Gestion /</span> Tous les colis
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
                <h5 class="mb-0">Tous les colis</h5>
                <p class="text-muted mb-0">Liste de tous les colis (toutes entreprises confondues)</p>
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
            <form method="GET" action="{{ route('platform-admin.global-data.colis') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Rechercher (code, client, téléphone, boutique, entreprise)" value="{{ request('search') }}">
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
                            <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>En attente</option>
                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>En cours</option>
                            <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Terminé</option>
                            <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>Annulé (client)</option>
                            <option value="4" {{ request('status') == '4' ? 'selected' : '' }}>Annulé (livreur)</option>
                            <option value="5" {{ request('status') == '5' ? 'selected' : '' }}>Annulé (marchand)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-search me-1"></i> Rechercher
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('platform-admin.global-data.colis') }}" class="btn btn-secondary w-100">
                            <i class="ti ti-x me-1"></i> Réinitialiser
                        </a>
                    </div>
                </div>
                @if(request('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
            </form>

            @if($colis->total() > 0)
                <div class="mb-3">
                    <small class="text-muted">
                        Affichage de {{ $colis->firstItem() }} à {{ $colis->lastItem() }} sur {{ $colis->total() }} colis
                    </small>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Entreprise</th>
                            <th>Code</th>
                            <th>Client</th>
                            <th>Téléphone</th>
                            <th>Boutique</th>
                            <th style="width: 120px;">Montant</th>
                            <th>Statut</th>
                            <th style="width: 130px;">Date création</th>
                            <th style="width: 100px;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($colis as $coli)
                            @php
                                $boutiqueId = $coli->packageColis && $coli->packageColis->boutique_id
                                    ? $coli->packageColis->boutique_id
                                    : null;
                            @endphp
                            <tr>
                                <td><strong>#{{ $coli->id }}</strong></td>
                                <td>
                                    @if($coli->entreprise)
                                        <a href="{{ route('platform-admin.entreprises.show', $coli->entreprise_id) }}" class="text-primary text-decoration-none">
                                            <strong>{{ $coli->entreprise->name }}</strong>
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td><strong>{{ $coli->code }}</strong></td>
                                <td>{{ $coli->nom_client ?? 'N/A' }}</td>
                                <td>{{ $coli->telephone_client ?? 'N/A' }}</td>
                                <td>
                                    @if($coli->packageColis && $coli->packageColis->boutique)
                                        {{ $coli->packageColis->boutique->libelle }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $coli->prix_de_vente ? number_format($coli->prix_de_vente, 0, ',', ' ') . ' FCFA' : 'N/A' }}</td>
                                <td>
                                    @if($coli->status == 0)
                                        <span class="badge bg-label-secondary">En attente</span>
                                    @elseif($coli->status == 1)
                                        <span class="badge bg-label-primary">En cours</span>
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
                                <td>
                                    <small>{{ $coli->created_at ? $coli->created_at->format('d/m/Y') : 'N/A' }}</small>
                                    <br><small class="text-muted">{{ $coli->created_at ? $coli->created_at->format('H:i') : '' }}</small>
                                </td>
                                <td class="text-center">
                                    @if($boutiqueId && $coli->entreprise_id)
                                        <a href="{{ route('platform-admin.entreprises.boutique.colis.show', [$coli->entreprise_id, $boutiqueId, $coli->id]) }}" class="btn btn-sm btn-info" title="Voir les détails">
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
                                    <span class="text-muted">Aucun colis trouvé</span>
                                </td>
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

