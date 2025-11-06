@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">
            Gestion /
            <a href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}" class="text-muted text-decoration-none">{{ $entreprise->name }}</a> /
        </span> Upgrade d'abonnement
    </h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Upgrade d'abonnement - {{ $entreprise->name }}</h5>
            <div>
                <a href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}" class="btn btn-secondary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i> Retour
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($current_subscription)
                <div class="alert alert-info mb-4">
                    <h6 class="mb-2"><i class="ti ti-info-circle me-2"></i>Abonnement actuel</h6>
                    <p class="mb-1">
                        <strong>{{ $current_subscription->name ?? 'N/A' }}</strong>
                        @if($current_subscription->pricingPlan)
                            <br><small class="text-muted">Plan: {{ $current_subscription->pricingPlan->name }}</small>
                        @endif
                        <br><small class="text-muted">Prix: {{ number_format($current_subscription->price ?? 0, 0, ',', ' ') }} {{ $current_subscription->currency ?? 'FCFA' }}</small>
                        @if($current_subscription->expires_at)
                            <br><small class="text-muted">Expire le: {{ $current_subscription->expires_at->format('d/m/Y') }}</small>
                        @endif
                    </p>
                </div>
            @else
                <div class="alert alert-warning mb-4">
                    <h6 class="mb-2"><i class="ti ti-alert-triangle me-2"></i>Aucun abonnement actif</h6>
                    <p class="mb-0">Cette entreprise n'a pas d'abonnement actif. Vous pouvez créer un nouvel abonnement.</p>
                </div>
            @endif

            <form action="{{ route('platform-admin.subscriptions.upgrade', $entreprise->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label for="nouveau_pricing_plan_id" class="form-label">Nouveau plan tarifaire <span class="text-danger">*</span></label>
                    <select class="form-select @error('nouveau_pricing_plan_id') is-invalid @enderror"
                            id="nouveau_pricing_plan_id"
                            name="nouveau_pricing_plan_id"
                            required>
                        <option value="">Sélectionner un plan tarifaire</option>
                        @foreach($pricing_plans as $plan)
                            <option value="{{ $plan->id }}"
                                    {{ old('nouveau_pricing_plan_id') == $plan->id ? 'selected' : '' }}
                                    data-price="{{ $plan->price }}"
                                    data-currency="{{ $plan->currency ?? 'FCFA' }}"
                                    data-period="{{ $plan->period }}">
                                {{ $plan->name }} - {{ number_format($plan->price, 0, ',', ' ') }} {{ $plan->currency ?? 'FCFA' }} / {{ $plan->period == 'year' ? 'an' : 'mois' }}
                                @if($plan->is_popular)
                                    <span class="badge bg-label-warning">Populaire</span>
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('nouveau_pricing_plan_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="started_at" class="form-label">Date de début</label>
                    <input type="datetime-local"
                           class="form-control @error('started_at') is-invalid @enderror"
                           id="started_at"
                           name="started_at"
                           value="{{ old('started_at', now()->format('Y-m-d\TH:i')) }}">
                    <small class="text-muted">Laisser vide pour utiliser la date actuelle. La date d'expiration sera calculée automatiquement en fonction du plan tarifaire sélectionné.</small>
                    @error('started_at')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="raison" class="form-label">Raison de l'upgrade</label>
                    <textarea class="form-control @error('raison') is-invalid @enderror"
                              id="raison"
                              name="raison"
                              rows="3"
                              placeholder="Expliquez la raison de cet upgrade (optionnel)">{{ old('raison') }}</textarea>
                    @error('raison')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror"
                              id="notes"
                              name="notes"
                              rows="3"
                              placeholder="Notes supplémentaires (optionnel)">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="document" class="form-label">Document <span class="text-muted">(optionnel)</span></label>
                    <input type="file"
                           class="form-control @error('document') is-invalid @enderror"
                           id="document"
                           name="document"
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    <small class="text-muted">Formats acceptés: PDF, DOC, DOCX, JPG, JPEG, PNG (max 10MB)</small>
                    @error('document')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-warning">
                    <i class="ti ti-alert-triangle me-2"></i>
                    <strong>Attention:</strong> L'ancien abonnement sera désactivé et un nouveau abonnement sera créé avec le plan tarifaire sélectionné.
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i> Confirmer l'upgrade
                    </button>
                    <a href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}" class="btn btn-secondary ms-2">
                        <i class="ti ti-x me-1"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

@include('platform-admin.layouts.footer')

