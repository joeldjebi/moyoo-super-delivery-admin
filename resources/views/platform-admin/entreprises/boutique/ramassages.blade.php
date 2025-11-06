@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">
            Entreprises /
            <a href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}" class="text-muted text-decoration-none">{{ $entreprise->name }}</a> /
            <a href="{{ route('platform-admin.entreprises.boutiques', $entreprise->id) }}" class="text-muted text-decoration-none">Boutiques</a> /
            <a href="{{ route('platform-admin.entreprises.boutique.show', [$entreprise->id, $boutique->id]) }}" class="text-muted text-decoration-none">{{ $boutique->libelle }}</a> /
        </span> Ramassages
    </h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Liste des ramassages</h5>
            <a href="{{ route('platform-admin.entreprises.boutique.show', [$entreprise->id, $boutique->id]) }}" class="btn btn-secondary btn-sm">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
        </div>
        <div class="card-body">
            <!-- Filtres -->
            <form method="GET" action="{{ route('platform-admin.entreprises.boutique.ramassages', [$entreprise->id, $boutique->id]) }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Rechercher (code, contact, adresse)" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="statut" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="demande" {{ request('statut') == 'demande' ? 'selected' : '' }}>Demande</option>
                            <option value="planifie" {{ request('statut') == 'planifie' ? 'selected' : '' }}>Planifié</option>
                            <option value="en_cours" {{ request('statut') == 'en_cours' ? 'selected' : '' }}>En cours</option>
                            <option value="termine" {{ request('statut') == 'termine' ? 'selected' : '' }}>Terminé</option>
                            <option value="annule" {{ request('statut') == 'annule' ? 'selected' : '' }}>Annulé</option>
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
                            <a href="{{ route('platform-admin.entreprises.boutique.ramassages', [$entreprise->id, $boutique->id]) }}" class="btn btn-secondary">
                                <i class="ti ti-x me-1"></i> Réinitialiser
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            @if($ramassages->total() > 0)
                <div class="mb-3">
                    <small class="text-muted">
                        Affichage de {{ $ramassages->firstItem() }} à {{ $ramassages->lastItem() }} sur {{ $ramassages->total() }} ramassages
                    </small>
                </div>
            @endif

            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Contact</th>
                            <th>Date demande</th>
                            <th>Date planifiée</th>
                            <th>Date effectuée</th>
                            <th>Nombre colis</th>
                            <th>Statut</th>
                            <th>Date création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($ramassages as $ramassage)
                            <tr>
                                <td>{{ $ramassage->id }}</td>
                                <td><strong>{{ $ramassage->code_ramassage }}</strong></td>
                                <td>{{ $ramassage->contact_ramassage ?? 'N/A' }}</td>
                                <td>{{ $ramassage->date_demande ? $ramassage->date_demande->format('d/m/Y H:i') : 'N/A' }}</td>
                                <td>{{ $ramassage->date_planifiee ? $ramassage->date_planifiee->format('d/m/Y H:i') : 'N/A' }}</td>
                                <td>{{ $ramassage->date_effectuee ? \Carbon\Carbon::parse($ramassage->date_effectuee)->format('d/m/Y') : 'N/A' }}</td>
                                <td>
                                    <strong>{{ $ramassage->nombre_colis_reel ?? 0 }}</strong>
                                    @if($ramassage->nombre_colis_estime)
                                        <br><small class="text-muted">Estimé: {{ $ramassage->nombre_colis_estime }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($ramassage->statut == 'demande')
                                        <span class="badge bg-label-secondary">Demande</span>
                                    @elseif($ramassage->statut == 'planifie')
                                        <span class="badge bg-label-info">Planifié</span>
                                    @elseif($ramassage->statut == 'en_cours')
                                        <span class="badge bg-label-primary">En cours</span>
                                    @elseif($ramassage->statut == 'termine')
                                        <span class="badge bg-label-success">Terminé</span>
                                    @elseif($ramassage->statut == 'annule')
                                        <span class="badge bg-label-warning">Annulé</span>
                                    @else
                                        <span class="badge bg-label-secondary">Inconnu</span>
                                    @endif
                                </td>
                                <td>{{ $ramassage->created_at ? $ramassage->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('platform-admin.entreprises.boutique.ramassage.show', [$entreprise->id, $boutique->id, $ramassage->id]) }}" class="btn btn-sm btn-primary">
                                        <i class="ti ti-eye me-1"></i> Détails
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">Aucun ramassage trouvé</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($ramassages->hasPages())
                <div class="mt-4">
                    {{ $ramassages->links() }}
                </div>
            @endif
        </div>
    </div>

@include('platform-admin.layouts.footer')

