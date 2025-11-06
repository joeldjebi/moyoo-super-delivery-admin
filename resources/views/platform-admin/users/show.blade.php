@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">
            Gestion /
            <a href="{{ route('platform-admin.users.index') }}" class="text-muted text-decoration-none">Utilisateurs</a> /
        </span> Détails de l'utilisateur
    </h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(!$user)
        <div class="alert alert-danger">
            Utilisateur non trouvé.
        </div>
    @else
        <!-- Informations principales -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Informations de l'utilisateur</h5>
                <div>
                    <a href="{{ route('platform-admin.users.index') }}" class="btn btn-secondary btn-sm">
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
                                <td><strong>#{{ $user->id }}</strong></td>
                            </tr>
                            <tr>
                                <th>Prénom:</th>
                                <td><strong>{{ $user->first_name ?? 'N/A' }}</strong></td>
                            </tr>
                            <tr>
                                <th>Nom:</th>
                                <td><strong>{{ $user->last_name ?? 'N/A' }}</strong></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td>{{ $user->email ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Téléphone:</th>
                                <td>{{ $user->mobile ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Statut:</th>
                                <td>
                                    @if($user->status == 'active')
                                        <span class="badge bg-label-success">Actif</span>
                                    @elseif($user->status == 'inactive')
                                        <span class="badge bg-label-secondary">Inactif</span>
                                    @elseif($user->status == 'suspended')
                                        <span class="badge bg-label-warning">Suspendu</span>
                                    @else
                                        <span class="badge bg-label-secondary">{{ $user->status ?? 'N/A' }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Rôle:</th>
                                <td>{{ $user->role ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Type d'utilisateur:</th>
                                <td>
                                    @if($user->user_type == 'super_admin')
                                        <span class="badge bg-label-danger">Super Admin</span>
                                    @elseif($user->user_type == 'entreprise_admin')
                                        <span class="badge bg-label-primary">Admin Entreprise</span>
                                    @elseif($user->user_type == 'entreprise_user')
                                        <span class="badge bg-label-info">Utilisateur Entreprise</span>
                                    @else
                                        <span class="badge bg-label-secondary">{{ $user->user_type ?? 'N/A' }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Email vérifié:</th>
                                <td>
                                    @if(isset($user->email_verified_at) && $user->email_verified_at)
                                        <span class="badge bg-label-success">Oui</span>
                                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($user->email_verified_at)->format('d/m/Y H:i:s') }}</small>
                                    @else
                                        <span class="badge bg-label-warning">Non</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Créé par:</th>
                                <td>
                                    @if(isset($createur) && $createur)
                                        <strong>{{ $createur->first_name ?? '' }} {{ $createur->last_name ?? '' }}</strong>
                                        @if(isset($createur->email) && $createur->email)
                                            <br><small class="text-muted">Email: {{ $createur->email }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Créé le:</th>
                                <td>{{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('d/m/Y H:i:s') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="200">Entreprise:</th>
                                <td>
                                    @if($user->entreprise_name)
                                        <a href="{{ route('platform-admin.entreprises.show', $user->entreprise_id) }}" class="text-decoration-none">
                                            <strong>{{ $user->entreprise_name }}</strong>
                                        </a>
                                        @if(isset($user->entreprise_statut) && $user->entreprise_statut != 1)
                                            <br><span class="badge bg-label-warning">Entreprise inactive</span>
                                        @endif
                                        @if($user->entreprise_email)
                                            <br><small class="text-muted">Email: {{ $user->entreprise_email }}</small>
                                        @endif
                                        @if($user->entreprise_mobile)
                                            <br><small class="text-muted">Tel: {{ $user->entreprise_mobile }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">Aucune entreprise</span>
                                    @endif
                                </td>
                            </tr>
                            @if(isset($user->fcm_token) && $user->fcm_token)
                                <tr>
                                    <th>FCM Token:</th>
                                    <td>
                                        <code class="small">{{ strlen($user->fcm_token) > 50 ? substr($user->fcm_token, 0, 50) . '...' : $user->fcm_token }}</code>
                                        <button type="button" class="btn btn-sm btn-link p-0 ms-2" onclick="copyToClipboard('{{ addslashes($user->fcm_token) }}')" title="Copier">
                                            <i class="ti ti-copy"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <th>Modifié le:</th>
                                <td>{{ $user->updated_at ? \Carbon\Carbon::parse($user->updated_at)->format('d/m/Y H:i:s') : 'N/A' }}</td>
                            </tr>
                            @if($user->deleted_at)
                                <tr>
                                    <th>Supprimé le:</th>
                                    <td><span class="text-danger">{{ \Carbon\Carbon::parse($user->deleted_at)->format('d/m/Y H:i:s') }}</span></td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations d'abonnement -->
        @if((isset($user->subscription_plan_id) && $user->subscription_plan_id) || (isset($user->current_pricing_plan_id) && $user->current_pricing_plan_id) || (isset($user->subscription_started_at) && $user->subscription_started_at) || (isset($user->subscription_expires_at) && $user->subscription_expires_at))
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informations d'abonnement</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                @if($subscription_plan)
                                    <tr>
                                        <th width="200">Plan d'abonnement:</th>
                                        <td>
                                            <strong>{{ $subscription_plan->name ?? 'N/A' }}</strong>
                                            @if($subscription_plan->price)
                                                <br><small class="text-muted">Prix: {{ number_format($subscription_plan->price, 0) }} {{ $subscription_plan->currency ?? 'FCFA' }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                                @if($pricing_plan)
                                    <tr>
                                        <th>Plan tarifaire actuel:</th>
                                        <td>
                                            <strong>{{ $pricing_plan->name ?? 'N/A' }}</strong>
                                            @if($pricing_plan->price)
                                                <br><small class="text-muted">Prix: {{ number_format($pricing_plan->price, 0) }} {{ $pricing_plan->currency ?? 'FCFA' }}</small>
                                            @endif
                                            @if($pricing_plan->period)
                                                <br><small class="text-muted">Période: {{ $pricing_plan->period == 'month' ? 'Mensuel' : 'Annuel' }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Statut abonnement:</th>
                                    <td>
                                        @if(isset($user->subscription_status) && $user->subscription_status == 'active')
                                            <span class="badge bg-label-success">Actif</span>
                                        @elseif(isset($user->subscription_status) && $user->subscription_status == 'expired')
                                            <span class="badge bg-label-danger">Expiré</span>
                                        @elseif(isset($user->subscription_status) && $user->subscription_status == 'cancelled')
                                            <span class="badge bg-label-warning">Annulé</span>
                                        @elseif(isset($user->subscription_status) && $user->subscription_status == 'pending')
                                            <span class="badge bg-label-info">En attente</span>
                                        @else
                                            <span class="badge bg-label-secondary">{{ $user->subscription_status ?? 'N/A' }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Essai:</th>
                                    <td>
                                        @if(isset($user->is_trial) && $user->is_trial)
                                            <span class="badge bg-label-info">Oui</span>
                                            @if(isset($user->trial_expires_at) && $user->trial_expires_at)
                                                <br><small class="text-muted">Expire le: {{ \Carbon\Carbon::parse($user->trial_expires_at)->format('d/m/Y H:i') }}</small>
                                            @endif
                                        @else
                                            <span class="badge bg-label-secondary">Non</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                @if(isset($user->subscription_started_at) && $user->subscription_started_at)
                                    <tr>
                                        <th width="200">Date début abonnement:</th>
                                        <td>{{ \Carbon\Carbon::parse($user->subscription_started_at)->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endif
                                @if(isset($user->subscription_expires_at) && $user->subscription_expires_at)
                                    <tr>
                                        <th>Date expiration abonnement:</th>
                                        <td>
                                            {{ \Carbon\Carbon::parse($user->subscription_expires_at)->format('d/m/Y H:i') }}
                                            @if(\Carbon\Carbon::parse($user->subscription_expires_at)->isPast())
                                                <br><span class="badge bg-label-danger">Expiré</span>
                                            @elseif(\Carbon\Carbon::parse($user->subscription_expires_at)->diffInDays(now()) <= 7)
                                                <br><span class="badge bg-label-warning">Expire bientôt</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Permissions -->
        @if(!empty($permissions))
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Permissions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($permissions as $key => $value)
                            <div class="col-md-6 mb-2">
                                <strong>{{ $key }}:</strong>
                                @if(is_bool($value))
                                    <span class="badge {{ $value ? 'bg-label-success' : 'bg-label-secondary' }}">
                                        {{ $value ? 'Oui' : 'Non' }}
                                    </span>
                                @elseif(is_array($value))
                                    <code>{{ json_encode($value, JSON_PRETTY_PRINT) }}</code>
                                @else
                                    {{ $value }}
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

    @endif

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Token copié dans le presse-papiers');
    }, function(err) {
        console.error('Erreur lors de la copie:', err);
    });
}
</script>

@include('platform-admin.layouts.footer')

