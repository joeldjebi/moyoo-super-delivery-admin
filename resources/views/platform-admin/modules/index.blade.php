@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Gestion /</span> Modules
    </h4>

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

    <!-- Liste des modules avec leurs prix -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">
                    <i class="ti ti-package me-2"></i>Liste des modules
                </h5>
                <p class="text-muted mb-0">
                    Gérez les paramètres des modules.
                    <strong>Seul le module "Gestion de Stock" peut être optionnel</strong> et avoir un prix.
                </p>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Module</th>
                            <th>Description</th>
                            <th>Catégorie</th>
                            <th>Prix</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($modules as $module)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($module->icon)
                                            <i class="ti {{ $module->icon }} me-2 text-primary"></i>
                                        @endif
                                        <strong>{{ $module->name }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $module->description ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-label-{{ $module->category === 'premium' ? 'warning' : ($module->category === 'advanced' ? 'info' : 'success') }}">
                                        {{ ucfirst($module->category) }}
                                    </span>
                                </td>
                                <td>
                                    @if($module->slug === 'stock_management' && $module->is_optional)
                                        <strong class="text-primary">{{ $module->getFormattedPrice() }}</strong>
                                    @elseif($module->slug === 'stock_management')
                                        <span class="badge bg-label-warning">Prix non configuré</span>
                                    @else
                                        <span class="badge bg-label-info">Inclus dans le plan</span>
                                    @endif
                                </td>
                                <td>
                                    @if($module->slug === 'stock_management')
                                        <span class="badge bg-label-warning">
                                            <i class="ti ti-shopping-cart me-1"></i>Optionnel
                                        </span>
                                    @else
                                        <span class="badge bg-label-success">
                                            <i class="ti ti-check me-1"></i>Inclus
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($module->is_active)
                                        <span class="badge bg-label-success">Actif</span>
                                    @else
                                        <span class="badge bg-label-danger">Inactif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('platform-admin.modules.edit', $module->id) }}" class="btn btn-sm btn-primary">
                                        <i class="ti ti-edit"></i> Modifier
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="ti ti-package me-2"></i>Gestion des modules par plan tarifaire
            </h5>
            <p class="text-muted mb-0">Activez ou désactivez les modules pour chaque plan tarifaire et définissez des limites</p>
        </div>
        <div class="card-body">
            @foreach($pricingPlans as $plan)
                <div class="card mb-4">
                    <div class="card-header bg-label-primary">
                        <h5 class="mb-0">
                            <i class="ti ti-currency-dollar me-2"></i>{{ $plan->name }}
                            @if($plan->is_popular)
                                <span class="badge bg-label-warning ms-2">Populaire</span>
                            @endif
                        </h5>
                        <p class="text-muted mb-0">{{ $plan->description }}</p>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('platform-admin.modules.update-for-plan', $plan->id) }}" method="POST" id="form-{{ $plan->id }}">
                            @csrf

                            {{-- Champ caché pour garantir que tous les modules sont traités --}}
                            @foreach($modules as $module)
                                <input type="hidden" name="all_modules[]" value="{{ $module->id }}">
                            @endforeach

                            @php
                                $modulesByCategory = $modules->groupBy('category');
                            @endphp

                            <div class="accordion" id="modulesAccordion-{{ $plan->id }}">
                                @foreach($modulesByCategory as $category => $categoryModules)
                                    @php
                                        $categoryId = 'category-' . $plan->id . '-' . $category;
                                        $isFirst = $loop->first;
                                        $categoryName = $category === 'premium' ? 'Premium' : ($category === 'advanced' ? 'Avancé' : 'Core');
                                        $categoryBadge = $category === 'premium' ? 'bg-label-warning' : ($category === 'advanced' ? 'bg-label-info' : 'bg-label-success');
                                    @endphp
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading-{{ $categoryId }}">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $categoryId }}" aria-expanded="{{ $isFirst ? 'true' : 'false' }}" aria-controls="{{ $categoryId }}">
                                                <div class="d-flex align-items-center w-100">
                                                    <span class="badge {{ $categoryBadge }} me-2">{{ $categoryName }}</span>
                                                    <span class="fw-semibold me-2">{{ $categoryName }} Modules</span>
                                                    <span class="badge bg-label-secondary">{{ $categoryModules->count() }} module(s)</span>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="{{ $categoryId }}" class="accordion-collapse collapse " aria-labelledby="heading-{{ $categoryId }}" data-bs-parent="#modulesAccordion-{{ $plan->id }}">
                                            <div class="accordion-body">
                                                @foreach($categoryModules as $module)
                                                    @php
                                                        $pivot = $plan->modules->firstWhere('id', $module->id);
                                                        $isEnabled = $pivot ? $pivot->pivot->is_enabled : false;
                                                        $limits = $pivot && $pivot->pivot->limits ? json_decode($pivot->pivot->limits, true) : null;
                                                    @endphp
                                                    <div class="card mb-3 border">
                                                        <div class="card-body">
                                                            <div class="row align-items-center">
                                                                <div class="col-md-1">
                                                                    <div class="form-check">
                                                                        <input type="checkbox"
                                                                               class="form-check-input module-checkbox"
                                                                               name="modules[{{ $module->id }}][module_id]"
                                                                               value="{{ $module->id }}"
                                                                               id="module-{{ $plan->id }}-{{ $module->id }}"
                                                                               data-plan-id="{{ $plan->id }}"
                                                                               {{ $isEnabled ? 'checked' : '' }}
                                                                               onchange="updateModuleStatus({{ $plan->id }}, {{ $module->id }}, this.checked)">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="d-flex align-items-center">
                                                                        @if($module->icon)
                                                                            <i class="ti {{ $module->icon }} me-2 text-primary fs-5"></i>
                                                                        @endif
                                                                        <div>
                                                                            <strong>{{ $module->name }}</strong>
                                                                            @if($module->slug === 'stock_management')
                                                                                <br><small class="badge bg-label-warning">
                                                                                    <i class="ti ti-shopping-cart me-1"></i>Module optionnel
                                                                                </small>
                                                                            @else
                                                                                <br><small class="badge bg-label-success">
                                                                                    <i class="ti ti-check me-1"></i>Module inclus
                                                                                </small>
                                                                            @endif
                                                                            @if($module->description)
                                                                                <br><small class="text-muted">{{ $module->description }}</small>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <span class="badge {{ $isEnabled ? 'bg-label-success' : 'bg-label-secondary' }}">
                                                                        {{ $isEnabled ? 'Activé' : 'Désactivé' }}
                                                                    </span>
                                                                </div>
                                                                <div class="col-md-5">
                                                                    <div class="limits-container" id="limits-{{ $plan->id }}-{{ $module->id }}" style="display: {{ $isEnabled ? 'block' : 'none' }};">
                                                                        @if($module->slug === 'stock_management')
                                                                            {{-- Configuration spéciale pour le module Stock --}}
                                                                            <div class="alert alert-info mb-2 py-2">
                                                                                <small>
                                                                                    <i class="ti ti-info-circle me-1"></i>
                                                                                    <strong>Module optionnel</strong> - Prix: {{ $module->is_optional && $module->price ? number_format($module->price, 0, ',', ' ') . ' ' . $module->currency : 'Non configuré' }}
                                                                                </small>
                                                                            </div>
                                                                            <label class="form-label small">Limites du module Stock</label>
                                                                            <div class="row g-2">
                                                                                <div class="col-6">
                                                                                    <input type="number"
                                                                                           class="form-control form-control-sm"
                                                                                           name="modules[{{ $module->id }}][limits][max_products]"
                                                                                           value="{{ $limits['max_products'] ?? '' }}"
                                                                                           placeholder="Max produits">
                                                                                </div>
                                                                                <div class="col-6">
                                                                                    <input type="number"
                                                                                           class="form-control form-control-sm"
                                                                                           name="modules[{{ $module->id }}][limits][max_categories]"
                                                                                           value="{{ $limits['max_categories'] ?? '' }}"
                                                                                           placeholder="Max catégories">
                                                                                </div>
                                                                            </div>
                                                                            <small class="text-muted">Laisser vide pour illimité</small>
                                                                        @elseif(in_array($module->slug, ['colis_management', 'livreur_management', 'marchand_management', 'user_management']))
                                                                            @if($module->slug === 'colis_management')
                                                                                <label class="form-label small">Limite mensuelle de colis</label>
                                                                                <input type="number"
                                                                                       class="form-control form-control-sm"
                                                                                       name="modules[{{ $module->id }}][limits][max_per_month]"
                                                                                       value="{{ $limits['max_per_month'] ?? '' }}"
                                                                                       placeholder="Max/mois">
                                                                                <small class="text-muted">Laisser vide pour illimité</small>
                                                                            @elseif($module->slug === 'livreur_management')
                                                                                <label class="form-label small">Limite de livreurs</label>
                                                                                <input type="number"
                                                                                       class="form-control form-control-sm"
                                                                                       name="modules[{{ $module->id }}][limits][max_livreurs]"
                                                                                       value="{{ $limits['max_livreurs'] ?? '' }}"
                                                                                       placeholder="Max livreurs">
                                                                                <small class="text-muted">Laisser vide pour illimité</small>
                                                                            @elseif($module->slug === 'marchand_management')
                                                                                <label class="form-label small">Limites</label>
                                                                                <div class="row g-2">
                                                                                    <div class="col-6">
                                                                                        <input type="number"
                                                                                               class="form-control form-control-sm"
                                                                                               name="modules[{{ $module->id }}][limits][max_marchands]"
                                                                                               value="{{ $limits['max_marchands'] ?? '' }}"
                                                                                               placeholder="Max marchands">
                                                                                    </div>
                                                                                    <div class="col-6">
                                                                                        <input type="number"
                                                                                               class="form-control form-control-sm"
                                                                                               name="modules[{{ $module->id }}][limits][max_boutiques]"
                                                                                               value="{{ $limits['max_boutiques'] ?? '' }}"
                                                                                               placeholder="Max boutiques">
                                                                                    </div>
                                                                                </div>
                                                                                <small class="text-muted">Laisser vide pour illimité</small>
                                                                            @elseif($module->slug === 'user_management')
                                                                                <label class="form-label small">Limite d'utilisateurs</label>
                                                                                <input type="number"
                                                                                       class="form-control form-control-sm"
                                                                                       name="modules[{{ $module->id }}][limits][max_users]"
                                                                                       value="{{ $limits['max_users'] ?? '' }}"
                                                                                       placeholder="Max utilisateurs">
                                                                                <small class="text-muted">Laisser vide pour illimité</small>
                                                                            @endif
                                                                        @else
                                                                            <span class="text-muted">Aucune limite configurable</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4 d-flex justify-content-between align-items-center">
                                <div>
                                    <button type="button" class="btn btn-sm btn-label-secondary" onclick="toggleAllModules({{ $plan->id }}, true)">
                                        <i class="ti ti-check me-1"></i> Tout activer
                                    </button>
                                    <button type="button" class="btn btn-sm btn-label-secondary" onclick="toggleAllModules({{ $plan->id }}, false)">
                                        <i class="ti ti-x me-1"></i> Tout désactiver
                                    </button>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-1"></i> Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

<script>
function toggleAllModules(planId, enable) {
    const checkboxes = document.querySelectorAll(`input[data-plan-id="${planId}"].module-checkbox`);
    checkboxes.forEach(cb => {
        cb.checked = enable;
        const moduleId = cb.value;
        updateModuleStatus(planId, moduleId, enable);
    });
}

function updateModuleStatus(planId, moduleId, isEnabled) {
    const limitsContainer = document.getElementById('limits-' + planId + '-' + moduleId);
    if (limitsContainer) {
        limitsContainer.style.display = isEnabled ? 'block' : 'none';
    }

    // Mettre à jour le statut dans la carte
    const checkbox = document.getElementById('module-' + planId + '-' + moduleId);
    if (checkbox) {
        const card = checkbox.closest('.card');
        if (card) {
            const statusBadge = card.querySelector('.badge');
            if (statusBadge) {
                statusBadge.className = isEnabled ? 'badge bg-label-success' : 'badge bg-label-secondary';
                statusBadge.textContent = isEnabled ? 'Activé' : 'Désactivé';
            }
        }
    }
}
</script>

@include('platform-admin.layouts.footer')

