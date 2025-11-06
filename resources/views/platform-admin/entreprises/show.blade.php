@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Gestion /</span> Détails de l'entreprise
    </h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(!$entreprise)
        <div class="alert alert-danger">
            Entreprise non trouvée.
        </div>
    @else
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Informations de l'entreprise</h5>
                <div>
                    <a href="{{ route('platform-admin.entreprises.index') }}" class="btn btn-secondary btn-sm">
                        <i class="ti ti-arrow-left me-1"></i> Retour à la liste
                    </a>
                    <a href="{{ route('platform-admin.subscriptions.upgrade-form', $entreprise->id) }}" class="btn btn-primary btn-sm ms-2">
                        <i class="ti ti-arrow-up me-1"></i> Upgrade Abonnement
                    </a>
                    @if($entreprise->statut == 1)
                        <form action="{{ route('platform-admin.entreprises.toggle-status', $entreprise->id) }}" method="POST" class="d-inline ms-2">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir désactiver cette entreprise ?')">
                                <i class="ti ti-toggle-left me-1"></i> Désactiver
                            </button>
                        </form>
                    @else
                        <form action="{{ route('platform-admin.entreprises.toggle-status', $entreprise->id) }}" method="POST" class="d-inline ms-2">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir activer cette entreprise ?')">
                                <i class="ti ti-toggle-right me-1"></i> Activer
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Informations principales</h6>
                        <table class="table table-borderless">
                            <tbody>

                                <tr>
                                    <th>Ne pas mettre à jour</th>
                                    <td>
                                        @if($entreprise->not_update)
                                            <span class="badge bg-label-warning">Oui</span>
                                        @else
                                            <span class="badge bg-label-info">Non</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width: 200px;">Nom de l'entreprise</th>
                                    <td><strong>{{ $entreprise->name ?? 'N/A' }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $entreprise->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Téléphone</th>
                                    <td>{{ $entreprise->mobile ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Adresse</th>
                                    <td>{{ $entreprise->adresse ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Statut</th>
                                    <td>
                                        @if($entreprise->statut == 1)
                                            <span class="badge bg-label-success">Actif</span>
                                        @else
                                            <span class="badge bg-label-danger">Inactif</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Informations complémentaires</h6>
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th>Commune</th>
                                    <td>
                                        @if($commune)
                                            {{ $commune->libelle }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Logo</th>
                                    <td>
                                        @if($entreprise->logo)
                                            @php
                                                // Construire l'URL du logo avec la variable d'environnement
                                                $logoBaseUrl = env('LOGO_ENRTREPRISE_URL', 'http://192.168.1.7:8000/');
                                                // Le champ logo peut contenir: "logos/filename.jpg" ou "storage/logos/filename.jpg" ou juste "filename.jpg"
                                                $logoPath = $entreprise->logo;

                                                // Si le logo contient déjà "storage/", on le retire
                                                if (strpos($logoPath, 'storage/') === 0) {
                                                    $logoPath = substr($logoPath, 8); // Retirer "storage/"
                                                }

                                                // Si le logo ne contient pas "logos/", on l'ajoute
                                                if (strpos($logoPath, 'logos/') === false) {
                                                    $logoPath = 'logos/' . $logoPath;
                                                }

                                                // Construire l'URL complète
                                                $logoUrl = rtrim($logoBaseUrl, '/') . '/storage/' . $logoPath;
                                            @endphp
                                            <img src="{{ $logoUrl }}" alt="Logo" class="img-thumbnail" style="max-width: 100px; cursor: pointer;" onclick="window.open('{{ $logoUrl }}', '_blank')">
                                        @else
                                            <span class="text-muted">Aucun logo</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Propriétaire</th>
                                    <td>
                                        @if($proprietaire)
                                            <strong>{{ $proprietaire->first_name ?? '' }} {{ $proprietaire->last_name ?? '' }}</strong>
                                            @if($proprietaire->email)
                                                <br><small class="text-muted">{{ $proprietaire->email }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Date de création</th>
                                    <td>{{ $entreprise->created_at ? $entreprise->created_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Dernière modification</th>
                                    <td>{{ $entreprise->updated_at ? $entreprise->updated_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation vers les sections -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Informations complémentaires</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="{{ route('platform-admin.entreprises.boutiques', $entreprise->id) }}" class="card border text-decoration-none h-100 transition-all">
                            <div class="card-body text-center">
                                <i class="ti ti-building-store text-primary mb-2" style="font-size: 2rem;"></i>
                                <h6 class="mb-0">Boutiques</h6>
                                <small class="text-muted">Liste des boutiques</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('platform-admin.entreprises.ramassages', $entreprise->id) }}" class="card border text-decoration-none h-100 transition-all">
                            <div class="card-body text-center">
                                <i class="ti ti-package text-info mb-3" style="font-size: 2.5rem;"></i>
                                <h6 class="mb-0">Historique Ramassages</h6>
                                <p class="text-muted small mb-0 mt-2">Voir l'historique des ramassages</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('platform-admin.entreprises.livraisons', $entreprise->id) }}" class="card border text-decoration-none h-100 transition-all">
                            <div class="card-body text-center">
                                <i class="ti ti-truck text-success mb-3" style="font-size: 2.5rem;"></i>
                                <h6 class="mb-0">Historique Livraisons</h6>
                                <p class="text-muted small mb-0 mt-2">Voir l'historique des livraisons</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('platform-admin.entreprises.colis', $entreprise->id) }}" class="card border text-decoration-none h-100 transition-all">
                            <div class="card-body text-center">
                                <i class="ti ti-box text-warning mb-3" style="font-size: 2.5rem;"></i>
                                <h6 class="mb-0">Colis</h6>
                                <p class="text-muted small mb-0 mt-2">Voir la liste des colis</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('platform-admin.entreprises.livreurs', $entreprise->id) }}" class="card border text-decoration-none h-100 transition-all">
                            <div class="card-body text-center">
                                <i class="ti ti-user text-secondary mb-3" style="font-size: 2.5rem;"></i>
                                <h6 class="mb-0">Livreurs</h6>
                                <p class="text-muted small mb-0 mt-2">Voir la liste des livreurs</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('platform-admin.entreprises.config', $entreprise->id) }}" class="card border text-decoration-none h-100 transition-all">
                            <div class="card-body text-center">
                                <i class="ti ti-settings text-danger mb-3" style="font-size: 2.5rem;"></i>
                                <h6 class="mb-0">Configuration</h6>
                                <p class="text-muted small mb-0 mt-2">Voir les configurations</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

<style>
.transition-all {
    transition: all 0.3s ease;
}
.transition-all:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-color: var(--bs-primary) !important;
}
</style>

@include('platform-admin.layouts.footer')

