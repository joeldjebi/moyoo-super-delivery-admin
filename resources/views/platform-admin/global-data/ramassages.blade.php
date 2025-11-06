@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Gestion /</span> Tous les ramassages
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
                <h5 class="mb-0">Tous les ramassages</h5>
                <p class="text-muted mb-0">Liste de tous les ramassages (toutes entreprises confondues)</p>
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
            <form method="GET" action="{{ route('platform-admin.global-data.ramassages') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Rechercher (code, contact, adresse, boutique, marchand, entreprise)" value="{{ request('search') }}">
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
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-search me-1"></i> Rechercher
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('platform-admin.global-data.ramassages') }}" class="btn btn-secondary w-100">
                            <i class="ti ti-x me-1"></i> Réinitialiser
                        </a>
                    </div>
                </div>
                @if(request('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
            </form>

            @if($ramassages->total() > 0)
                <div class="mb-3">
                    <small class="text-muted">
                        Affichage de {{ $ramassages->firstItem() }} à {{ $ramassages->lastItem() }} sur {{ $ramassages->total() }} ramassages
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
                            <th>Boutique</th>
                            <th>Marchand</th>
                            <th>Contact</th>
                            <th style="width: 130px;">Date demande</th>
                            <th style="width: 130px;">Date planifiée</th>
                            <th style="width: 130px;">Date effectuée</th>
                            <th style="width: 100px;">Nombre colis</th>
                            <th>Statut</th>
                            <th style="width: 130px;">Date création</th>
                            <th style="width: 100px;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($ramassages as $ramassage)
                            <tr>
                                <td><strong>#{{ $ramassage->id }}</strong></td>
                                <td>
                                    @if($ramassage->entreprise)
                                        <a href="{{ route('platform-admin.entreprises.show', $ramassage->entreprise_id) }}" class="text-primary text-decoration-none">
                                            <strong>{{ $ramassage->entreprise->name }}</strong>
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td><strong>{{ $ramassage->code_ramassage }}</strong></td>
                                <td>
                                    @if($ramassage->boutique)
                                        {{ $ramassage->boutique->libelle }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ramassage->marchand)
                                        {{ $ramassage->marchand->first_name ?? '' }} {{ $ramassage->marchand->last_name ?? '' }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $ramassage->contact_ramassage ?? 'N/A' }}</td>
                                <td>
                                    <small>{{ $ramassage->date_demande ? $ramassage->date_demande->format('d/m/Y') : 'N/A' }}</small>
                                    <br><small class="text-muted">{{ $ramassage->date_demande ? $ramassage->date_demande->format('H:i') : '' }}</small>
                                </td>
                                <td>
                                    <small>{{ $ramassage->date_planifiee ? $ramassage->date_planifiee->format('d/m/Y') : 'N/A' }}</small>
                                    <br><small class="text-muted">{{ $ramassage->date_planifiee ? $ramassage->date_planifiee->format('H:i') : '' }}</small>
                                </td>
                                <td>
                                    <small>{{ $ramassage->date_effectuee ? \Carbon\Carbon::parse($ramassage->date_effectuee)->format('d/m/Y') : 'N/A' }}</small>
                                </td>
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
                                <td>
                                    <small>{{ $ramassage->created_at ? $ramassage->created_at->format('d/m/Y') : 'N/A' }}</small>
                                    <br><small class="text-muted">{{ $ramassage->created_at ? $ramassage->created_at->format('H:i') : '' }}</small>
                                </td>
                                <td class="text-center">
                                    @if($ramassage->boutique_id && $ramassage->entreprise_id)
                                        <a href="{{ route('platform-admin.entreprises.boutique.ramassage.show', [$ramassage->entreprise_id, $ramassage->boutique_id, $ramassage->id]) }}" class="btn btn-sm btn-info" title="Voir les détails">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    @elseif($ramassage->entreprise_id)
                                        <a href="{{ route('platform-admin.entreprises.ramassage.show', [$ramassage->entreprise_id, $ramassage->id]) }}" class="btn btn-sm btn-info" title="Voir les détails">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-center py-4">
                                    <span class="text-muted">Aucun ramassage trouvé</span>
                                </td>
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

<script>
function updatePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
}
</script>

@include('platform-admin.layouts.footer')

