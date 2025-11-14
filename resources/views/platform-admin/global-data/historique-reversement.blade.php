@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Données globales /</span> Tout l'historique de reversement
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
                <h5 class="mb-0">Tout l'historique de reversement</h5>
                <p class="text-muted mb-0">Liste de tout l'historique (toutes entreprises confondues)</p>
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
            <form method="GET" action="{{ route('platform-admin.global-data.historique-reversement') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="search" placeholder="Rechercher..." value="{{ request('search') }}">
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
                            <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                            <option value="valide" {{ request('statut') == 'valide' ? 'selected' : '' }}>Validé</option>
                            <option value="annule" {{ request('statut') == 'annule' ? 'selected' : '' }}>Annulé</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="mode_reversement" class="form-select">
                            <option value="">Tous les modes</option>
                            <option value="especes" {{ request('mode_reversement') == 'especes' ? 'selected' : '' }}>Espèces</option>
                            <option value="virement" {{ request('mode_reversement') == 'virement' ? 'selected' : '' }}>Virement</option>
                            <option value="mobile_money" {{ request('mode_reversement') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                            <option value="cheque" {{ request('mode_reversement') == 'cheque' ? 'selected' : '' }}>Chèque</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="marchand_id" class="form-select">
                            <option value="">Tous les marchands</option>
                            @foreach($marchands as $marchand)
                                <option value="{{ $marchand->id }}" {{ request('marchand_id') == $marchand->id ? 'selected' : '' }}>
                                    {{ $marchand->first_name }} {{ $marchand->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('platform-admin.global-data.historique-reversement') }}" class="btn btn-secondary w-100">Réinitialiser</a>
                    </div>
                </div>
                @if(request('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
            </form>

            @if($reversements->count() > 0)
                <div class="mb-3">
                    <small class="text-muted">
                        Affichage de {{ $reversements->firstItem() }} à {{ $reversements->lastItem() }} sur {{ $reversements->total() }} reversements
                    </small>
                </div>
            @endif

            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Entreprise</th>
                            <th>Marchand</th>
                            <th>Boutique</th>
                            <th>Montant</th>
                            <th>Mode</th>
                            <th>Référence</th>
                            <th>Statut</th>
                            <th>Date reversement</th>
                            <th>Créé par</th>
                            <th>Validé par</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($reversements as $reversement)
                            <tr>
                                <td>{{ $reversement->id }}</td>
                                <td>{{ \Carbon\Carbon::parse($reversement->created_at)->format('d/m/Y H:i') }}</td>
                                <td>
                                    <strong>{{ $reversement->entreprise->name ?? 'N/A' }}</strong>
                                </td>
                                <td>
                                    @if($reversement->marchand)
                                        <strong>
                                            {{ $reversement->marchand->first_name }} {{ $reversement->marchand->last_name }}
                                        </strong>
                                        <br><small class="text-muted">{{ $reversement->marchand->mobile ?? '' }}</small>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $reversement->boutique->libelle ?? 'N/A' }}</td>
                                <td>
                                    <span class="text-warning fw-bold fs-5">{{ number_format($reversement->montant_reverse, 2, ',', ' ') }} FCFA</span>
                                </td>
                                <td>
                                    @if($reversement->mode_reversement == 'especes')
                                        <span class="badge bg-label-primary">Espèces</span>
                                    @elseif($reversement->mode_reversement == 'virement')
                                        <span class="badge bg-label-info">Virement</span>
                                    @elseif($reversement->mode_reversement == 'mobile_money')
                                        <span class="badge bg-label-success">Mobile Money</span>
                                    @elseif($reversement->mode_reversement == 'cheque')
                                        <span class="badge bg-label-secondary">Chèque</span>
                                    @else
                                        <span class="badge bg-label-secondary">{{ $reversement->mode_reversement }}</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $reversement->reference_reversement }}</small>
                                </td>
                                <td>
                                    @if($reversement->statut == 'valide')
                                        <span class="badge bg-label-success">Validé</span>
                                    @elseif($reversement->statut == 'en_attente')
                                        <span class="badge bg-label-warning">En attente</span>
                                    @elseif($reversement->statut == 'annule')
                                        <span class="badge bg-label-danger">Annulé</span>
                                    @else
                                        <span class="badge bg-label-secondary">{{ $reversement->statut }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($reversement->date_reversement)
                                        {{ \Carbon\Carbon::parse($reversement->date_reversement)->format('d/m/Y H:i') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($reversement->createdBy)
                                        <small>{{ $reversement->createdBy->first_name ?? '' }} {{ $reversement->createdBy->last_name ?? '' }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($reversement->validatedBy)
                                        <small>{{ $reversement->validatedBy->first_name ?? '' }} {{ $reversement->validatedBy->last_name ?? '' }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @if($reversement->notes)
                                <tr>
                                    <td colspan="12" class="text-muted small">
                                        <i class="ti ti-note me-1"></i> <strong>Notes:</strong> {{ $reversement->notes }}
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="12" class="text-center">Aucun reversement trouvé</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($reversements->hasPages())
                <div class="mt-4">
                    {{ $reversements->links() }}
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

