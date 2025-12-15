@extends('layouts.sideBar')

@section('title', 'SSO Users Management')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="h3 mb-1 fw-semibold">SSO Users</h1>
            <p class="text-muted mb-0">Manage Single Sign-On user connections and associations</p>
        </div>

    </div>

    <!-- Stats Cards -->
    <div class="row mb-6">
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card card-stat border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                            <i class="fas fa-users fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Total SSO Users</h6>
                            <h3 class="fw-bold mb-0">{{ $ssoUsers->total() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-6">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('sso-users.index') }}" class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label fw-semibold">Search Users</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0"
                               placeholder="Name, email, or provider..."
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold">Provider</label>
                    <select name="provider" class="form-select">
                        <option value="">All Providers</option>
                        <option value="google" {{ request('provider') == 'google' ? 'selected' : '' }}>Google</option>
                        <option value="microsoft" {{ request('provider') == 'microsoft' ? 'selected' : '' }}>Microsoft</option>
                        <option value="github" {{ request('provider') == 'github' ? 'selected' : '' }}>GitHub</option>
                        <option value="apple" {{ request('provider') == 'apple' ? 'selected' : '' }}>Apple</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold">Date Range</label>
                    <input type="date" name="date_from" class="form-control"
                           value="{{ request('date_from') }}" placeholder="From">
                </div>
                <div class="col-lg-2 col-md-6">
                    <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center">
                        <i class="fas fa-filter me-2"></i>
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'provider', 'date_from']))
                        <a href="{{ route('sso-users.index') }}"
                           class="btn btn-outline-secondary w-100 mt-2 d-flex align-items-center justify-content-center">
                            <i class="fas fa-times me-2"></i>
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 border-0">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </div>
                            </th>
                            <th class="py-3 border-0 fw-semibold text-muted">User</th>
                            <th class="py-3 border-0 fw-semibold text-muted">SSO Provider</th>
                            <th class="py-3 border-0 fw-semibold text-muted">Created By</th>
                            <th class="py-3 border-0 fw-semibold text-muted">Date Added</th>
                            <th class="py-3 border-0 fw-semibold text-muted text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ssoUsers as $ssoUser)
                            <tr class="hover-row">
                                <td class="ps-4">
                                    <div class="form-check">
                                        <input class="form-check-input row-checkbox" type="checkbox"
                                               value="{{ $ssoUser->id }}">
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">
                                                <a href="{{ route('users.show', $ssoUser->user_id) }}" class="text-decoration-none">
                                                    {{ $ssoUser->user->name }}
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-envelope me-1"></i>
                                                {{ $ssoUser->user->email ?? 'No email' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $providerColors = [
                                            'google' => 'bg-danger bg-opacity-10 text-danger',
                                            'microsoft' => 'bg-success bg-opacity-10 text-success',
                                            'github' => 'bg-dark bg-opacity-10 text-dark',
                                            'apple' => 'bg-secondary bg-opacity-10 text-secondary',
                                        ];
                                        $providerColor = $providerColors[$ssoUser->provider] ?? 'bg-info bg-opacity-10 text-info';
                                        $providerIcons = [
                                            'google' => 'fab fa-google',
                                            'microsoft' => 'fab fa-microsoft',
                                            'github' => 'fab fa-github',
                                            'apple' => 'fab fa-apple',
                                        ];
                                        $providerIcon = $providerIcons[$ssoUser->provider] ?? 'fas fa-key';
                                    @endphp
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs {{ $providerColor }} rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <i class="{{ $providerIcon }} fa-xs"></i>
                                        </div>
                                        <div>
                                            <span class="fw-semibold text-capitalize">{{ $ssoUser->provider }}</span>
                                            <small class="d-block text-muted">SSO ID: {{ $ssoUser->sso_id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($ssoUser->createdBy)
                                            <div class="avatar-xs bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class="fas fa-user-edit fa-xs"></i>
                                            </div>
                                            <div>
                                                <a href="{{ route('users.show', $ssoUser->created_by) }}" class="text-decoration-none">
                                                    {{ $ssoUser->createdBy->name }}
                                                </a>
                                                <small class="d-block text-muted">
                                                    @if($ssoUser->created_by === $ssoUser->user_id)
                                                        <span class="badge bg-primary bg-opacity-10 text-primary">Self-registered</span>
                                                    @else
                                                        Admin
                                                    @endif
                                                </small>
                                            </div>
                                        @else
                                            <span class="text-muted">System</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold">{{ $ssoUser->created_at->format('M d, Y') }}</span>
                                        <small class="text-muted">{{ $ssoUser->created_at->format('h:i A') }}</small>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $ssoUser->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm" role="group">
                                        
                                        <button type="button"
                                                class="btn btn-outline-danger d-flex align-items-center btn-delete"
                                                data-id="{{ $ssoUser->id }}"
                                                data-user="{{ $ssoUser->user->name }}"
                                                data-provider="{{ $ssoUser->provider }}"
                                                data-bs-toggle="tooltip"
                                                title="Remove SSO Connection">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fas fa-key fa-3x text-muted mb-3"></i>
                                        <h4 class="fw-semibold">No SSO Users Found</h4>
                                        <p class="text-muted mb-4">
                                            @if(request()->hasAny(['search', 'provider', 'date_from']))
                                                Try adjusting your filters
                                            @else
                                                No single sign-on users have been configured yet
                                            @endif
                                        </p>
                                        @if(!request()->hasAny(['search', 'provider', 'date_from']))
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSsoUserModal">
                                                <i class="fas fa-plus me-2"></i>
                                                Add First SSO User
                                            </button>
                                        @else
                                            <a href="{{ route('sso-users.index') }}" class="btn btn-outline-secondary">
                                                Clear Filters
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Bulk Actions -->
            @if($ssoUsers->isNotEmpty())
                <div class="card-footer bg-light border-0 py-3 px-4 d-none" id="bulkActions">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <span class="text-muted me-3" id="selectedCount">0 items selected</span>
                            <select class="form-select form-select-sm w-auto me-2" id="bulkAction">
                                <option value="">Choose action...</option>
                                <option value="delete">Remove selected connections</option>
                                <option value="export">Export selected</option>
                                <option value="disable">Temporarily disable</option>
                            </select>
                            <button type="button" class="btn btn-sm btn-primary" id="applyBulkAction">Apply</button>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSelection">
                            Clear selection
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Pagination -->
    @if($ssoUsers->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing {{ $ssoUsers->firstItem() ?? 0 }} to {{ $ssoUsers->lastItem() ?? 0 }}
                of {{ $ssoUsers->total() }} entries
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination mb-0">
                    <li class="page-item {{ $ssoUsers->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $ssoUsers->previousPageUrl() }}">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>

                    @foreach(range(1, min(5, $ssoUsers->lastPage())) as $page)
                        @if(abs($page - $ssoUsers->currentPage()) <= 2 || $page == 1 || $page == $ssoUsers->lastPage())
                            <li class="page-item {{ $ssoUsers->currentPage() == $page ? 'active' : '' }}">
                                <a class="page-link" href="{{ $ssoUsers->url($page) }}">{{ $page }}</a>
                            </li>
                        @elseif(abs($page - $ssoUsers->currentPage()) == 3)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                    @endforeach

                    <li class="page-item {{ !$ssoUsers->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $ssoUsers->nextPageUrl() }}">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    @endif
</div>

{{-- <!-- Add SSO User Modal -->
<div class="modal fade" id="addSsoUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold">Add SSO User Connection</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('sso-users.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Select User</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">Choose a user...</option>
                            @foreach($allUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">SSO Provider</label>
                        <select name="provider" class="form-select" required>
                            <option value="">Select provider...</option>
                            <option value="google">Google</option>
                            <option value="microsoft">Microsoft</option>
                            <option value="github">GitHub</option>
                            <option value="apple">Apple</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">SSO ID</label>
                        <input type="text" name="sso_id" class="form-control"
                               placeholder="Enter the SSO identifier" required>
                        <small class="text-muted">This is the unique identifier from the SSO provider</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Connection</button>
                </div>
            </form>
        </div>
    </div>
</div> --}}

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold">SSO User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6 class="text-muted mb-3">User Information</h6>
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-lg bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="fas fa-user fa-xl"></i>
                        </div>
                        <div>
                            <h5 class="fw-semibold mb-1" id="detailUserName"></h5>
                            <p class="text-muted mb-0" id="detailUserEmail"></p>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">SSO Information</h6>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-key text-primary me-3"></i>
                            <div>
                                <p class="mb-0 fw-semibold" id="detailProvider"></p>
                                <small class="text-muted">Provider</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-id-card text-primary me-3"></i>
                            <div>
                                <p class="mb-0 fw-semibold" id="detailSsoId"></p>
                                <small class="text-muted">SSO ID</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Connection Info</h6>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-calendar-plus text-primary me-3"></i>
                            <div>
                                <p class="mb-0 fw-semibold" id="detailCreatedAt"></p>
                                <small class="text-muted">Created On</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-edit text-primary me-3"></i>
                            <div>
                                <p class="mb-0 fw-semibold" id="detailCreatedBy"></p>
                                <small class="text-muted">Created By</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info bg-info bg-opacity-10 border-info border-opacity-25">
                    <div class="d-flex">
                        <i class="fas fa-info-circle fa-lg text-info me-3"></i>
                        <div>
                            <h6 class="fw-semibold mb-1">About SSO Connections</h6>
                            <p class="mb-0 small">This connection allows the user to authenticate using their {{ ucfirst($ssoUser->provider ?? 'external') }} account. Removing this connection will require the user to use password authentication.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center px-5">
                <div class="icon-circle bg-danger bg-opacity-10 text-danger rounded-circle p-4 d-inline-flex mb-4">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
                <h4 class="fw-semibold mb-3">Remove SSO Connection</h4>
                <p class="text-muted mb-4" id="deleteMessage">
                    Are you sure you want to remove this SSO connection?
                </p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-5">
                <form method="POST" id="deleteForm">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="fas fa-trash me-2"></i>
                        Remove Connection
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card-stat {
        transition: transform 0.2s;
    }
    .card-stat:hover {
        transform: translateY(-2px);
    }
    .icon-circle {
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .hover-row:hover {
        background-color: rgba(var(--bs-primary-rgb), 0.02);
    }
    .avatar-sm {
        width: 40px;
        height: 40px;
    }
    .avatar-xs {
        width: 24px;
        height: 24px;
    }
    .avatar-lg {
        width: 64px;
        height: 64px;
    }
    .empty-state {
        padding: 3rem 0;
    }
    .form-check-input:checked {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
    }
    .btn-group .btn {
        border-radius: 6px !important;
        margin: 0 2px;
    }
    .badge {
        padding: 0.35em 0.65em;
        font-size: 0.75em;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(el => new bootstrap.Tooltip(el));

        // View Details functionality
        const detailsModal = new bootstrap.Modal(document.getElementById('detailsModal'));
        document.querySelectorAll('.btn-view-details').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('detailUserName').textContent = this.dataset.userName;
                document.getElementById('detailUserEmail').textContent = this.dataset.userEmail;
                document.getElementById('detailProvider').textContent = this.dataset.provider;
                document.getElementById('detailSsoId').textContent = this.dataset.ssoId;
                document.getElementById('detailCreatedAt').textContent = this.dataset.createdAt;
                document.getElementById('detailCreatedBy').textContent = this.dataset.createdBy;
                detailsModal.show();
            });
        });

        // Delete functionality
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const deleteForm = document.getElementById('deleteForm');
        const deleteMessage = document.getElementById('deleteMessage');

        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const userName = this.dataset.user;
                const provider = this.dataset.provider;

                deleteForm.action = `{{ route('sso-users.destroy', '') }}/${id}`;
                deleteMessage.innerHTML = `
                    Are you sure you want to remove the <strong>${provider}</strong>
                    SSO connection for <strong>${userName}</strong>?
                    This action cannot be undone.
                `;

                deleteModal.show();
            });
        });

        // Bulk actions
        const selectAll = document.getElementById('selectAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');
        const clearSelection = document.getElementById('clearSelection');
        const bulkAction = document.getElementById('bulkAction');
        const applyBulkAction = document.getElementById('applyBulkAction');

        function updateBulkActions() {
            const selected = document.querySelectorAll('.row-checkbox:checked');
            const count = selected.length;

            if (bulkActions) {
                if (count > 0) {
                    bulkActions.classList.remove('d-none');
                    selectedCount.textContent = `${count} connection${count !== 1 ? 's' : ''} selected`;
                } else {
                    bulkActions.classList.add('d-none');
                }
            }

            if (selectAll) {
                selectAll.indeterminate = count > 0 && count < rowCheckboxes.length;
                selectAll.checked = count === rowCheckboxes.length;
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                const isChecked = this.checked;
                rowCheckboxes.forEach(cb => cb.checked = isChecked);
                updateBulkActions();
            });
        }

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkActions);
        });

        if (clearSelection) {
            clearSelection.addEventListener('click', function() {
                rowCheckboxes.forEach(cb => cb.checked = false);
                if (selectAll) selectAll.checked = false;
                updateBulkActions();
            });
        }

        if (applyBulkAction && bulkAction) {
            applyBulkAction.addEventListener('click', function() {
                const action = bulkAction.value;
                if (!action) return;

                const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked'))
                    .map(cb => cb.value);

                if (selectedIds.length === 0) return;


            });
        }
    });
</script>
@endpush