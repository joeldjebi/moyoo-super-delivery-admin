@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')


    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Abonnements /</span> Historique des abonnements
    </h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Statistiques -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="card card-border-shadow-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-4">
                            <span class="avatar-initial rounded bg-label-primary"><i class="ti ti-list-check ti-28px"></i></span>
                        </div>
                        <h4 class="mb-0">{{ $stats['total_abonnements'] }}</h4>
                    </div>
                    <p class="mb-1">Total Abonnements</p>
                    <p class="mb-0">
                        <span class="text-heading fw-medium me-2">{{ $stats['total_abonnements'] }}</span>
                        <small class="text-muted">abonnements</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card card-border-shadow-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-4">
                            <span class="avatar-initial rounded bg-label-success"><i class="ti ti-check ti-28px"></i></span>
                        </div>
                        <h4 class="mb-0">{{ $stats['abonnements_actifs'] }}</h4>
                    </div>
                    <p class="mb-1">Abonnements Actifs</p>
                    <p class="mb-0">
                        <span class="text-heading fw-medium me-2">{{ $stats['abonnements_actifs'] }}</span>
                        <small class="text-muted">actifs</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card card-border-shadow-danger h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-4">
                            <span class="avatar-initial rounded bg-label-danger"><i class="ti ti-x ti-28px"></i></span>
                        </div>
                        <h4 class="mb-0">{{ $stats['abonnements_inactifs'] }}</h4>
                    </div>
                    <p class="mb-1">Abonnements Inactifs</p>
                    <p class="mb-0">
                        <span class="text-heading fw-medium me-2">{{ $stats['abonnements_inactifs'] }}</span>
                        <small class="text-muted">inactifs</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card card-border-shadow-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-4">
                            <span class="avatar-initial rounded bg-label-warning"><i class="ti ti-clock-exclamation ti-28px"></i></span>
                        </div>
                        <h4 class="mb-0">{{ $stats['abonnements_expires'] }}</h4>
                    </div>
                    <p class="mb-1">Abonnements Expirés</p>
                    <p class="mb-0">
                        <span class="text-heading fw-medium me-2">{{ $stats['abonnements_expires'] }}</span>
                        <small class="text-muted">expirés</small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="card card-border-shadow-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-4">
                            <span class="avatar-initial rounded bg-label-info"><i class="ti ti-clock-check ti-28px"></i></span>
                        </div>
                        <h4 class="mb-0">{{ $stats['abonnements_non_expires'] }}</h4>
                    </div>
                    <p class="mb-1">Abonnements Non Expirés</p>
                    <p class="mb-0">
                        <span class="text-heading fw-medium me-2">{{ $stats['abonnements_non_expires'] }}</span>
                        <small class="text-muted">valides</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card card-border-shadow-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-4">
                            <span class="avatar-initial rounded bg-label-success"><i class="ti ti-currency-dollar ti-28px"></i></span>
                        </div>
                        <h4 class="mb-0">{{ number_format($stats['revenus_totaux'], 0, ',', ' ') }}</h4>
                    </div>
                    <p class="mb-1">Revenus Totaux</p>
                    <p class="mb-0">
                        <span class="text-heading fw-medium me-2">{{ number_format($stats['revenus_totaux'], 0, ',', ' ') }}</span>
                        <small class="text-muted">FCFA</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card card-border-shadow-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-4">
                            <span class="avatar-initial rounded bg-label-primary"><i class="ti ti-building ti-28px"></i></span>
                        </div>
                        <h4 class="mb-0">{{ $stats['entreprises_abonnees'] }}</h4>
                    </div>
                    <p class="mb-1">Entreprises Abonnées</p>
                    <p class="mb-0">
                        <span class="text-heading fw-medium me-2">{{ $stats['entreprises_abonnees'] }}</span>
                        <small class="text-muted">entreprises</small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Historique des abonnements</h5>
                <p class="text-muted mb-0">Consultation uniquement de l'historique des abonnements</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('platform-admin.subscriptions.upgrade-history') }}" class="btn btn-info btn-sm">
                    <i class="ti ti-history me-1"></i> Historique des upgrades
                </a>
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
            <form method="GET" action="{{ route('platform-admin.subscriptions.index') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Rechercher..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
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
                        <select name="is_active" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Actif</option>
                            <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactif</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="expired" class="form-select">
                            <option value="">Tous</option>
                            <option value="no" {{ request('expired') == 'no' ? 'selected' : '' }}>Non expiré</option>
                            <option value="yes" {{ request('expired') == 'yes' ? 'selected' : '' }}>Expiré</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <a href="{{ route('platform-admin.subscriptions.index') }}" class="btn btn-secondary btn-sm">Réinitialiser</a>
                    </div>
                </div>
                @if(request('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
            </form>

            @if($subscriptions->count() > 0)
                <div class="mb-3">
                    <small class="text-muted">
                        Affichage de {{ $subscriptions->firstItem() }} à {{ $subscriptions->lastItem() }} sur {{ $subscriptions->total() }} abonnements
                    </small>
                </div>
            @endif

            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Entreprise</th>
                            <th>Plan tarifaire</th>
                            <th>Prix</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Durée</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($subscriptions as $subscription)
                            <tr>
                                <td>{{ $subscription->id }}</td>
                                <td><strong>{{ $subscription->name ?? 'N/A' }}</strong></td>
                                <td>
                                    @if($subscription->entreprise)
                                        <a href="{{ route('platform-admin.entreprises.show', $subscription->entreprise_id) }}" class="text-primary">
                                            {{ $subscription->entreprise->name }}
                                        </a>
                                        @if($subscription->entreprise->statut != 1)
                                            <span class="badge bg-label-warning ms-1">Entreprise inactive</span>
                                        @endif
                                    @else
                                        <span class="text-muted">Aucune</span>
                                    @endif
                                </td>
                                <td>
                                    @if($subscription->pricingPlan)
                                        {{ $subscription->pricingPlan->name }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ number_format($subscription->price ?? 0, 0, ',', ' ') }} {{ $subscription->currency ?? 'XOF' }}</td>
                                <td>{{ $subscription->started_at ? $subscription->started_at->format('d/m/Y') : 'N/A' }}</td>
                                <td>
                                    @if($subscription->expires_at)
                                        @if($subscription->expires_at->isPast())
                                            <span class="text-danger">
                                                {{ $subscription->expires_at->format('d/m/Y') }}
                                            </span>
                                        @else
                                            {{ $subscription->expires_at->format('d/m/Y') }}
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $subscription->duration_days ?? 'N/A' }} jours</td>
                                <td>
                                    @if($subscription->is_active)
                                        <span class="badge bg-label-success">Actif</span>
                                    @else
                                        <span class="badge bg-label-danger">Inactif</span>
                                    @endif
                                    @if($subscription->expires_at && $subscription->expires_at->isPast())
                                        <span class="badge bg-label-warning ms-1">Expiré</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('platform-admin.subscriptions.show', $subscription->id) }}">
                                                <i class="ti ti-eye me-1"></i> Voir les détails
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">Aucun abonnement trouvé</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($subscriptions->hasPages())
                <div class="mt-4">
                    {{ $subscriptions->links() }}
                </div>
            @endif
        </div>
    </div>

<script>
function updatePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('page', '1'); // Reset to first page
    window.location.href = url.toString();
}
</script>

@include('platform-admin.layouts.footer')
