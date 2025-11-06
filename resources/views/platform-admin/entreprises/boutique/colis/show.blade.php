@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">
            Entreprises /
            <a href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}" class="text-muted text-decoration-none">{{ $entreprise->name }}</a> /
            <a href="{{ route('platform-admin.entreprises.boutiques', $entreprise->id) }}" class="text-muted text-decoration-none">Boutiques</a> /
            <a href="{{ route('platform-admin.entreprises.boutique.show', [$entreprise->id, $boutique->id]) }}" class="text-muted text-decoration-none">{{ $boutique->libelle }}</a> /
            <a href="{{ route('platform-admin.entreprises.boutique.colis', [$entreprise->id, $boutique->id]) }}" class="text-muted text-decoration-none">Colis</a> /
        </span> Détails Colis
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
            <h5 class="mb-0">Informations du colis</h5>
            <a href="{{ route('platform-admin.entreprises.boutique.colis', [$entreprise->id, $boutique->id]) }}" class="btn btn-secondary btn-sm">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">ID:</th>
                            <td><strong>#{{ $colis->id }}</strong></td>
                        </tr>
                        <tr>
                            <th>UUID:</th>
                            <td><code>{{ $colis->uuid ?? 'N/A' }}</code></td>
                        </tr>
                        <tr>
                            <th>Code:</th>
                            <td><strong>{{ $colis->code }}</strong></td>
                        </tr>
                        <tr>
                            <th>Statut:</th>
                            <td>
                                @if($colis->status == 0)
                                    <span class="badge bg-label-secondary">En attente</span>
                                @elseif($colis->status == 1)
                                    <span class="badge bg-label-info">En cours</span>
                                @elseif($colis->status == 2)
                                    <span class="badge bg-label-success">Terminé</span>
                                @elseif($colis->status == 3)
                                    <span class="badge bg-label-warning">Annulé (client)</span>
                                @elseif($colis->status == 4)
                                    <span class="badge bg-label-warning">Annulé (livreur)</span>
                                @elseif($colis->status == 5)
                                    <span class="badge bg-label-warning">Annulé (marchand)</span>
                                @else
                                    <span class="badge bg-label-secondary">Inconnu</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Client:</th>
                            <td>{{ $colis->nom_client ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Téléphone client:</th>
                            <td>{{ $colis->telephone_client ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Adresse client:</th>
                            <td>{{ $colis->adresse_client ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Numéro facture:</th>
                            <td>{{ $colis->numero_facture ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Ordre livraison:</th>
                            <td>{{ $colis->ordre_livraison ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Date livraison prévue:</th>
                            <td>{{ $colis->date_livraison_prevue ? \Carbon\Carbon::parse($colis->date_livraison_prevue)->format('d/m/Y H:i') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Créé le:</th>
                            <td>{{ $colis->created_at ? $colis->created_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Commune:</th>
                            <td>{{ $commune ? $commune->libelle : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Zone:</th>
                            <td>{{ $zone ? $zone->nom : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Livreur:</th>
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
                            <th>Engin:</th>
                            <td>
                                @if($engin)
                                    {{ $engin->nom ?? $engin->libelle ?? 'N/A' }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Poids:</th>
                            <td>
                                @if($poids)
                                    {{ $poids->libelle ?? $poids->nom ?? 'N/A' }}
                                    @if($poids->libelle)
                                        <br><small class="text-muted">Valeur: {{ $poids->libelle }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Mode livraison:</th>
                            <td>
                                @if($mode_livraison)
                                    {{ $mode_livraison->libelle ?? $mode_livraison->nom ?? 'N/A' }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Temp:</th>
                            <td>
                                @if($temp)
                                    {{ $temp->libelle ?? $temp->nom ?? 'N/A' }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
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
                        <tr>
                            <th>Modifié le:</th>
                            <td>{{ $colis->updated_at ? $colis->updated_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
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
                            <h6 class="mb-1">Prix de vente</h6>
                            <h4 class="mb-0">{{ $colis->prix_de_vente ? number_format($colis->prix_de_vente, 0) . ' FCFA' : 'N/A' }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-label-success">
                        <div class="card-body text-center">
                            <h6 class="mb-1">Montant de la livraison</h6>
                            <h4 class="mb-0">
                                @if($livraison && $livraison->montant_de_la_livraison)
                                    {{ number_format($livraison->montant_de_la_livraison, 0) }} FCFA
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-label-info">
                        <div class="card-body text-center">
                            <h6 class="mb-1">Montant à encaisser</h6>
                            <h4 class="mb-0">{{ $colis->montant_a_encaisse ? number_format($colis->montant_a_encaisse, 0) . ' FCFA' : 'N/A' }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations complémentaires -->
    @if($colis->note_client || $colis->instructions_livraison)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informations complémentaires</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    @if($colis->note_client)
                        <tr>
                            <th width="200">Note client:</th>
                            <td>{{ $colis->note_client }}</td>
                        </tr>
                    @endif
                    @if($colis->instructions_livraison)
                        <tr>
                            <th>Instructions livraison:</th>
                            <td>{{ $colis->instructions_livraison }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    @endif

@include('platform-admin.layouts.footer')

