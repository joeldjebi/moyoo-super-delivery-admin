@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">
            Entreprises /
            <a href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}" class="text-muted text-decoration-none">{{ $entreprise->name }}</a> /
            <a href="{{ route('platform-admin.entreprises.boutiques', $entreprise->id) }}" class="text-muted text-decoration-none">Boutiques</a> /
            <a href="{{ route('platform-admin.entreprises.boutique.show', [$entreprise->id, $boutique->id]) }}" class="text-muted text-decoration-none">{{ $boutique->libelle }}</a> /
            <a href="{{ route('platform-admin.entreprises.boutique.livraisons', [$entreprise->id, $boutique->id]) }}" class="text-muted text-decoration-none">Livraisons</a> /
        </span> Détails Livraison
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
            <h5 class="mb-0">Informations de la livraison</h5>
            <a href="{{ route('platform-admin.entreprises.boutique.livraisons', [$entreprise->id, $boutique->id]) }}" class="btn btn-secondary btn-sm">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th>Statut:</th>
                            <td>
                                @if($livraison->status == 'termine')
                                    <span class="badge bg-label-success">Terminé</span>
                                @elseif($livraison->status == 'en_cours')
                                    <span class="badge bg-label-info">En cours</span>
                                @elseif($livraison->status == 'annule')
                                    <span class="badge bg-label-warning">Annulé</span>
                                @elseif($livraison->status == 'echoue')
                                    <span class="badge bg-label-danger">Échoué</span>
                                @else
                                    <span class="badge bg-label-secondary">{{ $livraison->status }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Colis:</th>
                            <td>
                                @if($livraison->colis)
                                    <strong>{{ $livraison->colis->code }}</strong>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Code validation:</th>
                            <td>{{ $livraison->code_validation_utilise ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Date livraison effective:</th>
                            <td>{{ $livraison->date_livraison_effective ? $livraison->date_livraison_effective->format('d/m/Y H:i:s') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Motif annulation:</th>
                            <td>{{ $livraison->motif_annulation ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Livreur:</th>
                            <td>
                                @if($livreur)
                                    {{ $livreur->first_name ?? '' }} {{ $livreur->last_name ?? '' }}
                                    @if($livreur->mobile)
                                        <br><small class="text-muted">Tel: {{ $livreur->mobile }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Coordonnées GPS:</th>
                            <td>
                                @if($livraison->latitude && $livraison->longitude)
                                    Latitude: {{ $livraison->latitude }}, Longitude: {{ $livraison->longitude }}
                                    <br><small class="text-muted">
                                        <a href="https://www.google.com/maps?q={{ $livraison->latitude }},{{ $livraison->longitude }}" target="_blank" class="text-primary">
                                            <i class="ti ti-map me-1"></i> Voir sur Google Maps
                                        </a>
                                    </small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Créé le:</th>
                            <td>{{ $livraison->created_at ? $livraison->created_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Modifié le:</th>
                            <td>{{ $livraison->updated_at ? $livraison->updated_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
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

    <!-- Informations financières -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Informations financières</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-label-primary">
                        <div class="card-body text-center">
                            <h6 class="mb-1">Montant à encaisser</h6>
                            <h4 class="mb-0">{{ number_format($livraison->montant_a_encaisse, 0) }} FCFA</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-label-success">
                        <div class="card-body text-center">
                            <h6 class="mb-1">Prix de vente</h6>
                            <h4 class="mb-0">{{ number_format($livraison->prix_de_vente, 0) }} FCFA</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-label-info">
                        <div class="card-body text-center">
                            <h6 class="mb-1">Montant de la livraison</h6>
                            <h4 class="mb-0">{{ number_format($livraison->montant_de_la_livraison, 0) }} FCFA</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Note de livraison -->
    @if($livraison->note_livraison)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Note de livraison</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $livraison->note_livraison }}</p>
            </div>
        </div>
    @endif

    <!-- Photos de preuve -->
    @if(!empty($photos))
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Photos de preuve de livraison</h5>
                <small class="text-muted">{{ count($photos) }} photo(s)</small>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($photos as $index => $photo)
                        <div class="col-md-4 col-lg-3">
                            <div class="card border">
                                <div class="card-body p-2">
                                    <div class="position-relative" style="cursor: pointer;" onclick="openPhotoModal({{ $index }})">
                                        <img src="{{ $photo }}"
                                             alt="Photo de preuve {{ $index + 1 }}"
                                             class="img-fluid rounded w-100"
                                             style="height: 200px; object-fit: cover;">
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge bg-dark bg-opacity-50">
                                                <i class="ti ti-zoom-in"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-center mt-2">
                                        <small class="text-muted">Photo {{ $index + 1 }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Modal unique pour toutes les photos -->
                <div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="photoModalLabel">Photo de preuve</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img id="photoModalImage" src="" alt="Photo de preuve" class="img-fluid rounded" style="max-height: 70vh;">
                            </div>
                            <div class="modal-footer">
                                <a id="photoModalLink" href="#" target="_blank" class="btn btn-primary">
                                    <i class="ti ti-external-link me-1"></i> Ouvrir dans un nouvel onglet
                                </a>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                // Photos disponibles
                const photos = @json($photos);

                function openPhotoModal(index) {
                    if (index >= 0 && index < photos.length) {
                        const fullUrl = photos[index];

                        // Mettre à jour l'image du modal
                        document.getElementById('photoModalImage').src = fullUrl;

                        // Mettre à jour le lien
                        document.getElementById('photoModalLink').href = fullUrl;

                        // Mettre à jour le titre
                        document.getElementById('photoModalLabel').textContent = 'Photo de preuve ' + (index + 1);

                        // Ouvrir le modal
                        const modal = new bootstrap.Modal(document.getElementById('photoModal'));
                        modal.show();
                    }
                }
                </script>
            </div>
        </div>
    @endif

    <!-- Signature -->
    @if($livraison->signature_data)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Signature</h5>
            </div>
            <div class="card-body text-center">
                <img src="{{ $livraison->signature_data }}" alt="Signature" class="img-fluid rounded" style="max-height: 300px; background: white; padding: 10px;">
            </div>
        </div>
    @endif

    <!-- Informations du colis -->
    @if($livraison->colis)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informations du colis</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="200">Code:</th>
                                <td><strong>{{ $livraison->colis->code }}</strong></td>
                            </tr>
                            <tr>
                                <th>Client:</th>
                                <td>{{ $livraison->colis->nom_client ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Téléphone:</th>
                                <td>{{ $livraison->colis->telephone_client ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Adresse:</th>
                                <td>{{ $livraison->colis->adresse_client ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="200">Statut:</th>
                                <td>
                                    @if($livraison->colis->status == 0)
                                        <span class="badge bg-label-secondary">En attente</span>
                                    @elseif($livraison->colis->status == 1)
                                        <span class="badge bg-label-info">En cours</span>
                                    @elseif($livraison->colis->status == 2)
                                        <span class="badge bg-label-success">Terminé</span>
                                    @elseif($livraison->colis->status == 3)
                                        <span class="badge bg-label-warning">Annulé (client)</span>
                                    @elseif($livraison->colis->status == 4)
                                        <span class="badge bg-label-warning">Annulé (livreur)</span>
                                    @elseif($livraison->colis->status == 5)
                                        <span class="badge bg-label-warning">Annulé (marchand)</span>
                                    @else
                                        <span class="badge bg-label-secondary">Inconnu</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Prix de vente:</th>
                                <td>{{ $livraison->colis->prix_de_vente ? number_format($livraison->colis->prix_de_vente, 2) . ' FCFA' : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Frais de livraison:</th>
                                <td>{{ $livraison->colis->frais_livraison ? number_format($livraison->colis->frais_livraison, 2) . ' FCFA' : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Date création:</th>
                                <td>{{ $livraison->colis->created_at ? $livraison->colis->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

@include('platform-admin.layouts.footer')

