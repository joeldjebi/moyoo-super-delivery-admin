@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Gestion /</span> <a href="{{ route('platform-admin.modules.index') }}" class="text-decoration-none">Modules</a> / Modifier
        </h4>
        <div>
            <a href="{{ route('platform-admin.modules.index') }}" class="btn btn-label-secondary">
                <i class="ti ti-arrow-left me-1"></i> Retour
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

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-edit me-2"></i>Modifier le module: {{ $module->name }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('platform-admin.modules.update', $module->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nom du module <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $module->name) }}" required>
                                @error('name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="slug" class="form-label">Slug</label>
                                <input type="text" class="form-control" id="slug" value="{{ $module->slug }}" disabled>
                                <small class="text-muted">Le slug ne peut pas être modifié</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $module->description) }}</textarea>
                            @error('description')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="icon" class="form-label">Icône</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="ti {{ old('icon', $module->icon) ?: 'ti-package' }}" id="icon-preview"></i>
                                    </span>
                                    <select class="form-select" id="icon" name="icon" onchange="updateIconPreview(this.value)">
                                        <option value="">-- Sélectionner une icône --</option>
                                        <optgroup label="Général">
                                            <option value="ti-package" {{ old('icon', $module->icon) === 'ti-package' ? 'selected' : '' }}>📦 Package</option>
                                            <option value="ti-smart-home" {{ old('icon', $module->icon) === 'ti-smart-home' ? 'selected' : '' }}>🏠 Smart Home</option>
                                            <option value="ti-building" {{ old('icon', $module->icon) === 'ti-building' ? 'selected' : '' }}>🏢 Building</option>
                                            <option value="ti-building-store" {{ old('icon', $module->icon) === 'ti-building-store' ? 'selected' : '' }}>🏪 Building Store</option>
                                            <option value="ti-users" {{ old('icon', $module->icon) === 'ti-users' ? 'selected' : '' }}>👥 Users</option>
                                            <option value="ti-user" {{ old('icon', $module->icon) === 'ti-user' ? 'selected' : '' }}>👤 User</option>
                                            <option value="ti-user-shield" {{ old('icon', $module->icon) === 'ti-user-shield' ? 'selected' : '' }}>🛡️ User Shield</option>
                                            <option value="ti-user-circle" {{ old('icon', $module->icon) === 'ti-user-circle' ? 'selected' : '' }}>⭕ User Circle</option>
                                        </optgroup>
                                        <optgroup label="Commerce & Livraison">
                                            <option value="ti-shopping-cart" {{ old('icon', $module->icon) === 'ti-shopping-cart' ? 'selected' : '' }}>🛒 Shopping Cart</option>
                                            <option value="ti-truck" {{ old('icon', $module->icon) === 'ti-truck' ? 'selected' : '' }}>🚚 Truck</option>
                                            <option value="ti-truck-delivery" {{ old('icon', $module->icon) === 'ti-truck-delivery' ? 'selected' : '' }}>🚛 Truck Delivery</option>
                                            <option value="ti-box" {{ old('icon', $module->icon) === 'ti-box' ? 'selected' : '' }}>📦 Box</option>
                                            <option value="ti-package-export" {{ old('icon', $module->icon) === 'ti-package-export' ? 'selected' : '' }}>📤 Package Export</option>
                                            <option value="ti-package-import" {{ old('icon', $module->icon) === 'ti-package-import' ? 'selected' : '' }}>📥 Package Import</option>
                                        </optgroup>
                                        <optgroup label="Finances">
                                            <option value="ti-currency-dollar" {{ old('icon', $module->icon) === 'ti-currency-dollar' ? 'selected' : '' }}>💵 Currency Dollar</option>
                                            <option value="ti-wallet" {{ old('icon', $module->icon) === 'ti-wallet' ? 'selected' : '' }}>💳 Wallet</option>
                                            <option value="ti-credit-card" {{ old('icon', $module->icon) === 'ti-credit-card' ? 'selected' : '' }}>💳 Credit Card</option>
                                            <option value="ti-cash" {{ old('icon', $module->icon) === 'ti-cash' ? 'selected' : '' }}>💰 Cash</option>
                                        </optgroup>
                                        <optgroup label="Statistiques & Rapports">
                                            <option value="ti-chart-bar" {{ old('icon', $module->icon) === 'ti-chart-bar' ? 'selected' : '' }}>📊 Chart Bar</option>
                                            <option value="ti-chart-line" {{ old('icon', $module->icon) === 'ti-chart-line' ? 'selected' : '' }}>📈 Chart Line</option>
                                            <option value="ti-chart-pie" {{ old('icon', $module->icon) === 'ti-chart-pie' ? 'selected' : '' }}>🥧 Chart Pie</option>
                                            <option value="ti-chart-area" {{ old('icon', $module->icon) === 'ti-chart-area' ? 'selected' : '' }}>📉 Chart Area</option>
                                        </optgroup>
                                        <optgroup label="Sécurité & Administration">
                                            <option value="ti-shield-check" {{ old('icon', $module->icon) === 'ti-shield-check' ? 'selected' : '' }}>✅ Shield Check</option>
                                            <option value="ti-shield" {{ old('icon', $module->icon) === 'ti-shield' ? 'selected' : '' }}>🛡️ Shield</option>
                                            <option value="ti-lock" {{ old('icon', $module->icon) === 'ti-lock' ? 'selected' : '' }}>🔒 Lock</option>
                                            <option value="ti-key" {{ old('icon', $module->icon) === 'ti-key' ? 'selected' : '' }}>🔑 Key</option>
                                        </optgroup>
                                        <optgroup label="Stock & Inventaire">
                                            <option value="ti-warehouse" {{ old('icon', $module->icon) === 'ti-warehouse' ? 'selected' : '' }}>🏭 Warehouse</option>
                                            <option value="ti-stack" {{ old('icon', $module->icon) === 'ti-stack' ? 'selected' : '' }}>📚 Stack</option>
                                            <option value="ti-layers" {{ old('icon', $module->icon) === 'ti-layers' ? 'selected' : '' }}>📋 Layers</option>
                                            <option value="ti-database" {{ old('icon', $module->icon) === 'ti-database' ? 'selected' : '' }}>💾 Database</option>
                                        </optgroup>
                                        <optgroup label="Communication">
                                            <option value="ti-mail" {{ old('icon', $module->icon) === 'ti-mail' ? 'selected' : '' }}>✉️ Mail</option>
                                            <option value="ti-bell" {{ old('icon', $module->icon) === 'ti-bell' ? 'selected' : '' }}>🔔 Bell</option>
                                            <option value="ti-message" {{ old('icon', $module->icon) === 'ti-message' ? 'selected' : '' }}>💬 Message</option>
                                            <option value="ti-phone" {{ old('icon', $module->icon) === 'ti-phone' ? 'selected' : '' }}>📞 Phone</option>
                                        </optgroup>
                                        <optgroup label="Autres">
                                            <option value="ti-settings" {{ old('icon', $module->icon) === 'ti-settings' ? 'selected' : '' }}>⚙️ Settings</option>
                                            <option value="ti-crown" {{ old('icon', $module->icon) === 'ti-crown' ? 'selected' : '' }}>👑 Crown</option>
                                            <option value="ti-history" {{ old('icon', $module->icon) === 'ti-history' ? 'selected' : '' }}>🕐 History</option>
                                            <option value="ti-file-text" {{ old('icon', $module->icon) === 'ti-file-text' ? 'selected' : '' }}>📄 File Text</option>
                                            <option value="ti-layout-sidebar" {{ old('icon', $module->icon) === 'ti-layout-sidebar' ? 'selected' : '' }}>📑 Layout Sidebar</option>
                                        </optgroup>
                                    </select>
                                </div>
                                <small class="text-muted">Sélectionnez une icône dans la liste</small>
                                @error('icon')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="category" class="form-label">Catégorie <span class="text-danger">*</span></label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="core" {{ old('category', $module->category) === 'core' ? 'selected' : '' }}>Core</option>
                                    <option value="advanced" {{ old('category', $module->category) === 'advanced' ? 'selected' : '' }}>Advanced</option>
                                    <option value="premium" {{ old('category', $module->category) === 'premium' ? 'selected' : '' }}>Premium</option>
                                </select>
                                @error('category')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="sort_order" class="form-label">Ordre de tri</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{ old('sort_order', $module->sort_order) }}" min="0">
                                @error('sort_order')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3">
                            <i class="ti ti-currency-dollar me-2"></i>Configuration du prix
                        </h5>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch">
                                    @if($module->slug === 'stock_management')
                                        <input class="form-check-input" type="checkbox" id="is_optional" name="is_optional" value="1"
                                               {{ old('is_optional', $module->is_optional) ? 'checked' : '' }} onchange="togglePriceFields()">
                                        <label class="form-check-label" for="is_optional">
                                            Module optionnel (avec prix)
                                        </label>
                                        <small class="text-muted d-block">Ce module peut être ajouté en option lors de l'abonnement</small>
                                    @else
                                        <input class="form-check-input" type="checkbox" id="is_optional" name="is_optional" value="1"
                                               disabled>
                                        <label class="form-check-label text-muted" for="is_optional">
                                            Module optionnel (avec prix)
                                        </label>
                                        <small class="text-danger d-block">
                                            <i class="ti ti-alert-circle"></i> Seul le module "Gestion de Stock" peut être optionnel
                                        </small>
                                        <input type="hidden" name="is_optional" value="0">
                                    @endif
                                </div>
                            </div>

                            @if($module->slug === 'stock_management')
                                <div class="col-md-4 mb-3" id="price-field" style="display: {{ old('is_optional', $module->is_optional) ? 'block' : 'none' }};">
                                    <label for="price" class="form-label">Prix</label>
                                    <input type="number" class="form-control" id="price" name="price"
                                           value="{{ old('price', $module->price) }}" min="0" step="0.01" placeholder="0.00">
                                    <small class="text-muted">Laissez vide pour gratuit</small>
                                    @error('price')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3" id="currency-field" style="display: {{ old('is_optional', $module->is_optional) ? 'block' : 'none' }};">
                                    <label for="currency" class="form-label">Devise</label>
                                    <select class="form-select" id="currency" name="currency">
                                        <option value="">-- Sélectionner --</option>
                                        <option value="XOF" {{ old('currency', $module->currency ?? 'XOF') === 'XOF' ? 'selected' : '' }}>XOF (Franc CFA)</option>
                                        <option value="EUR" {{ old('currency', $module->currency ?? 'XOF') === 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                                        <option value="USD" {{ old('currency', $module->currency ?? 'XOF') === 'USD' ? 'selected' : '' }}>USD (Dollar)</option>
                                    </select>
                                    @error('currency')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            @else
                                <div class="col-md-8 mb-3">
                                    <div class="alert alert-info">
                                        <i class="ti ti-info-circle me-2"></i>
                                        Les champs de prix ne sont disponibles que pour le module "Gestion de Stock".
                                    </div>
                                </div>
                            @endif
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', $module->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Module actif
                                </label>
                            </div>
                            <small class="text-muted">Si désactivé, le module ne sera pas disponible</small>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('platform-admin.modules.index') }}" class="btn btn-label-secondary">
                                <i class="ti ti-x me-1"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePriceFields() {
    const isOptionalCheckbox = document.getElementById('is_optional');
    if (!isOptionalCheckbox) return;

    const isOptional = isOptionalCheckbox.checked;
    const priceField = document.getElementById('price-field');
    const currencyField = document.getElementById('currency-field');
    const currencySelect = document.getElementById('currency');

    if (isOptional) {
        if (priceField) priceField.style.display = 'block';
        if (currencyField) currencyField.style.display = 'block';
        if (currencySelect) currencySelect.removeAttribute('required');
    } else {
        if (priceField) priceField.style.display = 'none';
        if (currencyField) currencyField.style.display = 'none';
        if (currencySelect) {
            currencySelect.removeAttribute('required');
            currencySelect.value = '';
        }
        const priceInput = document.getElementById('price');
        if (priceInput) priceInput.value = '';
    }
}

function updateIconPreview(iconValue) {
    const preview = document.getElementById('icon-preview');
    if (preview) {
        // Retirer toutes les classes ti-*
        preview.className = preview.className.replace(/ti-\S+/g, '');
        // Ajouter la nouvelle classe
        if (iconValue) {
            preview.classList.add('ti', iconValue);
        } else {
            preview.classList.add('ti', 'ti-package');
        }
    }
}

// Initialiser l'aperçu au chargement
document.addEventListener('DOMContentLoaded', function() {
    const iconSelect = document.getElementById('icon');
    if (iconSelect) {
        updateIconPreview(iconSelect.value);
    }
});
</script>

@include('platform-admin.layouts.footer')

