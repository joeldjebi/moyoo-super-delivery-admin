@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')


    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Abonnements /</span> Plans tarifaires
    </h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Liste des plans tarifaires</h5>
            <a href="{{ route('platform-admin.pricing-plans.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i>
                Nouveau plan
            </a>
        </div>
        <div class="card-body">
            <!-- Recherche et filtres -->
            <form method="GET" action="{{ route('platform-admin.pricing-plans.index') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Rechercher..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="is_active" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Actif</option>
                            <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactif</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="period" class="form-select">
                            <option value="">Toutes les périodes</option>
                            <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>Mensuel</option>
                            <option value="year" {{ request('period') == 'year' ? 'selected' : '' }}>Annuel</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <a href="{{ route('platform-admin.pricing-plans.index') }}" class="btn btn-secondary btn-sm">Réinitialiser</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Prix</th>
                            <th>Période</th>
                            <th>Modules attachés</th>
                            <th>Populaire</th>
                            <th>Statut</th>
                            <th>Créé le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($plans as $plan)
                            <tr>
                                <td>{{ $plan->id }}</td>
                                <td>
                                    <strong>{{ $plan->name ?? 'N/A' }}</strong>
                                    @if($plan->is_popular ?? false)
                                        <span class="badge bg-label-warning ms-1">Populaire</span>
                                    @endif
                                </td>
                                <td>{{ number_format($plan->price ?? 0, 0, ',', ' ') }} {{ $plan->currency ?? 'XOF' }}</td>
                                <td>
                                    @if(($plan->period ?? 'month') == 'month')
                                        <span class="badge bg-label-info">Mensuel</span>
                                    @else
                                        <span class="badge bg-label-primary">Annuel</span>
                                    @endif
                                </td>
                                <td>
                                    @if($plan->modules && $plan->modules->count() > 0)
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($plan->modules as $module)
                                                @if($module->pivot->is_enabled)
                                                    <span class="badge bg-label-success" title="{{ $module->name }}">
                                                        {{ $module->slug === 'stock_management' ? 'Stock' : $module->name }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-label-secondary" title="{{ $module->name }} (désactivé)">
                                                        {{ $module->slug === 'stock_management' ? 'Stock' : $module->name }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">Aucun module</span>
                                    @endif
                                </td>
                                <td>
                                    @if($plan->is_popular ?? false)
                                        <span class="badge bg-label-success">Oui</span>
                                    @else
                                        <span class="badge bg-label-secondary">Non</span>
                                    @endif
                                </td>
                                <td>
                                    @if($plan->is_active ?? true)
                                        <span class="badge bg-label-success">Actif</span>
                                    @else
                                        <span class="badge bg-label-danger">Inactif</span>
                                    @endif
                                </td>
                                <td>{{ isset($plan->created_at) ? \Carbon\Carbon::parse($plan->created_at)->format('d/m/Y') : 'N/A' }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('platform-admin.pricing-plans.show', $plan->id) }}">
                                                <i class="ti ti-eye me-1"></i> Voir
                                            </a>
                                            <a class="dropdown-item" href="{{ route('platform-admin.pricing-plans.edit', $plan->id) }}">
                                                <i class="ti ti-pencil me-1"></i> Modifier
                                            </a>
                                            <form action="{{ route('platform-admin.pricing-plans.destroy', $plan->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Êtes-vous sûr ?')">
                                                    <i class="ti ti-trash me-1"></i> Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Aucun plan tarifaire trouvé</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $plans->links() }}
            </div>
        </div>
    </div>


@include('platform-admin.layouts.footer')
