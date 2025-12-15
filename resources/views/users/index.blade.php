@extends('layouts.sideBar')


@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1 class="h3 mb-2 text-dark">
                    <i class="fas fa-users me-2"></i>
                    {{ __('messages.users_management') }}
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item active" aria-current="page">{{ __('messages.users') }}</li>
                    </ol>
                </nav>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                {{-- <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus-circle me-2"></i>
                    {{ __('messages.add_user') }}
                </button> --}}

                {{--<div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-download me-2"></i>
                        {{ __('messages.export') }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2"></i> Excel</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf me-2"></i> PDF</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-csv me-2"></i> CSV</a></li>
                    </ul>
                </div> --}}
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="fas fa-users fa-lg text-primary"></i>
                        </div>
                        <div>
                            <h2 class="mb-1 fw-bold">{{ $totalUsers ?? 0 }}</h2>
                            <p class="text-muted mb-0">{{ __('messages.total_users') }}</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="fas fa-user-check fa-lg text-success"></i>
                        </div>
                        <div>
                            <h2 class="mb-1 fw-bold">{{ $activeUsers ?? 0 }}</h2>
                            <p class="text-muted mb-0">{{ __('messages.active_users') }}</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-success bg-opacity-10 text-success">
                            <i class="fas fa-circle me-1 small"></i> {{ __('messages.active') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="fas fa-user-clock fa-lg text-warning"></i>
                        </div>
                        <div>
                            <h2 class="mb-1 fw-bold">{{ $inactiveUsers ?? 0 }}</h2>
                            <p class="text-muted mb-0">{{ __('messages.inactive_users') }}</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-circle me-1 small"></i> {{ __('messages.inactive') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-danger bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="fas fa-user-shield fa-lg text-danger"></i>
                        </div>
                        <div>
                            <h2 class="mb-1 fw-bold"><a href="{{route('sso-users.index')}}">{{ $adminUsers ?? 0 }}</a></h2>
                            <p class="text-muted mb-0">{{ __('messages.admin_users') }}</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-shield-alt me-1"></i> {{ __('messages.administrator') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small fw-semibold text-muted">{{ __('messages.search') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="searchInput"
                               placeholder="{{ __('messages.search_by_name_email') }}">
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label small fw-semibold text-muted">{{ __('messages.status') }}</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">{{ __('messages.all_status') }}</option>
                        <option value="1">{{ __('messages.active') }}</option>
                        <option value="0">{{ __('messages.inactive') }}</option>
                    </select>
                </div>

                {{-- <div class="col-lg-3 col-md-6">
                    <label class="form-label small fw-semibold text-muted">{{ __('messages.date_range') }}</label>
                    <div class="input-group">
                        <input type="date" class="form-control" id="dateFrom">
                        <span class="input-group-text bg-light">to</span>
                        <input type="date" class="form-control" id="dateTo">
                    </div>
                </div> --}}

                <div class="col-lg-2 col-md-12 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button class="btn btn-primary flex-grow-1" id="applyFilters">
                            <i class="fas fa-filter me-2"></i>{{ __('messages.apply') }}
                        </button>
                        <button class="btn btn-outline-secondary" id="resetFilters" title="{{ __('messages.reset_filters') }}">
                            <i class="fas fa-redo"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-semibold">
                    <i class="fas fa-list me-2"></i>{{ __('messages.users_list') }}
                </h5>
                <div class="text-muted small">
                    {{ __('messages.showing') }} <span id="showingCount">10</span> {{ __('messages.of') }}
                    <span id="totalCount">{{ $users->total() ?? 0 }}</span> {{ __('messages.users') }}
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="usersTable">
                    <thead class="table-light">
                        <tr>
                            <th width="50">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </div>
                            </th>
                            <th width="60">{{ __('messages.photo') }}</th>
                            <th>{{ __('messages.name') }}</th>
                            <th>{{ __('messages.email') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th class="text-end" width="150">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr data-user-id="{{ $user->id }}">
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input user-checkbox" type="checkbox" value="{{ $user->id }}">
                                </div>
                            </td>
                            <td>
                                <div class="avatar avatar-sm">
                                    @if($user->profile_photo)
                                    <img src="{{ asset('storage/' . $user->profile_photo) }}"
                                         alt="{{ $user->name }}"
                                         class="rounded-circle"
                                         width="40"
                                         height="40"
                                         loading="lazy">
                                    @else
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $user->name }}</div>
                                <small class="text-muted">ID: {{ $user->membership_code }}</small>
                            </td>
                            <td>
                                <div>{{ $user->email }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $user->status == App\Enums\UserStatus::ACTIVE ? 'bg-success' : 'bg-danger' }}">
                                    {{ $user->status == App\Enums\UserStatus::ACTIVE ? __('messages.active') : __('messages.inactive') }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{route('users.show', $user->id)}}" class="btn btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    {{-- <form id="delete-form-{{ $user->id }}"
                                          action="{{ route('users.destroy', $user->id) }}"
                                          method="POST"
                                          class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form> --}}
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted mb-3">
                                    <i class="fas fa-users fa-3x opacity-25"></i>
                                </div>
                                <h5 class="text-muted">{{ __('messages.no_users_found') }}</h5>
                                <p class="text-muted small">{{ __('messages.add_your_first_user') }}</p>
                                <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                    <i class="fas fa-plus me-2"></i>{{ __('messages.add_user') }}
                                </button>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Bulk Actions -->
            <div class="card-footer bg-light border-0 py-3" id="bulkActions" style="display: none;">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small" id="selectedCount">0 {{ __('messages.selected') }}</div>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm w-auto" id="bulkAction">
                            <option value="">{{ __('messages.bulk_actions') }}</option>
                            <option value="activate">{{ __('messages.activate') }}</option>
                            <option value="deactivate">{{ __('messages.deactivate') }}</option>
                            <option value="delete">{{ __('messages.delete_selected') }}</option>
                        </select>
                        <button class="btn btn-sm btn-primary" id="applyBulkAction">
                            <i class="fas fa-check me-1"></i>{{ __('messages.apply') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
<div class="d-flex justify-content-center pt-2">
    @if ($users->hasPages())
        <nav>
            <ul class="pagination">
                {{-- Previous Page Link --}}
                @if ($users->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $users->previousPageUrl() }}" rel="prev">&laquo;</a>
                    </li>
                @endif

                @php
                    $current = $users->currentPage();
                    $last = $users->lastPage();
                    $start = max($current - 2, 2);
                    $end = min($current + 2, $last - 1);
                @endphp

                {{-- First page --}}
                <li class="page-item {{ $current === 1 ? 'active' : '' }}">
                    <a class="page-link" href="{{ $users->url(1) }}">1</a>
                </li>

                {{-- Dots before start --}}
                @if ($start > 2)
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                @endif

                {{-- Page range --}}
                @for ($page = $start; $page <= $end; $page++)
                    <li class="page-item {{ $current === $page ? 'active' : '' }}">
                        <a class="page-link" href="{{ $users->url($page) }}">{{ $page }}</a>
                    </li>
                @endfor

                {{-- Dots after end --}}
                @if ($end < $last - 1)
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                @endif

                {{-- Last page --}}
                @if ($last > 1)
                    <li class="page-item {{ $current === $last ? 'active' : '' }}">
                        <a class="page-link" href="{{ $users->url($last) }}">{{ $last }}</a>
                    </li>
                @endif

                {{-- Next Page Link --}}
                @if ($users->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $users->nextPageUrl() }}" rel="next">&raquo;</a>
                    </li>
                @else
                    <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                @endif
            </ul>
        </nav>
    @endif
</div>
        @endif
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>{{ __('messages.add_new_user') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Content loaded via AJAX -->
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Content loaded via AJAX -->
        </div>
    </div>
</div>

<style>
    .avatar img {
        object-fit: cover;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(var(--bs-primary-rgb), 0.05);
    }

    .badge {
        padding: 0.35em 0.65em;
        font-weight: 500;
    }

    .form-check-input:checked {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
    }

    .page-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 1.5rem;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .card {
        border-radius: 0.75rem;
        transition: transform 0.2s;
    }

    .card:hover {
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        .page-header {
            padding: 1rem;
        }

        .card-body {
            padding: 1rem !important;
        }

        .btn-group .btn {
            padding: 0.25rem 0.5rem;
        }
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(tooltip => new bootstrap.Tooltip(tooltip));

        // Password toggle
        const togglePassword = document.getElementById('togglePassword');
        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                const passwordInput = document.getElementById('password');
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });
        }

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            let searchTimer;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    performSearch(this.value);
                }, 500);
            });
        }

        // Select All checkbox
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.user-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkActions();
            });
        }

        // Individual checkbox changes
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('user-checkbox')) {
                updateBulkActions();
            }
        });

        // Bulk actions
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');

        function updateBulkActions() {
            const selected = document.querySelectorAll('.user-checkbox:checked');
            if (selected.length > 0) {
                bulkActions.style.display = 'block';
                selectedCount.textContent = selected.length + ' {{ __("messages.selected") }}';
                document.getElementById('selectAll').checked =
                    selected.length === document.querySelectorAll('.user-checkbox').length;
            } else {
                bulkActions.style.display = 'none';
            }
        }

        // Apply bulk action
        const applyBulkAction = document.getElementById('applyBulkAction');
        if (applyBulkAction) {
            applyBulkAction.addEventListener('click', function() {
                const action = document.getElementById('bulkAction').value;
                const selectedIds = Array.from(document.querySelectorAll('.user-checkbox:checked'))
                    .map(checkbox => checkbox.value);

                if (!action || selectedIds.length === 0) return;

                if (action === 'delete') {
                    if (!confirm('{{ __("messages.confirm_delete_selected") }}')) return;
                }

                // Send bulk action request
                fetch('{{ route("users.bulk") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        action: action,
                        ids: selectedIds
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        }

        // Filter functionality
        const applyFilters = document.getElementById('applyFilters');
        if (applyFilters) {
            applyFilters.addEventListener('click', function() {
                applyServerFilters();
            });
        }

        // Reset filters
        const resetFilters = document.getElementById('resetFilters');
        if (resetFilters) {
            resetFilters.addEventListener('click', function() {
                document.getElementById('searchInput').value = '';
                document.getElementById('statusFilter').value = '';
                document.getElementById('roleFilter').value = '';
                document.getElementById('dateFrom').value = '';
                document.getElementById('dateTo').value = '';
                applyServerFilters();
            });
        }

        // Status filter change
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                applyServerFilters();
            });
        }
    });

    function performSearch(query) {
        const rows = document.querySelectorAll('#usersTable tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query.toLowerCase()) ? '' : 'none';
        });

        // Update showing count
        const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
        document.getElementById('showingCount').textContent = visibleRows.length;
    }

    function applyServerFilters() {
        const search = document.getElementById('searchInput').value;
        const status = document.getElementById('statusFilter').value;

        // Build query string
        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (status) params.append('status', status);

        // Redirect to the filtered URL
        const url = '{{ route("users.index") }}' + (params.toString() ? '?' + params.toString() : '');
        window.location.href = url;
    }

    function viewUser(userId) {
        fetch(`/users/${userId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('viewUserModal').querySelector('.modal-content').innerHTML = html;
                const modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
                modal.show();
            })
            .catch(error => console.error('Error:', error));
    }

    function editUser(userId) {
        fetch(`/users/${userId}/edit`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('editUserModal').querySelector('.modal-content').innerHTML = html;
                const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
                modal.show();
            })
            .catch(error => console.error('Error:', error));
    }

    // Auto-dismiss alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
</script>
@endpush
@endsection