@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">
            Gestion /
            <a href="{{ route('platform-admin.entreprises.index') }}" class="text-muted text-decoration-none">Entreprises</a> /
            <a href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}" class="text-muted text-decoration-none">{{ $entreprise->name }}</a> /
        </span> Balances des marchands
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
                <h5 class="mb-0">Balances des marchands</h5>
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
            <form method="GET" action="{{ route('platform-admin.entreprises.balances', ['id' => $entreprise->id]) }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Rechercher..." value="{{ request('search') }}">
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
                        <select name="boutique_id" class="form-select">
                            <option value="">Toutes les boutiques</option>
                            @foreach($boutiques as $boutique)
                                <option value="{{ $boutique->id }}" {{ request('boutique_id') == $boutique->id ? 'selected' : '' }}>
                                    {{ $boutique->libelle }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('platform-admin.entreprises.balances', ['id' => $entreprise->id]) }}" class="btn btn-secondary w-100">Réinitialiser</a>
                    </div>
                </div>
                @if(request('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
            </form>

            @if($balances->count() > 0)
                <div class="mb-3">
                    <small class="text-muted">
                        Affichage de {{ $balances->firstItem() }} à {{ $balances->lastItem() }} sur {{ $balances->total() }} balances
                    </small>
                </div>
            @endif

            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Marchand</th>
                            <th>Boutique</th>
                            <th>Montant encaissé</th>
                            <th>Montant reversé</th>
                            <th>Balance actuelle</th>
                            <th>Dernière mise à jour</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($balances as $balance)
                            <tr>
                                <td>{{ $balance->id }}</td>
                                <td>
                                    <strong>
                                        {{ $balance->marchand->first_name ?? 'N/A' }} {{ $balance->marchand->last_name ?? '' }}
                                    </strong>
                                    @if($balance->marchand)
                                        <br><small class="text-muted">{{ $balance->marchand->mobile ?? '' }}</small>
                                    @endif
                                </td>
                                <td>{{ $balance->boutique->libelle ?? 'N/A' }}</td>
                                <td>
                                    <span class="text-success fw-bold">{{ number_format($balance->montant_encaisse, 2, ',', ' ') }} FCFA</span>
                                </td>
                                <td>
                                    <span class="text-warning fw-bold">{{ number_format($balance->montant_reverse, 2, ',', ' ') }} FCFA</span>
                                </td>
                                <td>
                                    <span class="text-primary fw-bold fs-5">{{ number_format($balance->balance_actuelle, 2, ',', ' ') }} FCFA</span>
                                </td>
                                <td>
                                    {{ $balance->derniere_mise_a_jour ? \Carbon\Carbon::parse($balance->derniere_mise_a_jour)->format('d/m/Y H:i') : 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Aucune balance trouvée pour cette entreprise</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($balances->hasPages())
                <div class="mt-4">
                    {{ $balances->links() }}
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

