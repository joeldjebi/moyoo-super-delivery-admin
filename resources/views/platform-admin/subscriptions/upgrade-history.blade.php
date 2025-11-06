@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">
            Gestion /
            <a href="{{ route('platform-admin.subscriptions.index') }}" class="text-muted text-decoration-none">Abonnements</a> /
        </span> Historique des upgrades
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
                <h5 class="mb-0">Historique des upgrades d'abonnement</h5>
                <p class="text-muted mb-0">Historique de tous les upgrades d'abonnement effectués</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label for="per_page" class="small text-muted mb-0">Par page:</label>
                <select id="per_page" class="form-select form-select-sm" style="width: auto;" onchange="updatePerPage(this.value)">
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                </select>
                <a href="{{ route('platform-admin.subscriptions.index') }}" class="btn btn-secondary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i> Retour
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Recherche et filtres -->
            <form method="GET" action="{{ route('platform-admin.subscriptions.upgrade-history') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Rechercher (entreprise, plan tarifaire, raison)" value="{{ request('search') }}">
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
                        <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('platform-admin.subscriptions.upgrade-history') }}" class="btn btn-secondary w-100">Réinitialiser</a>
                    </div>
                </div>
                @if(request('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
            </form>

            @if($upgrade_history->count() > 0)
                <div class="mb-3">
                    <small class="text-muted">
                        Affichage de {{ $upgrade_history->firstItem() }} à {{ $upgrade_history->lastItem() }} sur {{ $upgrade_history->total() }} upgrades
                    </small>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Entreprise</th>
                            <th>Ancien Plan</th>
                            <th>Nouveau Plan</th>
                            <th style="width: 120px;">Ancien Prix</th>
                            <th style="width: 120px;">Nouveau Prix</th>
                            <th style="width: 100px;">Document</th>
                            <th style="width: 150px;">Upgradé par</th>
                            <th style="width: 130px;">Date upgrade</th>
                            <th style="width: 100px;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($upgrade_history as $upgrade)
                            <tr>
                                <td><strong>#{{ $upgrade->id }}</strong></td>
                                <td>
                                    @if($upgrade->entreprise)
                                        <a href="{{ route('platform-admin.entreprises.show', $upgrade->entreprise_id) }}" class="text-primary text-decoration-none">
                                            <strong>{{ $upgrade->entreprise->name }}</strong>
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($upgrade->ancienPricingPlan)
                                        <span class="badge bg-label-secondary">{{ $upgrade->ancienPricingPlan->name }}</span>
                                    @else
                                        <span class="text-muted">Aucun</span>
                                    @endif
                                </td>
                                <td>
                                    @if($upgrade->nouveauPricingPlan)
                                        <span class="badge bg-label-success">{{ $upgrade->nouveauPricingPlan->name }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($upgrade->ancien_prix)
                                        <span class="text-muted">{{ number_format($upgrade->ancien_prix, 0, ',', ' ') }} {{ $upgrade->ancien_currency ?? 'FCFA' }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($upgrade->nouveau_prix)
                                        <strong class="text-success">{{ number_format($upgrade->nouveau_prix, 0, ',', ' ') }} {{ $upgrade->nouveau_currency ?? 'FCFA' }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($upgrade->document)
                                        <a href="{{ Storage::url($upgrade->document) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Télécharger le document">
                                            <i class="ti ti-file"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($upgrade->upgradedBy)
                                        <div>
                                            <strong class="d-block">{{ $upgrade->upgradedBy->first_name ?? '' }} {{ $upgrade->upgradedBy->last_name ?? '' }}</strong>
                                            <small class="text-muted">{{ $upgrade->upgradedBy->username ?? '' }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $upgrade->date_upgrade ? $upgrade->date_upgrade->format('d/m/Y') : 'N/A' }}</small>
                                    <br><small class="text-muted">{{ $upgrade->date_upgrade ? $upgrade->date_upgrade->format('H:i') : '' }}</small>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#upgradeModal{{ $upgrade->id }}" title="Voir les détails">
                                        <i class="ti ti-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <span class="text-muted">Aucun upgrade trouvé</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Modals pour les détails (placées en dehors du tableau) -->
            @foreach($upgrade_history as $upgrade)
                <div class="modal fade" id="upgradeModal{{ $upgrade->id }}" tabindex="-1" aria-labelledby="upgradeModalLabel{{ $upgrade->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="upgradeModalLabel{{ $upgrade->id }}">Détails de l'upgrade #{{ $upgrade->id }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="mb-3">Ancien abonnement</h6>
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <th width="150">Plan:</th>
                                                <td>{{ $upgrade->ancienPricingPlan ? $upgrade->ancienPricingPlan->name : 'Aucun' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Prix:</th>
                                                <td>
                                                    @if($upgrade->ancien_prix)
                                                        {{ number_format($upgrade->ancien_prix, 0, ',', ' ') }} {{ $upgrade->ancien_currency ?? 'FCFA' }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="mb-3">Nouveau abonnement</h6>
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <th width="150">Plan:</th>
                                                <td><strong class="text-success">{{ $upgrade->nouveauPricingPlan ? $upgrade->nouveauPricingPlan->name : 'N/A' }}</strong></td>
                                            </tr>
                                            <tr>
                                                <th>Prix:</th>
                                                <td>
                                                    @if($upgrade->nouveau_prix)
                                                        <strong class="text-success">{{ number_format($upgrade->nouveau_prix, 0, ',', ' ') }} {{ $upgrade->nouveau_currency ?? 'FCFA' }}</strong>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                @if($upgrade->raison)
                                    <div class="mt-3">
                                        <h6>Raison:</h6>
                                        <p class="mb-0">{{ $upgrade->raison }}</p>
                                    </div>
                                @endif
                                @if($upgrade->notes)
                                    <div class="mt-3">
                                        <h6>Notes:</h6>
                                        <p class="mb-0">{{ $upgrade->notes }}</p>
                                    </div>
                                @endif
                                @if($upgrade->document)
                                    <div class="mt-3">
                                        <h6>Document:</h6>
                                        <a href="{{ Storage::url($upgrade->document) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-file me-1"></i> Télécharger le document
                                        </a>
                                    </div>
                                @endif
                                <div class="mt-3">
                                    <h6>Informations</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <th width="150">Entreprise:</th>
                                            <td>
                                                @if($upgrade->entreprise)
                                                    <a href="{{ route('platform-admin.entreprises.show', $upgrade->entreprise_id) }}" class="text-primary">
                                                        {{ $upgrade->entreprise->name }}
                                                    </a>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Upgradé par:</th>
                                            <td>
                                                @if($upgrade->upgradedBy)
                                                    <strong>{{ $upgrade->upgradedBy->first_name ?? '' }} {{ $upgrade->upgradedBy->last_name ?? '' }}</strong>
                                                    <br><small class="text-muted">{{ $upgrade->upgradedBy->username ?? '' }}</small>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Date upgrade:</th>
                                            <td>{{ $upgrade->date_upgrade ? $upgrade->date_upgrade->format('d/m/Y H:i:s') : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Créé le:</th>
                                            <td>{{ $upgrade->created_at ? $upgrade->created_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            @if($upgrade_history->hasPages())
                <div class="mt-4">
                    {{ $upgrade_history->links() }}
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

