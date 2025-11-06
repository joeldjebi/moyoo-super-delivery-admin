@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">
            Entreprises /
            <a href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}" class="text-muted text-decoration-none">{{ $entreprise->name }}</a> /
            <a href="{{ route('platform-admin.entreprises.boutiques', $entreprise->id) }}" class="text-muted text-decoration-none">Boutiques</a> /
        </span> {{ $boutique->libelle }}
    </h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Informations principales -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Informations de la boutique</h5>
            <a href="{{ route('platform-admin.entreprises.boutiques', $entreprise->id) }}" class="btn btn-secondary btn-sm">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th>Image de couverture:</th>
                            <td>
                                @if($boutique->cover_image)
                                    @php
                                        $logoBaseUrl = env('LOGO_ENRTREPRISE_URL', 'http://192.168.1.7:8000/');
                                        $coverPath = $boutique->cover_image;

                                        // Si le cover_image contient déjà "storage/", on le retire
                                        if (strpos($coverPath, 'storage/') === 0) {
                                            $coverPath = substr($coverPath, 8); // Retirer "storage/"
                                        }

                                        // Si le cover_image ne contient pas "boutiques/", on l'ajoute
                                        if (strpos($coverPath, 'boutiques/') === false) {
                                            $coverPath = 'boutiques/' . $coverPath;
                                        }

                                        // Construire l'URL complète
                                        $coverUrl = rtrim($logoBaseUrl, '/') . '/storage/' . $coverPath;
                                    @endphp
                                    <img src="{{ $coverUrl }}" alt="Image de couverture" class="img-thumbnail" style="max-width: 100px; cursor: pointer;" onclick="window.open('{{ $coverUrl }}', '_blank')">
                                @else
                                    <span class="text-muted">Aucune image</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th width="200">Nom de la boutique:</th>
                            <td><strong>{{ $boutique->libelle }}</strong></td>
                        </tr>
                        <tr>
                            <th>Mobile:</th>
                            <td>{{ $boutique->mobile ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Adresse:</th>
                            <td>{{ $boutique->adresse ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Adresse GPS:</th>
                            <td>{{ $boutique->adresse_gps ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Statut:</th>
                            <td>
                                @if($boutique->status == 'active')
                                    <span class="badge bg-label-success">Actif</span>
                                @elseif($boutique->status == 'inactive')
                                    <span class="badge bg-label-danger">Inactif</span>
                                @else
                                    <span class="badge bg-label-warning">Suspendu</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Marchand:</th>
                            <td>
                                @if($boutique->marchand)
                                    <a href="{{ route('platform-admin.entreprises.marchand.show', [$entreprise->id, $boutique->marchand->id]) }}" class="text-decoration-none">
                                        <strong>{{ $boutique->marchand->full_name }}</strong>
                                    </a>
                                    @if($boutique->marchand->email)
                                        <br><small class="text-muted">Email: {{ $boutique->marchand->email }}</small>
                                    @endif
                                    @if($boutique->marchand->mobile)
                                        <br><small class="text-muted">Tel: {{ $boutique->marchand->mobile }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Commune:</th>
                            <td>{{ $commune ? $commune->libelle : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Créé le:</th>
                            <td>{{ $boutique->created_at ? $boutique->created_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Modifié le:</th>
                            <td>{{ $boutique->updated_at ? $boutique->updated_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Créé par:</th>
                            <td>
                                @if($createur)
                                    <strong>{{ $createur->first_name ?? '' }} {{ $createur->last_name ?? '' }}</strong>
                                    @if($createur->email)
                                        <br><small class="text-muted">Email: {{ $createur->email }}</small>
                                    @endif
                                    @if($createur->mobile)
                                        <br><small class="text-muted">Tel: {{ $createur->mobile }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <!-- Statistiques Colis -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Statistiques Colis</h5>
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('platform-admin.entreprises.boutique.colis', [$entreprise->id, $boutique->id]) }}" class="btn btn-primary">
                            <i class="ti ti-box me-1"></i> Voir les colis
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h3 class="mb-0">{{ $stats['colis']['total'] ?? 0 }}</h3>
                        <small class="text-muted">Total de colis</small>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="card bg-label-success">
                                <div class="card-body text-center">
                                    <h4 class="mb-0">{{ $stats['colis']['termine'] ?? 0 }}</h4>
                                    <small class="text-muted">Terminés</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-label-info">
                                <div class="card-body text-center">
                                    <h4 class="mb-0">{{ $stats['colis']['en_cours'] ?? 0 }}</h4>
                                    <small class="text-muted">En cours</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-label-warning">
                                <div class="card-body text-center">
                                    <h4 class="mb-0">{{ $stats['colis']['annule'] ?? 0 }}</h4>
                                    <small class="text-muted">Annulés</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-label-secondary">
                                <div class="card-body text-center">
                                    <h4 class="mb-0">{{ $stats['colis']['en_attente'] ?? 0 }}</h4>
                                    <small class="text-muted">En attente</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques Ramassages -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Stats Ramassages</h5>
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('platform-admin.entreprises.boutique.ramassages', [$entreprise->id, $boutique->id]) }}" class="btn btn-primary">
                            <i class="ti ti-package me-1"></i> Voir les ramassages
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h3 class="mb-0">{{ $stats['ramassages']['total'] ?? 0 }}</h3>
                        <small class="text-muted">Total de ramassages</small>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="card bg-label-success">
                                <div class="card-body text-center">
                                    <h4 class="mb-0">{{ $stats['ramassages']['termine'] ?? 0 }}</h4>
                                    <small class="text-muted">Terminés</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-label-info">
                                <div class="card-body text-center">
                                    <h4 class="mb-0">{{ $stats['ramassages']['en_cours'] ?? 0 }}</h4>
                                    <small class="text-muted">En cours</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-label-warning">
                                <div class="card-body text-center">
                                    <h4 class="mb-0">{{ $stats['ramassages']['annule'] ?? 0 }}</h4>
                                    <small class="text-muted">Annulés</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-label-secondary">
                                <div class="card-body text-center">
                                    <h4 class="mb-0">{{ $stats['ramassages']['planifie'] ?? 0 }}</h4>
                                    <small class="text-muted">Planifiés</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques Livraisons -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Stats Livraisons</h5>
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('platform-admin.entreprises.boutique.livraisons', [$entreprise->id, $boutique->id]) }}" class="btn btn-primary">
                            <i class="ti ti-truck me-1"></i> Voir les livraisons
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h3 class="mb-0">{{ $stats['livraisons']['total'] ?? 0 }}</h3>
                        <small class="text-muted">Total de livraisons</small>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="card bg-label-success">
                                <div class="card-body text-center">
                                    <h4 class="mb-0">{{ $stats['livraisons']['termine'] ?? 0 }}</h4>
                                    <small class="text-muted">Terminés</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-label-info">
                                <div class="card-body text-center">
                                    <h4 class="mb-0">{{ $stats['livraisons']['en_cours'] ?? 0 }}</h4>
                                    <small class="text-muted">En cours</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-label-warning">
                                <div class="card-body text-center">
                                    <h4 class="mb-0">{{ $stats['livraisons']['annule'] ?? 0 }}</h4>
                                    <small class="text-muted">Annulés</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-label-danger">
                                <div class="card-body text-center">
                                    <h4 class="mb-0">{{ $stats['livraisons']['echoue'] ?? 0 }}</h4>
                                    <small class="text-muted">Échoués</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@include('platform-admin.layouts.footer')

