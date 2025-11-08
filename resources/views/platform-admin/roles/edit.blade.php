@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Gestion /</span> <a href="{{ route('platform-admin.roles.index') }}" class="text-decoration-none">Rôles</a> / Modifier
        </h4>
        <div>
            <a href="{{ route('platform-admin.roles.index') }}" class="btn btn-label-secondary">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-edit me-2"></i>Modifier le rôle
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('platform-admin.roles.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    <i class="ti ti-user-circle me-1 text-primary"></i>Nom du rôle <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $role->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="slug" class="form-label">
                                    <i class="ti ti-code me-1 text-primary"></i>Slug <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $role->slug) }}" required>
                                <small class="text-muted">Minuscules, tirets autorisés (ex: manager, employee)</small>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">
                                    <i class="ti ti-file-text me-1 text-primary"></i>Description
                                </label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $role->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-4">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_system_role" name="is_system_role" value="1" {{ old('is_system_role', $role->is_system_role) ? 'checked' : '' }} {{ $role->is_system_role ? 'disabled' : '' }}>
                                            <label class="form-check-label" for="is_system_role">
                                                <i class="ti ti-lock me-1"></i>Rôle système
                                            </label>
                                            @if($role->is_system_role)
                                                <small class="text-muted d-block mt-1">Ce rôle système ne peut pas être modifié</small>
                                            @else
                                                <small class="text-muted d-block mt-1">Les rôles système ne peuvent pas être supprimés</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Permissions -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold mb-3">
                                <i class="ti ti-shield-check me-1 text-primary"></i>Permissions
                            </label>
                            <div class="accordion" id="permissionsAccordion">
                                @foreach($permissions as $resource => $resourcePermissions)
                                    @php
                                        $index = $loop->index;
                                        $resourceId = 'resource_' . $index;
                                        $isFirst = $loop->first;
                                    @endphp
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading{{ $index }}">
                                            <button class="accordion-button {{ $isFirst ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $resourceId }}" aria-expanded="{{ $isFirst ? 'true' : 'false' }}" aria-controls="{{ $resourceId }}">
                                                <div class="d-flex align-items-center w-100">
                                                    <i class="ti ti-folder me-2 text-primary"></i>
                                                    <span class="text-capitalize fw-semibold me-2">{{ str_replace('_', ' ', $resource) }}</span>
                                                    <span class="badge bg-label-secondary">{{ $resourcePermissions->count() }} permission(s)</span>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="{{ $resourceId }}" class="accordion-collapse collapse {{ $isFirst ? 'show' : '' }}" aria-labelledby="heading{{ $index }}" data-bs-parent="#permissionsAccordion">
                                            <div class="accordion-body">
                                                @foreach($resourcePermissions as $permission)
                                                    <div class="form-check mb-3">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission_{{ $permission->id }}" {{ $role->permissions->contains($permission->id) || in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                            <div class="d-flex align-items-start">
                                                                <i class="ti ti-check-circle text-success me-2 mt-1"></i>
                                                                <div class="flex-grow-1">
                                                                    <span class="fw-semibold d-block">{{ $permission->name }}</span>
                                                                    <small class="text-muted">
                                                                        <code class="bg-label-primary px-2 py-1 rounded">{{ $permission->action }}</code>
                                                                    </small>
                                                                    @if($permission->description)
                                                                        <p class="text-muted small mb-0 mt-1">{{ $permission->description }}</p>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>
                                                    @if(!$loop->last)
                                                        <hr class="my-2">
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('permissions')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('platform-admin.roles.index') }}" class="btn btn-label-secondary">
                                <i class="ti ti-x me-1"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-info-circle me-2"></i>Informations
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Date de création</label>
                        <div class="d-flex align-items-center">
                            <i class="ti ti-calendar me-2 text-primary"></i>
                            <span>{{ $role->created_at->format('d/m/Y à H:i') }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Dernière modification</label>
                        <div class="d-flex align-items-center">
                            <i class="ti ti-clock me-2 text-primary"></i>
                            <span>{{ $role->updated_at->format('d/m/Y à H:i') }}</span>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-0">
                        <label class="form-label text-muted small">Nombre d'administrateurs</label>
                        <div class="d-flex align-items-center">
                            <i class="ti ti-users me-2 text-primary"></i>
                            <span class="fw-semibold">{{ $role->admins->count() }} administrateur(s)</span>
                        </div>
                    </div>
                    <div class="mb-0 mt-3">
                        <label class="form-label text-muted small">Nombre de permissions</label>
                        <div class="d-flex align-items-center">
                            <i class="ti ti-shield-check me-2 text-primary"></i>
                            <span class="fw-semibold">{{ $role->permissions->count() }} permission(s)</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($role->is_system_role)
                <div class="alert alert-warning">
                    <i class="ti ti-alert-triangle me-2"></i>
                    <strong>Rôle système :</strong> Ce rôle ne peut pas être supprimé.
                </div>
            @endif
        </div>
    </div>

@include('platform-admin.layouts.footer')
