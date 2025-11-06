@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">
            Entreprises /
            <a href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}" class="text-muted text-decoration-none">{{ $entreprise->name }}</a> /
            <a href="{{ route('platform-admin.entreprises.boutiques', $entreprise->id) }}" class="text-muted text-decoration-none">Boutiques</a> /
            <a href="{{ route('platform-admin.entreprises.boutique.show', [$entreprise->id, $boutique->id]) }}" class="text-muted text-decoration-none">{{ $boutique->libelle }}</a> /
            <a href="{{ route('platform-admin.entreprises.boutique.ramassages', [$entreprise->id, $boutique->id]) }}" class="text-muted text-decoration-none">Ramassages</a> /
        </span> Détails Ramassage
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
            <h5 class="mb-0">Informations du ramassage</h5>
            <a href="{{ route('platform-admin.entreprises.boutique.ramassages', [$entreprise->id, $boutique->id]) }}" class="btn btn-secondary btn-sm">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Code:</th>
                            <td><strong>{{ $ramassage->code_ramassage }}</strong></td>
                        </tr>
                        <tr>
                            <th>Statut:</th>
                            <td>
                                @if($ramassage->statut == 'demande')
                                    <span class="badge bg-label-secondary">Demande</span>
                                @elseif($ramassage->statut == 'planifie')
                                    <span class="badge bg-label-info">Planifié</span>
                                @elseif($ramassage->statut == 'en_cours')
                                    <span class="badge bg-label-primary">En cours</span>
                                @elseif($ramassage->statut == 'termine')
                                    <span class="badge bg-label-success">Terminé</span>
                                @elseif($ramassage->statut == 'annule')
                                    <span class="badge bg-label-warning">Annulé</span>
                                @else
                                    <span class="badge bg-label-secondary">Inconnu</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Boutique:</th>
                            <td>{{ $ramassage->boutique ? $ramassage->boutique->libelle : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Adresse ramassage:</th>
                            <td>{{ $ramassage->adresse_ramassage ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Contact ramassage:</th>
                            <td>{{ $ramassage->contact_ramassage ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Date demande:</th>
                            <td>{{ $ramassage->date_demande ? $ramassage->date_demande->format('d/m/Y H:i:s') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Date planifiée:</th>
                            <td>{{ $ramassage->date_planifiee ? $ramassage->date_planifiee->format('d/m/Y H:i:s') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Date effectuée:</th>
                            <td>{{ $ramassage->date_effectuee ? \Carbon\Carbon::parse($ramassage->date_effectuee)->format('d/m/Y') : 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Livreur:</th>
                            <td>
                                @if($livreur)
                                    {{ ($livreur->first_name ?? '') . ' ' . ($livreur->last_name ?? '') }}
                                    @if($livreur->mobile)
                                        <br><small class="text-muted">Tel: {{ $livreur->mobile }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Date début ramassage:</th>
                            <td>{{ $ramassage->date_debut_ramassage ? $ramassage->date_debut_ramassage->format('d/m/Y H:i:s') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Date fin ramassage:</th>
                            <td>{{ $ramassage->date_fin_ramassage ? $ramassage->date_fin_ramassage->format('d/m/Y H:i:s') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Créé le:</th>
                            <td>{{ $ramassage->created_at ? $ramassage->created_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Modifié le:</th>
                            <td>{{ $ramassage->updated_at ? $ramassage->updated_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                        </tr>
                        @if($ramassage->statut == 'annule')
                            <tr>
                                <th>Annulé par:</th>
                                <td>
                                    @if($annuleur)
                                        <strong>{{ $annuleur->first_name ?? '' }} {{ $annuleur->last_name ?? '' }}</strong>
                                        @if($annuleur->email)
                                            <br><small class="text-muted">Email: {{ $annuleur->email }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Date annulation:</th>
                                <td>{{ $ramassage->date_annulation ? $ramassage->date_annulation->format('d/m/Y H:i:s') : 'N/A' }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations colis -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Informations colis</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-label-primary">
                        <div class="card-body text-center">
                            <h6 class="mb-1">Nombre colis estimé</h6>
                            <h4 class="mb-0">{{ $ramassage->nombre_colis_estime ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-label-success">
                        <div class="card-body text-center">
                            <h6 class="mb-1">Nombre colis réel</h6>
                            <h4 class="mb-0">{{ $ramassage->nombre_colis_reel ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-label-info">
                        <div class="card-body text-center">
                            <h6 class="mb-1">Différence</h6>
                            <h4 class="mb-0">
                                @if($ramassage->difference_colis)
                                    {{ $ramassage->difference_colis > 0 ? '+' : '' }}{{ $ramassage->difference_colis }}
                                @else
                                    0
                                @endif
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
            @if($ramassage->type_difference || $ramassage->raison_difference)
                <div class="mt-3">
                    <table class="table table-borderless">
                        @if($ramassage->type_difference)
                            <tr>
                                <th width="200">Type différence:</th>
                                <td>{{ $ramassage->type_difference }}</td>
                            </tr>
                        @endif
                        @if($ramassage->raison_difference)
                            <tr>
                                <th>Raison différence:</th>
                                <td>{{ $ramassage->raison_difference }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Informations financières -->
    @if($ramassage->montant_total)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informations financières</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-borderless">
                            <tr>
                                <th width="200">Montant total:</th>
                                <td><h4 class="mb-0">{{ number_format($ramassage->montant_total, 2) }} FCFA</h4></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Informations annulation -->
    @if($ramassage->statut == 'annule')
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informations annulation</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    @if($ramassage->raison_annulation)
                        <tr>
                            <th width="200">Raison annulation:</th>
                            <td>{{ $ramassage->raison_annulation }}</td>
                        </tr>
                    @endif
                    @if($ramassage->commentaire_annulation)
                        <tr>
                            <th>Commentaire annulation:</th>
                            <td>{{ $ramassage->commentaire_annulation }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    @endif

    <!-- Notes -->
    @if($ramassage->notes || $ramassage->notes_livreur || $ramassage->notes_ramassage)
        <div class="card mb-4">
            <div class="card-body">

                <!-- Parser notes_livreur pour extraire les photos et notes livreur -->
                @if($ramassage->notes_livreur)
                    @php
                        $photoBaseUrl = env('PHOTO_PROOF_BASE_URL', 'http://192.168.1.8:8000/');
                        $notesText = $ramassage->notes_livreur;
                        $photosColis = [];
                        $notesLivreurText = null;

                        // Extraire les notes livreur (texte après "Notes livreur:")
                        if (preg_match('/Notes livreur:\s*(.+)/is', $notesText, $notesMatches)) {
                            $notesLivreurText = trim($notesMatches[1]);
                        }

                        // Parser les photos - Format: "📸 PHOTOS DES COLIS RAMASSÉS (X photos):\n- Date: ...\n- filename.jpg"
                        // Chercher si le texte contient la section des photos (avec ou sans emoji)
                        $hasPhotosSection = preg_match('/PHOTOS.*?DES.*?COLIS.*?RAMASSÉS/i', $notesText) || strpos($notesText, '📸') !== false;

                        if ($hasPhotosSection) {
                            // Extraire la date (format: "05/11/2025 12:16" ou "Date: 05/11/2025 12:16")
                            $globalDate = null;
                            // Chercher avec "- Date:" ou juste "Date:"
                            if (preg_match('/(?:-\s*)?Date:\s*(\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2})/i', $notesText, $dateMatches)) {
                                $globalDate = trim($dateMatches[1]);
                            } elseif (preg_match('/(\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2})/i', $notesText, $dateMatches)) {
                                $globalDate = trim($dateMatches[1]);
                            }

                            // Extraire tous les noms de fichiers
                            // Pattern pour capturer: "- colis_2_1762345016_1.jpg" ou juste "colis_2_1762345016_1.jpg"
                            // Supporte les underscores, tirets, et points dans le nom
                            if (preg_match_all('/(?:-\s*)?([a-zA-Z0-9_\-\.]+\.(jpg|jpeg|png|gif|JPG|JPEG|PNG|GIF))/i', $notesText, $fileMatches)) {
                                foreach ($fileMatches[1] as $index => $filename) {
                                    $filename = trim($filename);

                                    if (empty($filename)) continue;

                                    // Chercher la date spécifique pour ce fichier (avant le fichier)
                                    $photoDate = $globalDate;

                                    // Trouver toutes les occurrences du fichier pour prendre la première
                                    $filePos = strpos($notesText, $filename);
                                    if ($filePos !== false) {
                                        // Extraire le texte avant le fichier
                                        $beforeFile = substr($notesText, 0, $filePos);
                                        // Chercher la dernière date avant ce fichier
                                        if (preg_match_all('/(\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2})/i', $beforeFile, $localDateMatches)) {
                                            if (!empty($localDateMatches[1])) {
                                                $photoDate = trim(end($localDateMatches[1]));
                                            }
                                        }
                                    }

                                    $photoPath = 'ramassages/photos/' . $filename;
                                    $photoUrl = $photoBaseUrl . 'storage/' . $photoPath;

                                    $photosColis[] = [
                                        'filename' => $filename,
                                        'url' => $photoUrl,
                                        'date' => $photoDate,
                                    ];
                                }
                            }
                        }
                    @endphp

                    @if(!empty($photosColis) && count($photosColis) > 0)
                        <div class="mt-4 pt-4 border-top">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-photo me-2"></i>
                                        Photos des Colis Ramassés ({{ count($photosColis) }})
                                    </h6>
                                    @if($photosColis[0]['date'] ?? null)
                                        <small class="text-muted">Date: {{ $photosColis[0]['date'] }}</small>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        @foreach($photosColis as $photo)
                                            <div class="col-md-4 col-lg-3">
                                                <div class="card border">
                                                    <div class="card-body p-2">
                                                        <div class="position-relative">
                                                            <img src="{{ $photo['url'] }}"
                                                                 alt="{{ $photo['filename'] }}"
                                                                 class="img-fluid rounded"
                                                                 style="max-height: 200px; width: 100%; object-fit: cover;"
                                                                 onerror="this.src='{{ asset('images/placeholder-image.png') }}'">
                                                            <a href="{{ $photo['url'] }}"
                                                               target="_blank"
                                                               class="btn btn-sm btn-outline-primary position-absolute top-0 end-0 m-2"
                                                               title="Voir en grand">
                                                                <i class="ti ti-maximize"></i>
                                                            </a>
                                                        </div>
                                                        <div class="mt-2">
                                                            <small class="text-muted d-block">{{ $photo['filename'] }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Notes du Livreur -->
                    @if(!empty($notesLivreurText))
                        <div class="mt-4 pt-4 border-top">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-note me-2"></i>
                                        Notes du Livreur
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="bg-light p-3 rounded">
                                        <p class="mb-0" style="white-space: pre-wrap;">{{ $notesLivreurText }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @endif

    <!-- Photo ramassage -->
    @if($ramassage->photo_ramassage)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Photo du ramassage</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        @php
                            $photoBaseUrl = env('PHOTO_PROOF_BASE_URL', 'http://192.168.1.8:8000/');
                            $photoUrl = $photoBaseUrl . 'storage/' . $ramassage->photo_ramassage;
                        @endphp
                        <img src="{{ $photoUrl }}" alt="Photo ramassage" class="img-fluid rounded" style="max-height: 400px; cursor: pointer;" onclick="window.open('{{ $photoUrl }}', '_blank')">
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Planifications -->
    @if($ramassage->planifications && $ramassage->planifications->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Planifications</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date planifiée</th>
                                <th>Heure début</th>
                                <th>Heure fin</th>
                                <th>Zone ramassage</th>
                                <th>Ordre visite</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ramassage->planifications as $planification)
                                <tr>
                                    <td>{{ $planification->date_planifiee ? $planification->date_planifiee->format('d/m/Y') : 'N/A' }}</td>
                                    <td>{{ $planification->heure_debut ?? 'N/A' }}</td>
                                    <td>{{ $planification->heure_fin ?? 'N/A' }}</td>
                                    <td>{{ $planification->zone_ramassage ?? 'N/A' }}</td>
                                    <td>{{ $planification->ordre_visite ?? 'N/A' }}</td>
                                    <td>
                                        @if($planification->statut_planification == 'planifie')
                                            <span class="badge bg-label-info">Planifié</span>
                                        @elseif($planification->statut_planification == 'en_cours')
                                            <span class="badge bg-label-primary">En cours</span>
                                        @elseif($planification->statut_planification == 'termine')
                                            <span class="badge bg-label-success">Terminé</span>
                                        @elseif($planification->statut_planification == 'annule')
                                            <span class="badge bg-label-warning">Annulé</span>
                                        @else
                                            <span class="badge bg-label-secondary">Inconnu</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

@include('platform-admin.layouts.footer')

