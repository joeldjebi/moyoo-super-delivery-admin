@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

            <h4 class="fw-bold py-3 mb-4">
                <span class="text-muted fw-light">Gestion /</span> Entreprises
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
                                    <span class="avatar-initial rounded bg-label-primary"><i class="ti ti-building ti-28px"></i></span>
                                </div>
                                <h4 class="mb-0">{{ $stats['total_entreprises'] }}</h4>
                            </div>
                            <p class="mb-1">Total Entreprises</p>
                            <p class="mb-0">
                                <span class="text-heading fw-medium me-2">{{ $stats['total_entreprises'] }}</span>
                                <small class="text-muted">entreprises</small>
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
                                <h4 class="mb-0">{{ $stats['entreprises_actives'] }}</h4>
                            </div>
                            <p class="mb-1">Entreprises Actives</p>
                            <p class="mb-0">
                                <span class="text-heading fw-medium me-2">{{ $stats['entreprises_actives'] }}</span>
                                <small class="text-muted">actives</small>
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
                                <h4 class="mb-0">{{ $stats['entreprises_inactives'] }}</h4>
                            </div>
                            <p class="mb-1">Entreprises Inactives</p>
                            <p class="mb-0">
                                <span class="text-heading fw-medium me-2">{{ $stats['entreprises_inactives'] }}</span>
                                <small class="text-muted">inactives</small>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="card card-border-shadow-info h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar me-4">
                                    <span class="avatar-initial rounded bg-label-info"><i class="ti ti-credit-card ti-28px"></i></span>
                                </div>
                                <h4 class="mb-0">{{ $stats['avec_abonnement'] }}</h4>
                            </div>
                            <p class="mb-1">Avec Abonnement</p>
                            <p class="mb-0">
                                <span class="text-heading fw-medium me-2">{{ $stats['avec_abonnement'] }}</span>
                                <small class="text-muted">abonnées</small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-3 col-sm-6">
                    <div class="card card-border-shadow-secondary h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar me-4">
                                    <span class="avatar-initial rounded bg-label-secondary"><i class="ti ti-credit-card-off ti-28px"></i></span>
                                </div>
                                <h4 class="mb-0">{{ $stats['sans_abonnement'] }}</h4>
                            </div>
                            <p class="mb-1">Sans Abonnement</p>
                            <p class="mb-0">
                                <span class="text-heading fw-medium me-2">{{ $stats['sans_abonnement'] }}</span>
                                <small class="text-muted">non abonnées</small>
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
                                <small class="text-muted">actifs</small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Basic Bootstrap Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Liste des entreprises</h5>
                        <p class="text-muted mb-0">Vous pouvez uniquement consulter et activer/désactiver les entreprises</p>
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
                    <!-- Recherche -->
                    <form method="GET" action="{{ route('platform-admin.entreprises.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="Rechercher..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="statut" class="form-select">
                                    <option value="">Tous les statuts</option>
                                    <option value="1" {{ request('statut') == '1' ? 'selected' : '' }}>Actif</option>
                                    <option value="0" {{ request('statut') == '0' ? 'selected' : '' }}>Inactif</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('platform-admin.entreprises.index') }}" class="btn btn-secondary w-100">Réinitialiser</a>
                            </div>
                        </div>
                        @if(request('per_page'))
                            <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                        @endif
                    </form>

                    @if($entreprises->count() > 0)
                        <div class="mb-3">
                            <small class="text-muted">
                                Affichage de {{ $entreprises->firstItem() }} à {{ $entreprises->lastItem() }} sur {{ $entreprises->total() }} entreprises
                            </small>
                        </div>
                    @endif

                    <div class="table-responsive text-nowrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Statut</th>
                                    <th>Abonnement</th>
                                    <th>Créé le</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @forelse($entreprises as $entreprise)
                                    <tr>
                                        <td>{{ $entreprise->id }}</td>
                                        <td>{{ $entreprise->name ?? 'N/A' }}</td>
                                        <td>{{ $entreprise->email ?? 'N/A' }}</td>
                                        <td>{{ $entreprise->mobile ?? 'N/A' }}</td>
                                        <td>
                                            @if(($entreprise->statut ?? 0) == 1)
                                                <span class="badge bg-label-success">Actif</span>
                                            @else
                                                <span class="badge bg-label-danger">Inactif</span>
                                            @endif
                                            <form action="{{ route('platform-admin.entreprises.toggle-status', $entreprise->id) }}" method="POST" class="d-inline ms-2">
                                                @csrf
                                                @if(($entreprise->statut ?? 0) == 1)
                                                    <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('Désactiver cette entreprise ?')" title="Désactiver">
                                                        <i class="ti ti-toggle-left"></i>
                                                    </button>
                                                @else
                                                    <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Activer cette entreprise ?')" title="Activer">
                                                        <i class="ti ti-toggle-right"></i>
                                                    </button>
                                                @endif
                                            </form>
                                        </td>
                                        <td>
                                            @if($entreprise->subscription_id)
                                                <div>
                                                    <span class="badge bg-label-primary">{{ $entreprise->subscription_name ?? $entreprise->pricing_plan_name ?? 'Abonnement actif' }}</span>
                                                    @if($entreprise->subscription_price)
                                                        <br><small class="text-muted">{{ number_format($entreprise->subscription_price, 0) }} {{ $entreprise->subscription_currency ?? 'FCFA' }}</small>
                                                    @endif
                                                    @if($entreprise->subscription_expires_at)
                                                        @php
                                                            $expiresAt = \Carbon\Carbon::parse($entreprise->subscription_expires_at);
                                                            $isExpired = $expiresAt->isPast();
                                                        @endphp
                                                        <br><small class="text-muted {{ $isExpired ? 'text-danger' : '' }}">
                                                            {{ $isExpired ? 'Expiré le' : 'Expire le' }}: {{ $expiresAt->format('d/m/Y') }}
                                                        </small>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="badge bg-label-secondary">Aucun abonnement</span>
                                            @endif
                                        </td>
                                        <td>{{ isset($entreprise->created_at) ? \Carbon\Carbon::parse($entreprise->created_at)->format('d/m/Y') : 'N/A' }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}">
                                                        <i class="ti ti-eye me-1"></i> Voir les détails
                                                    </a>
                                                    <a class="dropdown-item" href="{{ route('platform-admin.entreprises.users', $entreprise->id) }}">
                                                        <i class="ti ti-users me-1"></i> Utilisateurs de l'entreprise
                                                    </a>
                                                    <a class="dropdown-item" href="{{ route('platform-admin.entreprises.tarifs-livraison', $entreprise->id) }}">
                                                        <i class="ti ti-truck-delivery me-1"></i> Tarifs de livraison
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item" href="{{ route('platform-admin.entreprises.balances', $entreprise->id) }}">
                                                        <i class="ti ti-wallet me-1"></i> Balances des marchands
                                                    </a>
                                                    <a class="dropdown-item" href="{{ route('platform-admin.entreprises.historique-balance', $entreprise->id) }}">
                                                        <i class="ti ti-history me-1"></i> Historique de balance
                                                    </a>
                                                    <a class="dropdown-item" href="{{ route('platform-admin.entreprises.historique-reversement', $entreprise->id) }}">
                                                        <i class="ti ti-arrow-back-up me-1"></i> Historique de reversement
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <form action="{{ route('platform-admin.entreprises.toggle-status', $entreprise->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @if(($entreprise->statut ?? 0) == 1)
                                                            <button type="submit" class="dropdown-item text-warning" onclick="return confirm('Êtes-vous sûr de vouloir désactiver cette entreprise ?')">
                                                                <i class="ti ti-toggle-left me-1"></i> Désactiver
                                                            </button>
                                                        @else
                                                            <button type="submit" class="dropdown-item text-success" onclick="return confirm('Êtes-vous sûr de vouloir activer cette entreprise ?')">
                                                                <i class="ti ti-toggle-right me-1"></i> Activer
                                                            </button>
                                                        @endif
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Aucune entreprise trouvée</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $entreprises->links() }}
                    </div>
                </div>
            </div>
            <!--/ Basic Bootstrap Table -->

<script>
function updatePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('page', '1'); // Reset to first page
    window.location.href = url.toString();
}
</script>

@include('platform-admin.layouts.footer')

