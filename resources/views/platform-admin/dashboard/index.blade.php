@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

    <div class="row g-6">
        <div class="col-lg-3 col-sm-6">
        <div class="card card-border-shadow-primary h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-4">
                    <span class="avatar-initial rounded bg-label-primary"><i class="ti ti-truck ti-28px"></i></span>
                    </div>
                    <h4 class="mb-0">{{ $stats['total_entreprises'] }}</h4>
                </div>
                <p class="mb-1">Entreprises</p>

                <p class="mb-0">
                    <span class="text-heading fw-medium me-2">{{ $stats['total_entreprises'] }}</span>
                    <small class="text-muted">{{ $stats['entreprises_actives'] }} actives</small>
                </p>
            </div>
        </div>
        </div>
        <div class="col-lg-3 col-sm-6">
        <div class="card card-border-shadow-warning h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-4">
                    <span class="avatar-initial rounded bg-label-warning"><i class="ti ti-alert-triangle ti-28px"></i></span>
                    </div>
                    <h4 class="mb-0">{{ $stats['total_users'] }}</h4>
                </div>
                <p class="mb-1">Utilisateurs</p>
                <p class="mb-0">
                    <span class="text-heading fw-medium me-2">{{ $stats['total_users'] }}</span>
                    <small class="text-muted">Total</small>
                </p>
            </div>
        </div>
        </div>
        <div class="col-lg-3 col-sm-6">
        <div class="card card-border-shadow-danger h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-4">
                    <span class="avatar-initial rounded bg-label-danger"><i class="ti ti-git-fork ti-28px"></i></span>
                    </div>
                    <h4 class="mb-0">{{ $stats['total_colis'] }}</h4>
                </div>
                <p class="mb-1">Colis</p>
                <p class="mb-0">
                    <span class="text-heading fw-medium me-2">{{ $stats['total_colis'] }}</span>
                    <small class="text-muted">Total</small>
                </p>
            </div>
        </div>
        </div>
        <div class="col-lg-3 col-sm-6">
        <div class="card card-border-shadow-info h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-4">
                    <span class="avatar-initial rounded bg-label-info"><i class="ti ti-currency-dollar ti-28px"></i></span>
                    </div>
                    <h4 class="mb-0">{{ number_format($stats['total_revenus'] ?? 0, 0, ',', ' ') }}</h4>
                </div>
                <p class="mb-1">Revenus</p>
                <p class="mb-0">
                    <span class="text-heading fw-medium me-2">{{ number_format($stats['revenus_totaux'] ?? 0, 0, ',', ' ') }} FCFA</span>
                    <small class="text-muted">Total</small>
                </p>
            </div>
        </div>
        </div>
    </div>

    <!-- Statistiques des abonnements -->
    <div class="row g-6 mt-0">
        <div class="col-lg-3 col-sm-6">
            <div class="card card-border-shadow-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-4">
                            <span class="avatar-initial rounded bg-label-success"><i class="ti ti-crown ti-28px"></i></span>
                        </div>
                        <h4 class="mb-0">{{ $stats['total_abonnements'] ?? 0 }}</h4>
                    </div>
                    <p class="mb-1">Abonnements</p>
                    <p class="mb-0">
                        <span class="text-heading fw-medium me-2">{{ $stats['total_abonnements'] ?? 0 }}</span>
                        <small class="text-muted">{{ $stats['abonnements_actifs'] ?? 0 }} actifs</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card card-border-shadow-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-4">
                            <span class="avatar-initial rounded bg-label-warning"><i class="ti ti-x ti-28px"></i></span>
                        </div>
                        <h4 class="mb-0">{{ $stats['abonnements_expires'] ?? 0 }}</h4>
                    </div>
                    <p class="mb-1">Abonnements Expirés</p>
                    <p class="mb-0">
                        <span class="text-heading fw-medium me-2">{{ $stats['abonnements_expires'] ?? 0 }}</span>
                        <small class="text-muted">Expirés</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card card-border-shadow-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-4">
                            <span class="avatar-initial rounded bg-label-info"><i class="ti ti-building ti-28px"></i></span>
                        </div>
                        <h4 class="mb-0">{{ $stats['entreprises_abonnees'] ?? 0 }}</h4>
                    </div>
                    <p class="mb-1">Entreprises Abonnées</p>
                    <p class="mb-0">
                        <span class="text-heading fw-medium me-2">{{ $stats['entreprises_abonnees'] ?? 0 }}</span>
                        <small class="text-muted">Avec abonnement actif</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card card-border-shadow-secondary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-4">
                            <span class="avatar-initial rounded bg-label-secondary"><i class="ti ti-currency-dollar ti-28px"></i></span>
                        </div>
                        <h4 class="mb-0">{{ number_format(($stats['revenus_totaux'] ?? 0) / 1000, 0, ',', ' ') }}k</h4>
                    </div>
                    <p class="mb-1">Montant Total</p>
                    <p class="mb-0">
                        <span class="text-heading fw-medium me-2">{{ number_format($stats['revenus_totaux'] ?? 0, 0, ',', ' ') }} FCFA</span>
                        <small class="text-muted">Abonnements actifs</small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Listes des entreprises et abonnements -->
    <div class="row g-4 mt-4">
        <!-- Liste des entreprises -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Dernières entreprises</h5>
                    <a href="{{ route('platform-admin.entreprises.index') }}" class="btn btn-sm btn-outline-primary">
                        Voir tout <i class="ti ti-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body">
                    @if($entreprises && $entreprises->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;">ID</th>
                                        <th>Nom</th>
                                        <th>Email</th>
                                        <th>Statut</th>
                                        <th style="width: 130px;">Date création</th>
                                        <th style="width: 100px;" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($entreprises as $entreprise)
                                        <tr>
                                            <td><strong>#{{ $entreprise->id }}</strong></td>
                                            <td>
                                                <strong>{{ $entreprise->name }}</strong>
                                            </td>
                                            <td>{{ $entreprise->email ?? 'N/A' }}</td>
                                            <td>
                                                @if($entreprise->statut == 1)
                                                    <span class="badge bg-label-success">Actif</span>
                                                @else
                                                    <span class="badge bg-label-danger">Inactif</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($entreprise->created_at)
                                                    <small>{{ \Carbon\Carbon::parse($entreprise->created_at)->format('d/m/Y') }}</small>
                                                    <br><small class="text-muted">{{ \Carbon\Carbon::parse($entreprise->created_at)->format('H:i') }}</small>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}" class="btn btn-sm btn-info" title="Voir les détails">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <span class="text-muted">Aucune entreprise trouvée</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Liste des abonnements -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Derniers abonnements</h5>
                    <a href="{{ route('platform-admin.subscriptions.index') }}" class="btn btn-sm btn-outline-primary">
                        Voir tout <i class="ti ti-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body">
                    @if($abonnements && $abonnements->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;">ID</th>
                                        <th>Entreprise</th>
                                        <th>Plan</th>
                                        <th>Prix</th>
                                        <th>Statut</th>
                                        <th style="width: 130px;">Date création</th>
                                        <th style="width: 100px;" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($abonnements as $abonnement)
                                        <tr>
                                            <td><strong>#{{ $abonnement->id }}</strong></td>
                                            <td>
                                                <strong>{{ $abonnement->entreprise_name ?? 'N/A' }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-label-secondary">{{ $abonnement->pricing_plan_name ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                {{ number_format($abonnement->price ?? 0, 0, ',', ' ') }} {{ $abonnement->currency ?? 'FCFA' }}
                                            </td>
                                            <td>
                                                @if($abonnement->is_active)
                                                    <span class="badge bg-label-success">Actif</span>
                                                @else
                                                    <span class="badge bg-label-secondary">Inactif</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($abonnement->created_at)
                                                    <small>{{ \Carbon\Carbon::parse($abonnement->created_at)->format('d/m/Y') }}</small>
                                                    <br><small class="text-muted">{{ \Carbon\Carbon::parse($abonnement->created_at)->format('H:i') }}</small>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('platform-admin.subscriptions.show', $abonnement->id) }}" class="btn btn-sm btn-info" title="Voir les détails">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <span class="text-muted">Aucun abonnement trouvé</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>





@include('platform-admin.layouts.footer')

