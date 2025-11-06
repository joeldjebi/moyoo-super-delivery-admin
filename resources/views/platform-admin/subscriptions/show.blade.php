@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">
            Gestion /
            <a href="{{ route('platform-admin.subscriptions.index') }}" class="text-muted text-decoration-none">Abonnements</a> /
        </span> Détails de l'abonnement
    </h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(!$subscription)
        <div class="alert alert-danger">
            Abonnement non trouvé.
        </div>
    @else
        <!-- Informations principales -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Informations de l'abonnement</h5>
                <div>
                    <a href="{{ route('platform-admin.subscriptions.index') }}" class="btn btn-secondary btn-sm">
                        <i class="ti ti-arrow-left me-1"></i> Retour à la liste
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="200">ID:</th>
                                <td><strong>#{{ $subscription->id }}</strong></td>
                            </tr>
                            <tr>
                                <th>Nom:</th>
                                <td><strong>{{ $subscription->name ?? 'N/A' }}</strong></td>
                            </tr>
                            <tr>
                                <th>Slug:</th>
                                <td><code>{{ $subscription->slug ?? 'N/A' }}</code></td>
                            </tr>
                            <tr>
                                <th>Description:</th>
                                <td>{{ $subscription->description ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Prix:</th>
                                <td>
                                    <strong>{{ number_format($subscription->price ?? 0, 0, ',', ' ') }} {{ $subscription->currency ?? 'FCFA' }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <th>Durée:</th>
                                <td>{{ $subscription->duration_days ?? 'N/A' }} jours</td>
                            </tr>
                            <tr>
                                <th>Statut:</th>
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
                            </tr>
                            <tr>
                                <th>Date début:</th>
                                <td>{{ $subscription->started_at ? $subscription->started_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Date expiration:</th>
                                <td>
                                    @if($subscription->expires_at)
                                        @if($subscription->expires_at->isPast())
                                            <span class="text-danger">
                                                {{ $subscription->expires_at->format('d/m/Y H:i:s') }}
                                            </span>
                                        @else
                                            {{ $subscription->expires_at->format('d/m/Y H:i:s') }}
                                        @endif
                                    @else
                                        <span class="text-muted">Aucune expiration</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Créé le:</th>
                                <td>{{ $subscription->created_at ? $subscription->created_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="200">Entreprise:</th>
                                <td>
                                    @if($subscription->entreprise)
                                        <a href="{{ route('platform-admin.entreprises.show', $subscription->entreprise_id) }}" class="text-decoration-none">
                                            <strong>{{ $subscription->entreprise->name }}</strong>
                                        </a>
                                        @if($subscription->entreprise->statut != 1)
                                            <br><span class="badge bg-label-warning">Entreprise inactive</span>
                                        @endif
                                        @if($subscription->entreprise->email)
                                            <br><small class="text-muted">Email: {{ $subscription->entreprise->email }}</small>
                                        @endif
                                        @if($subscription->entreprise->mobile)
                                            <br><small class="text-muted">Tel: {{ $subscription->entreprise->mobile }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">Aucune entreprise</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Plan tarifaire:</th>
                                <td>
                                    @if($subscription->pricingPlan)
                                        <strong>{{ $subscription->pricingPlan->name }}</strong>
                                        @if($subscription->pricingPlan->description)
                                            <br><small class="text-muted">{{ $subscription->pricingPlan->description }}</small>
                                        @endif
                                        @if($subscription->pricingPlan->price)
                                            <br><small class="text-muted">Prix: {{ number_format($subscription->pricingPlan->price, 0, ',', ' ') }} {{ $subscription->pricingPlan->currency ?? 'FCFA' }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Ordre de tri:</th>
                                <td>{{ $subscription->sort_order ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Modifié le:</th>
                                <td>{{ $subscription->updated_at ? $subscription->updated_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Limites et fonctionnalités -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Limites et fonctionnalités</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="200">Max colis/mois:</th>
                                <td>{{ $subscription->max_colis_per_month ?? 'Illimité' }}</td>
                            </tr>
                            <tr>
                                <th>Max livreurs:</th>
                                <td>{{ $subscription->max_livreurs ?? 'Illimité' }}</td>
                            </tr>
                            <tr>
                                <th>Max marchands:</th>
                                <td>{{ $subscription->max_marchands ?? 'Illimité' }}</td>
                            </tr>
                            <tr>
                                <th>Limite SMS WhatsApp:</th>
                                <td>{{ $subscription->whatsapp_sms_limit ?? 'Illimité' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="200">Notifications WhatsApp:</th>
                                <td>
                                    @if($subscription->whatsapp_notifications)
                                        <span class="badge bg-label-success">Activé</span>
                                    @else
                                        <span class="badge bg-label-secondary">Désactivé</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Notifications Firebase:</th>
                                <td>
                                    @if($subscription->firebase_notifications)
                                        <span class="badge bg-label-success">Activé</span>
                                    @else
                                        <span class="badge bg-label-secondary">Désactivé</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Accès API:</th>
                                <td>
                                    @if($subscription->api_access)
                                        <span class="badge bg-label-success">Activé</span>
                                    @else
                                        <span class="badge bg-label-secondary">Désactivé</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Rapports avancés:</th>
                                <td>
                                    @if($subscription->advanced_reports)
                                        <span class="badge bg-label-success">Activé</span>
                                    @else
                                        <span class="badge bg-label-secondary">Désactivé</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Support prioritaire:</th>
                                <td>
                                    @if($subscription->priority_support)
                                        <span class="badge bg-label-success">Activé</span>
                                    @else
                                        <span class="badge bg-label-secondary">Désactivé</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fonctionnalités (JSON) -->
        @if($subscription->features && is_array($subscription->features) && count($subscription->features) > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Fonctionnalités</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @foreach($subscription->features as $feature)
                            <li class="mb-2">
                                <i class="ti ti-check text-success me-2"></i>{{ $feature }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Informations financières -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informations financières</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-label-primary">
                            <div class="card-body text-center">
                                <h6 class="mb-1">Prix de l'abonnement</h6>
                                <h4 class="mb-0">{{ number_format($subscription->price ?? 0, 0, ',', ' ') }} {{ $subscription->currency ?? 'FCFA' }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-label-info">
                            <div class="card-body text-center">
                                <h6 class="mb-1">Durée</h6>
                                <h4 class="mb-0">{{ $subscription->duration_days ?? 'N/A' }} jours</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-label-success">
                            <div class="card-body text-center">
                                <h6 class="mb-1">Prix journalier</h6>
                                <h4 class="mb-0">
                                    @if($subscription->duration_days && $subscription->duration_days > 0)
                                        {{ number_format(($subscription->price ?? 0) / $subscription->duration_days, 0, ',', ' ') }} {{ $subscription->currency ?? 'FCFA' }}
                                    @else
                                        N/A
                                    @endif
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

@include('platform-admin.layouts.footer')

