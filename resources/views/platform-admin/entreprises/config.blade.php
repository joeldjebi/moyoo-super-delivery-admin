@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Entreprises / <a href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}" class="text-muted text-decoration-none">{{ $entreprise->name }}</a> /</span> Configuration
    </h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Configuration de l'entreprise</h5>
            <a href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}" class="btn btn-secondary btn-sm">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="ti ti-map-pin me-2"></i>Zones & Communes</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>Commune Zone</li>
                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>Zones</li>
                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>Zone Activités</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="ti ti-package me-2"></i>Colis & Conditionnement</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>Conditionnement Colis</li>
                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>Type Colis</li>
                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>Package Colis</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="ti ti-truck me-2"></i>Livraisons</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>Tarif Livraisons</li>
                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>Mode Livraisons</li>
                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>Délais</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="ti ti-settings me-2"></i>Configuration</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>Engins</li>
                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>Type Engins</li>
                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>Planification Ramassage</li>
                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>Temps</li>
                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>Rôle Permissions</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 text-center">
                <p class="text-muted small">Toutes ces configurations seront affichées ici</p>
            </div>
        </div>
    </div>

@include('platform-admin.layouts.footer')

