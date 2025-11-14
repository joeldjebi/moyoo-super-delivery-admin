@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">
            Gestion /
            <a href="{{ route('platform-admin.entreprises.index') }}" class="text-muted text-decoration-none">Entreprises</a> /
            <a href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}" class="text-muted text-decoration-none">{{ $entreprise->name }}</a> /
        </span> Historique de balance
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
                <h5 class="mb-0">Historique de balance</h5>
                <p class="text-muted mb-0">{{ $entreprise->name }}</p>
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
            <form method="GET" action="{{ route('platform-admin.entreprises.historique-balance', ['id' => $entreprise->id]) }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Rechercher..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="type_operation" class="form-select">
                            <option value="">Tous les types</option>
                            <option value="encaissement" {{ request('type_operation') == 'encaissement' ? 'selected' : '' }}>Encaissement</option>
                            <option value="reversement" {{ request('type_operation') == 'reversement' ? 'selected' : '' }}>Reversement</option>
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
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('platform-admin.entreprises.historique-balance', ['id' => $entreprise->id]) }}" class="btn btn-secondary w-100">Réinitialiser</a>
                    </div>
                </div>
                @if(request('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
            </form>

            @if($historiques->count() > 0)
                <div class="mb-3">
                    <small class="text-muted">
                        Affichage de {{ $historiques->firstItem() }} à {{ $historiques->lastItem() }} sur {{ $historiques->total() }} opérations
                    </small>
                </div>
            @endif

            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Marchand</th>
                            <th>Boutique</th>
                            <th>Montant</th>
                            <th>Balance avant</th>
                            <th>Balance après</th>
                            <th>Référence</th>
                            <th>Créé par</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($historiques as $historique)
                            <tr>
                                <td>{{ $historique->id }}</td>
                                <td>{{ \Carbon\Carbon::parse($historique->created_at)->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($historique->type_operation == 'encaissement')
                                        <span class="badge bg-label-success">Encaissement</span>
                                    @else
                                        <span class="badge bg-label-warning">Reversement</span>
                                    @endif
                                </td>
                                <td>
                                    @if($historique->balanceMarchand && $historique->balanceMarchand->marchand)
                                        <strong>
                                            {{ $historique->balanceMarchand->marchand->first_name }} {{ $historique->balanceMarchand->marchand->last_name }}
                                        </strong>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    {{ $historique->balanceMarchand && $historique->balanceMarchand->boutique ? $historique->balanceMarchand->boutique->libelle : 'N/A' }}
                                </td>
                                <td>
                                    <span class="fw-bold {{ $historique->type_operation == 'encaissement' ? 'text-success' : 'text-warning' }}">
                                        {{ $historique->type_operation == 'encaissement' ? '+' : '-' }}{{ number_format($historique->montant, 2, ',', ' ') }} FCFA
                                    </span>
                                </td>
                                <td>{{ number_format($historique->balance_avant, 2, ',', ' ') }} FCFA</td>
                                <td>
                                    <span class="text-primary fw-bold">{{ number_format($historique->balance_apres, 2, ',', ' ') }} FCFA</span>
                                </td>
                                <td>
                                    @if($historique->reference)
                                        <small class="text-muted">{{ $historique->reference }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($historique->createdBy)
                                        {{ $historique->createdBy->first_name ?? '' }} {{ $historique->createdBy->last_name ?? '' }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            @if($historique->description)
                                <tr>
                                    <td colspan="10" class="text-muted small">
                                        <i class="ti ti-info-circle me-1"></i> {{ $historique->description }}
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">Aucun historique trouvé pour cette entreprise</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($historiques->hasPages())
                <div class="mt-4">
                    {{ $historiques->links() }}
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

