@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">
            Entreprises /
            <a href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}" class="text-muted text-decoration-none">{{ $entreprise->name }}</a> /
        </span> Tarifs de livraison
    </h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="ti ti-truck-delivery me-2"></i>Tarifs de livraison
            </h5>
            <a href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}" class="btn btn-secondary btn-sm">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
        </div>
        <div class="card-body">
            <!-- Filtres -->
            <form method="GET" action="{{ route('platform-admin.entreprises.tarifs-livraison', $entreprise->id) }}" class="mb-4">
                <div class="row g-3 mb-3">
                    <!-- Recherche textuelle -->
                    <div class="col-md-12">
                        <input type="text" name="search" class="form-control" placeholder="Rechercher (commune départ, commune arrivée, type engin, mode livraison)" value="{{ request('search') }}">
                    </div>
                </div>

                <div class="row g-3">
                    <!-- Commune départ -->
                    <div class="col-md-2">
                        <label class="form-label small">Commune départ</label>
                        <select name="commune_depart_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Toutes</option>
                            @foreach($communes as $id => $libelle)
                                <option value="{{ $id }}" {{ (string)request('commune_depart_id') === (string)$id ? 'selected' : '' }}>{{ $libelle }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Commune arrivée -->
                    <div class="col-md-2">
                        <label class="form-label small">Commune arrivée</label>
                        <select name="commune_arrivee_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Toutes</option>
                            @foreach($communes as $id => $libelle)
                                <option value="{{ $id }}" {{ (string)request('commune_arrivee_id') === (string)$id ? 'selected' : '' }}>{{ $libelle }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Type engin -->
                    <div class="col-md-2">
                        <label class="form-label small">Type engin</label>
                        <select name="type_engin_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Tous</option>
                            @foreach($type_engins as $id => $libelle)
                                <option value="{{ $id }}" {{ (string)request('type_engin_id') === (string)$id ? 'selected' : '' }}>{{ $libelle }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Mode livraison -->
                    <div class="col-md-2">
                        <label class="form-label small">Mode livraison</label>
                        <select name="mode_livraison_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Tous</option>
                            @foreach($mode_livraisons as $id => $libelle)
                                <option value="{{ $id }}" {{ (string)request('mode_livraison_id') === (string)$id ? 'selected' : '' }}>{{ $libelle }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Poids -->
                    <div class="col-md-2">
                        <label class="form-label small">Poids</label>
                        <select name="poids_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Tous</option>
                            @foreach($poids as $id => $libelle)
                                <option value="{{ $id }}" {{ (string)request('poids_id') === (string)$id ? 'selected' : '' }}>{{ $libelle }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Temps -->
                    <div class="col-md-2">
                        <label class="form-label small">Temps</label>
                        <select name="temps_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Tous</option>
                            @foreach($temps as $id => $libelle)
                                <option value="{{ $id }}" {{ (string)request('temps_id') === (string)$id ? 'selected' : '' }}>{{ $libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-2">
                        <select name="per_page" class="form-select" onchange="this.form.submit()">
                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15 par page</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 par page</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 par page</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 par page</option>
                        </select>
                    </div>
                    <div class="col-md-10">
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-search me-1"></i> Filtrer
                            </button>
                            <a href="{{ route('platform-admin.entreprises.tarifs-livraison', $entreprise->id) }}" class="btn btn-secondary">
                                <i class="ti ti-x me-1"></i> Réinitialiser
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            @if($tarifs->total() > 0)
                <div class="mb-3">
                    <small class="text-muted">
                        Affichage de {{ $tarifs->firstItem() }} à {{ $tarifs->lastItem() }} sur {{ $tarifs->total() }} tarif(s)
                    </small>
                </div>
            @endif

            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Commune départ</th>
                            <th>Commune arrivée</th>
                            <th>Type engin</th>
                            <th>Mode livraison</th>
                            <th>Poids</th>
                            <th>Temps</th>
                            <th>Montant</th>
                            <th>Date création</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($tarifs as $tarif)
                            <tr>
                                <td><span class="badge bg-label-secondary">#{{ $tarif->id }}</span></td>
                                <td>
                                    <span class="fw-semibold">{{ $tarif->commune_depart_nom ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $tarif->commune_arrivee_nom ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-label-info">{{ $tarif->type_engin_nom ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-label-primary">{{ $tarif->mode_livraison_nom ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-label-warning">{{ $tarif->poids_nom ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-label-success">{{ $tarif->temps_nom ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <strong class="text-success">{{ number_format($tarif->amount, 2) }} FCFA</strong>
                                </td>
                                <td>
                                    {{ $tarif->created_at ? \Carbon\Carbon::parse($tarif->created_at)->format('d/m/Y H:i') : 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="ti ti-truck-off text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-3 mb-0">Aucun tarif de livraison trouvé pour cette entreprise.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tarifs->hasPages())
                <div class="mt-4">
                    {{ $tarifs->links() }}
                </div>
            @endif
        </div>
    </div>

@include('platform-admin.layouts.footer')

