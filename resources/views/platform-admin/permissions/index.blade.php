@include('platform-admin.layouts.header')
@include('platform-admin.layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Gestion /</span> Permissions
    </h4>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">
                <i class="ti ti-shield-check me-2"></i>Liste des permissions
                <span class="badge bg-label-primary ms-2">{{ count($permissions) }} ressource(s)</span>
            </h5>
        </div>
        <div class="card-body">
            @if(count($permissions) > 0)
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
                                        <span class="badge bg-label-secondary">{{ count($resourcePermissions) }} permission(s)</span>
                                    </div>
                                </button>
                            </h2>
                            <div id="{{ $resourceId }}" class="accordion-collapse collapse {{ $isFirst ? 'show' : '' }}" aria-labelledby="heading{{ $index }}" data-bs-parent="#permissionsAccordion">
                                <div class="accordion-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="30%">Nom</th>
                                                    <th width="20%">Action</th>
                                                    <th width="50%">Description</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($resourcePermissions as $permission)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <i class="ti ti-check-circle text-success me-2"></i>
                                                                <span class="fw-semibold">{{ $permission->name }}</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <code class="bg-label-primary px-2 py-1 rounded">{{ $permission->action }}</code>
                                                        </td>
                                                        <td>
                                                            <span class="text-muted">{{ $permission->description ?? '-' }}</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="ti ti-shield-off text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3 mb-0">Aucune permission trouvée.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@include('platform-admin.layouts.footer')

