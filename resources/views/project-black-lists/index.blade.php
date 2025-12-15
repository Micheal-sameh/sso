@extends('layouts.sideBar')

@section('title', 'Project Black Lists')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-6">
            <div>
                <h1 class="h3 mb-1 fw-semibold">Project Black Lists</h1>
                <p class="text-muted mb-0">Manage users blocked from specific projects</p>
            </div>
            <div>
                <a href="{{ route('project-black-lists.create') }}" class="btn btn-primary d-flex align-items-center">
                    <i class="fas fa-plus me-2"></i>
                    Add to Blacklist
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-6">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-stat border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                                <i class="fas fa-ban fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Entries</h6>
                                <h3 class="fw-bold mb-0">{{ $projectBlackLists->total() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-stat border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-success bg-opacity-10 text-success rounded-circle p-3 me-3">
                                <i class="fas fa-project-diagram fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Projects</h6>
                                <h3 class="fw-bold mb-0">{{ $projects->count() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Filter Card -->
        <div class="card border-0 shadow-sm mb-6">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('project-black-lists.index') }}" class="row g-3 align-items-end">
                    <div class="col-lg-4 col-md-6">
                        <label class="form-label fw-semibold">Search</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0"
                                   placeholder="Project, user, or reason..."
                                   value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-semibold">Project</label>
                        <select name="project_id" class="form-select">
                            <option value="">All Projects</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}"
                                    {{ ($filters['project_id'] ?? '') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" {{ ($filters['status'] ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ ($filters['status'] ?? '') == 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center">
                            <i class="fas fa-filter me-2"></i>
                            Filter
                        </button>
                        @if(request()->hasAny(['search', 'project_id', 'status']))
                            <a href="{{ route('project-black-lists.index') }}"
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
                                <th class="py-3 border-0 fw-semibold text-muted">Project</th>
                                <th class="py-3 border-0 fw-semibold text-muted">User</th>
                                <th class="py-3 border-0 fw-semibold text-muted">Reason</th>
                                <th class="py-3 border-0 fw-semibold text-muted">Created By</th>
                                <th class="py-3 border-0 fw-semibold text-muted">Date</th>
                                <th class="py-3 border-0 fw-semibold text-muted text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($projectBlackLists as $projectBlackList)
                                <tr class="hover-row">
                                    <td class="ps-4">
                                        <div class="form-check">
                                            <input class="form-check-input row-checkbox" type="checkbox"
                                                   value="{{ $projectBlackList->id }}">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                           
                                            <div>
                                                <h6 class="mb-0 fw-semibold">{{ $projectBlackList->project->name }}</h6>
                                                <small class="text-muted">ID: {{ $projectBlackList->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $projectBlackList->user->name }}</h6>
                                                <small class="text-muted">{{ strtoupper($projectBlackList->user->membership_code)  }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="reason-cell" data-bs-toggle="tooltip" title="{{ e($projectBlackList->reason) }}">
                                            {{ \Illuminate\Support\Str::limit($projectBlackList->reason, 80) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($projectBlackList->creator)
                                                <div class="avatar-xs bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <i class="fas fa-user-edit fa-xs"></i>
                                                </div>
                                                <span>{{ $projectBlackList->creator->name }}</span>
                                            @else
                                                <span class="text-muted">System</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold">{{ $projectBlackList->created_at->format('M d, Y') }}</span>
                                            <small class="text-muted">{{ $projectBlackList->created_at->format('h:i A') }}</small>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group btn-group-sm" role="group">

                                            <button type="button"
                                                    class="btn btn-outline-danger d-flex align-items-center btn-delete"
                                                    data-id="{{ $projectBlackList->id }}"
                                                    data-project="{{ $projectBlackList->project->name }}"
                                                    data-user="{{ $projectBlackList->user->name }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Remove">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-ban fa-3x text-muted mb-3"></i>
                                            <h4 class="fw-semibold">No blacklist entries found</h4>
                                            <p class="text-muted mb-4">
                                                @if(request()->hasAny(['search', 'project_id', 'status']))
                                                    Try adjusting your filters
                                                @else
                                                    Start by adding users to the blacklist
                                                @endif
                                            </p>
                                            @if(!request()->hasAny(['search', 'project_id', 'status']))
                                                <a href="{{ route('project-black-lists.create') }}" class="btn btn-primary">
                                                    <i class="fas fa-plus me-2"></i>
                                                    Add First Entry
                                                </a>
                                            @else
                                                <a href="{{ route('project-black-lists.index') }}" class="btn btn-outline-secondary">
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
                @if($projectBlackLists->isNotEmpty())
                    <div class="card-footer bg-light border-0 py-3 px-4 d-none" id="bulkActions">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="text-muted me-3" id="selectedCount">0 items selected</span>
                                <select class="form-select form-select-sm w-auto me-2" id="bulkAction">
                                    <option value="">Choose action...</option>
                                    <option value="delete">Delete selected</option>
                                    <option value="export">Export selected</option>
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
        @if($projectBlackLists->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Showing {{ $projectBlackLists->firstItem() ?? 0 }} to {{ $projectBlackLists->lastItem() ?? 0 }}
                    of {{ $projectBlackLists->total() }} entries
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0">
                        <li class="page-item {{ $projectBlackLists->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $projectBlackLists->previousPageUrl() }}">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>

                        @foreach(range(1, min(5, $projectBlackLists->lastPage())) as $page)
                            @if(abs($page - $projectBlackLists->currentPage()) <= 2 || $page == 1 || $page == $projectBlackLists->lastPage())
                                <li class="page-item {{ $projectBlackLists->currentPage() == $page ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $projectBlackLists->url($page) }}">{{ $page }}</a>
                                </li>
                            @elseif(abs($page - $projectBlackLists->currentPage()) == 3)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                        @endforeach

                        <li class="page-item {{ !$projectBlackLists->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $projectBlackLists->nextPageUrl() }}">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        @endif
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
                    <h4 class="fw-semibold mb-3">Remove from Blacklist</h4>
                    <p class="text-muted mb-4" id="deleteMessage">
                        Are you sure you want to remove this entry? This action cannot be undone.
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
                            Remove
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Delete Confirmation Modal -->
    <div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center px-5">
                    <div class="icon-circle bg-danger bg-opacity-10 text-danger rounded-circle p-4 d-inline-flex mb-4">
                        <i class="fas fa-trash fa-2x"></i>
                    </div>
                    <h4 class="fw-semibold mb-3">Remove Selected Entries</h4>
                    <p class="text-muted mb-2">You are about to remove <strong><span id="bulkDeleteCount">0</span></strong> blacklist entr<span id="bulkDeletePlural">ies</span>.</p>
                    <p class="text-muted small">This action cannot be undone. You can export the selected entries before deleting if needed.</p>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-5">
                    <form method="POST" id="bulkDeleteForm">
                        @csrf
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="confirmBulkDeleteBtn" class="btn btn-danger px-4">
                            <i class="fas fa-trash me-2"></i>
                            Remove Selected
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
    .project-icon {
        width: 44px;
        height: 44px;
    }
    .reason-cell {
        max-width: 300px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        cursor: help;
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
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(el => new bootstrap.Tooltip(el));

        // Delete functionality
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const deleteForm = document.getElementById('deleteForm');
        const deleteMessage = document.getElementById('deleteMessage');

        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const project = this.dataset.project;
                const user = this.dataset.user;

                deleteForm.action = `{{ route('project-black-lists.destroy', '') }}/${id}`;
                deleteMessage.innerHTML = `
                    Are you sure you want to remove <strong>${user}</strong>
                    from the blacklist of <strong>${project}</strong>?
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
                    selectedCount.textContent = `${count} item${count !== 1 ? 's' : ''} selected`;
                } else {
                    bulkActions.classList.add('d-none');
                }
            }

            // Handle confirm bulk delete modal button
            const confirmBulkDeleteBtn = document.getElementById('confirmBulkDeleteBtn');
            if (confirmBulkDeleteBtn) {
                confirmBulkDeleteBtn.addEventListener('click', function() {
                    const ids = JSON.parse(this.dataset.ids || '[]');
                    if (!ids.length) return;

                    this.disabled = true;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Removing';

                    fetch('{{ route("project-black-lists.bulk-delete") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ ids })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            console.error(data);
                            alert('Failed to delete selected items');
                            location.reload();
                        }
                    }).catch(err => {
                        console.error(err);
                        alert('An error occurred while deleting');
                        location.reload();
                    });
                });
            }

            selectAll.indeterminate = count > 0 && count < rowCheckboxes.length;
            selectAll.checked = count === rowCheckboxes.length;
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

                if (action === 'delete') {
                    // Show a centered modal to confirm bulk deletion
                    const bulkDeleteModalEl = document.getElementById('bulkDeleteModal');
                    const bulkDeleteModal = new bootstrap.Modal(bulkDeleteModalEl);
                    document.getElementById('bulkDeleteCount').textContent = selectedIds.length;
                    // pluralize label
                    document.getElementById('bulkDeletePlural').textContent = selectedIds.length === 1 ? 'y' : 'ies';
                    // store ids on confirm button element
                    document.getElementById('confirmBulkDeleteBtn').dataset.ids = JSON.stringify(selectedIds);
                    bulkDeleteModal.show();
                }
            });
        }
    });
</script>
@endpush