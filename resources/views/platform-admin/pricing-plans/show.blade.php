@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Abonnements /</span> <a href="{{ route('platform-admin.pricing-plans.index') }}" class="text-decoration-none">Plans tarifaires</a> / Détails
        </h4>
        <div>
            <a href="{{ route('platform-admin.pricing-plans.index') }}" class="btn btn-label-secondary">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
            <a href="{{ route('platform-admin.pricing-plans.edit', $plan->id) }}" class="btn btn-primary">
                <i class="ti ti-edit me-1"></i> Modifier
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Informations du plan -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-info-circle me-2"></i>Informations du plan tarifaire
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Nom:</dt>
                                <dd class="col-sm-8"><strong>{{ $plan->name }}</strong></dd>

                                <dt class="col-sm-4">Description:</dt>
                                <dd class="col-sm-8">{{ $plan->description ?? 'N/A' }}</dd>

                                <dt class="col-sm-4">Prix:</dt>
                                <dd class="col-sm-8">{{ number_format($plan->price, 0, ',', ' ') }} {{ $plan->currency }}</dd>

                                <dt class="col-sm-4">Période:</dt>
                                <dd class="col-sm-8">
                                    @if($plan->period == 'month')
                                        <span class="badge bg-label-info">Mensuel</span>
                                    @else
                                        <span class="badge bg-label-primary">Annuel</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Statut:</dt>
                                <dd class="col-sm-8">
                                    @if($plan->is_active)
                                        <span class="badge bg-label-success">Actif</span>
                                    @else
                                        <span class="badge bg-label-danger">Inactif</span>
                                    @endif
                                </dd>

                                <dt class="col-sm-4">Populaire:</dt>
                                <dd class="col-sm-8">
                                    @if($plan->is_popular)
                                        <span class="badge bg-label-warning">Oui</span>
                                    @else
                                        <span class="badge bg-label-secondary">Non</span>
                                    @endif
                                </dd>

                                <dt class="col-sm-4">Modules attachés:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-label-primary">{{ $plan->modules->where('pivot.is_enabled', true)->count() }} activé(s)</span>
                                </dd>

                                <dt class="col-sm-4">Créé le:</dt>
                                <dd class="col-sm-8">{{ $plan->created_at->format('d/m/Y H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modules disponibles -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-package me-2"></i>Modules disponibles
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Description</th>
                                    <th>Prix</th>
                                    <th>Statut</th>
                                    <th>Limites</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($modules as $moduleData)
                                    @php
                                        $module = $moduleData['module'];
                                        $attached = $moduleData['attached'];
                                        $isEnabled = $moduleData['is_enabled'];
                                        $limits = $moduleData['limits'];
                                        $isStockManagement = $module->slug === 'stock_management';
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($module->icon)
                                                    <i class="{{ $module->icon }} me-2"></i>
                                                @else
                                                    <i class="ti ti-package me-2"></i>
                                                @endif
                                                <strong>{{ $module->name }}</strong>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $module->description ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            @if($module->is_optional)
                                                <div>
                                                    <strong class="text-primary">{{ $module->getFormattedPrice() }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="ti ti-info-circle"></i> Optionnel
                                                    </small>
                                                </div>
                                            @else
                                                <span class="badge bg-label-info">Inclus</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($attached)
                                                @if($isEnabled)
                                                    <span class="badge bg-label-success">Activé</span>
                                                @else
                                                    <span class="badge bg-label-secondary">Désactivé</span>
                                                @endif
                                            @else
                                                <span class="badge bg-label-warning">Non attaché</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($attached && $limits)
                                                @if($isStockManagement)
                                                    <div class="small">
                                                        @if(isset($limits['max_products']))
                                                            <div>Max produits: <strong>{{ $limits['max_products'] ?? 'Illimité' }}</strong></div>
                                                        @endif
                                                        @if(isset($limits['max_categories']))
                                                            <div>Max catégories: <strong>{{ $limits['max_categories'] ?? 'Illimité' }}</strong></div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <small class="text-muted">Limites configurées</small>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                @if($attached)
                                                    <form action="{{ route('platform-admin.pricing-plans.modules.toggle', [$plan->id, $module->id]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-{{ $isEnabled ? 'warning' : 'success' }}" title="{{ $isEnabled ? 'Désactiver' : 'Activer' }}">
                                                            <i class="ti ti-{{ $isEnabled ? 'toggle-left' : 'toggle-right' }}"></i>
                                                            {{ $isEnabled ? 'Désactiver' : 'Activer' }}
                                                        </button>
                                                    </form>

                                                    @if($isStockManagement)
                                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#configureModal{{ $module->id }}">
                                                            <i class="ti ti-settings"></i> Configurer
                                                        </button>
                                                    @endif

                                                    <form action="{{ route('platform-admin.pricing-plans.modules.detach', [$plan->id, $module->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir détacher ce module ?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Détacher">
                                                            <i class="ti ti-unlink"></i> Détacher
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('platform-admin.pricing-plans.modules.attach', [$plan->id, $module->id]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-primary" title="Attacher">
                                                            <i class="ti ti-link"></i> Attacher
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Modal de configuration pour le module Stock -->
                                    @if($isStockManagement && $attached)
                                        <div class="modal fade" id="configureModal{{ $module->id }}" tabindex="-1" aria-labelledby="configureModalLabel{{ $module->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('platform-admin.pricing-plans.modules.configure', [$plan->id, $module->id]) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="configureModalLabel{{ $module->id }}">
                                                                <i class="ti ti-settings me-2"></i>Configurer les limites - {{ $module->name }}
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="max_products{{ $module->id }}" class="form-label">Nombre maximum de produits</label>
                                                                <input type="number" class="form-control" id="max_products{{ $module->id }}" name="max_products"
                                                                       value="{{ $limits['max_products'] ?? '' }}" min="0" placeholder="Illimité si vide">
                                                                <small class="text-muted">Laissez vide pour illimité</small>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="max_categories{{ $module->id }}" class="form-label">Nombre maximum de catégories</label>
                                                                <input type="number" class="form-control" id="max_categories{{ $module->id }}" name="max_categories"
                                                                       value="{{ $limits['max_categories'] ?? '' }}" min="0" placeholder="Illimité si vide">
                                                                <small class="text-muted">Laissez vide pour illimité</small>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="ti ti-check me-1"></i> Enregistrer
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('platform-admin.layouts.footer')

