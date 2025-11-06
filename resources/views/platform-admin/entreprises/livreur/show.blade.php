@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">
            Entreprises /
            <a href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}" class="text-muted text-decoration-none">{{ $entreprise->name }}</a> /
            <a href="{{ route('platform-admin.entreprises.livreurs', $entreprise->id) }}" class="text-muted text-decoration-none">Livreurs</a> /
        </span> Détails du livreur
    </h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(!$livreur)
        <div class="alert alert-danger">
            Livreur non trouvé.
        </div>
    @else
        <!-- Informations principales -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Informations du livreur</h5>
                <div>
                    <a href="{{ route('platform-admin.entreprises.livreurs', $entreprise->id) }}" class="btn btn-secondary btn-sm">
                        <i class="ti ti-arrow-left me-1"></i> Retour à la liste
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="200">ID:</th>
                                <td><strong>#{{ $livreur->id }}</strong></td>
                            </tr>
                            <tr>
                                <th>Prénom:</th>
                                <td><strong>{{ $livreur->first_name ?? 'N/A' }}</strong></td>
                            </tr>
                            <tr>
                                <th>Nom:</th>
                                <td><strong>{{ $livreur->last_name ?? 'N/A' }}</strong></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td>{{ $livreur->email ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Téléphone:</th>
                                <td>{{ $livreur->mobile ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Adresse:</th>
                                <td>{{ $livreur->adresse ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Statut:</th>
                                <td>
                                    @if(($livreur->status ?? 'active') == 'active')
                                        <span class="badge bg-label-success">Actif</span>
                                    @elseif(($livreur->status ?? '') == 'inactive')
                                        <span class="badge bg-label-secondary">Inactif</span>
                                    @elseif(($livreur->status ?? '') == 'suspended')
                                        <span class="badge bg-label-warning">Suspendu</span>
                                    @else
                                        <span class="badge bg-label-secondary">{{ $livreur->status ?? 'N/A' }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Email vérifié:</th>
                                <td>
                                    @if(isset($livreur->email_verified_at) && $livreur->email_verified_at)
                                        <span class="badge bg-label-success">Oui</span>
                                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($livreur->email_verified_at)->format('d/m/Y H:i:s') }}</small>
                                    @else
                                        <span class="badge bg-label-warning">Non</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Créé le:</th>
                                <td>{{ isset($livreur->created_at) ? \Carbon\Carbon::parse($livreur->created_at)->format('d/m/Y H:i:s') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="200">Entreprise:</th>
                                <td>
                                    <a href="{{ route('platform-admin.entreprises.show', $entreprise->id) }}" class="text-decoration-none">
                                        <strong>{{ $entreprise->name }}</strong>
                                    </a>
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
                                <th>Zone d'activité:</th>
                                <td>
                                    @if($zone_activite)
                                        {{ $zone_activite->nom ?? $zone_activite->libelle ?? 'N/A' }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Permis:</th>
                                <td>{{ $livreur->permis ?? 'N/A' }}</td>
                            </tr>
                            @if($livreur->photo)
                                <tr>
                                    <th>Photo:</th>
                                    <td>
                                        @php
                                            // Construire l'URL de la photo avec la variable d'environnement
                                            $photoBaseUrl = env('PHOTO_LIVREUR_ENRTREPRISE_URL', 'http://192.168.1.6:8000/');
                                            // Le champ photo peut contenir: "livreurs/photos/filename.jpg" ou "storage/livreurs/photos/filename.jpg" ou juste "filename.jpg"
                                            $photoPath = $livreur->photo;

                                            // Si la photo contient déjà "storage/", on le retire
                                            if (strpos($photoPath, 'storage/') === 0) {
                                                $photoPath = substr($photoPath, 8); // Retirer "storage/"
                                            }

                                            // Si la photo ne contient pas "livreurs/", on l'ajoute
                                            if (strpos($photoPath, 'livreurs/') === false) {
                                                $photoPath = 'livreurs/photos/' . $photoPath;
                                            }

                                            // Construire l'URL complète
                                            $photoUrl = rtrim($photoBaseUrl, '/') . '/storage/' . $photoPath;
                                        @endphp
                                        <img src="{{ $photoUrl }}" alt="Photo du livreur" class="img-thumbnail" style="max-width: 150px; max-height: 150px; cursor: pointer;" onclick="window.open('{{ $photoUrl }}', '_blank')" onerror="this.src='{{ asset('images/placeholder-image.png') }}'">
                                        <br><small class="text-muted mt-2 d-block">Cliquez sur l'image pour l'agrandir</small>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <th>FCM Token:</th>
                                <td>
                                    @if($livreur->fcm_token)
                                        <code class="small">{{ strlen($livreur->fcm_token) > 50 ? substr($livreur->fcm_token, 0, 50) . '...' : $livreur->fcm_token }}</code>
                                        @if($livreur->fcm_token_updated_at)
                                            <br><small class="text-muted">Mis à jour le: {{ \Carbon\Carbon::parse($livreur->fcm_token_updated_at)->format('d/m/Y H:i:s') }}</small>
                                        @endif
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
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Modifié par:</th>
                                <td>
                                    @if($modificateur)
                                        <strong>{{ $modificateur->first_name ?? '' }} {{ $modificateur->last_name ?? '' }}</strong>
                                        @if($modificateur->email)
                                            <br><small class="text-muted">Email: {{ $modificateur->email }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Modifié le:</th>
                                <td>{{ isset($livreur->updated_at) ? \Carbon\Carbon::parse($livreur->updated_at)->format('d/m/Y H:i:s') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

@include('platform-admin.layouts.footer')

