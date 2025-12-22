@extends('layouts.sideBar')

@section('title', 'Groups')

@section('content')
    <div class="container-fluid px-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-6">
            <div>
                <h1 class="h3 mb-1 fw-semibold">Groups</h1>
                <p class="text-muted mb-0">Manage and monitor all registered groups</p>
            </div>
            <div>
                <a href="{{ route('groups.create') }}" class="btn btn-primary d-flex align-items-center">
                    <i class="fas fa-plus me-2"></i>
                    Add Group
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
                                <i class="fas fa-users fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Groups</h6>
                                <h3 class="fw-bold mb-0">{{ $groups->total() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card border-0 shadow-sm mb-6">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('groups.index') }}" class="row g-3 align-items-end">
                    <div class="col-lg-8 col-md-9">
                        <label class="form-label fw-semibold">Search Groups</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0"
                                placeholder="Group name or abbreviation..."
                                value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-3">
                        <button type="submit"
                            class="btn btn-primary w-100 d-flex align-items-center justify-content-center">
                            <i class="fas fa-filter me-2"></i>
                            Filter
                        </button>
                        @if (request()->has('search'))
                            <a href="{{ route('groups.index') }}"
                                class="btn btn-outline-secondary w-100 mt-2 d-flex align-items-center justify-content-center">
                                <i class="fas fa-times me-2"></i>
                                Clear
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Groups Grid -->
        <div class="row" id="groupsGrid">
            @forelse($groups as $group)
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card border-0 shadow-sm h-100 group-card" data-group-id="{{ $group->id }}"
                        data-group-name="{{ $group->name }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div
                                            class="group-icon bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-semibold mb-0">{{ $group->name }}</h5>
                                            <small class="text-muted">{{ $group->abbreviation }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary border-0" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('groups.show', $group->id) }}">
                                                <i class="fas fa-eye me-2"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('groups.edit', $group->id) }}">
                                                <i class="fas fa-edit me-2"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <button class="dropdown-item text-danger btn-delete"
                                                data-id="{{ $group->id }}" data-name="{{ $group->name }}">
                                                <i class="fas fa-trash me-2"></i>
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="group-info mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar text-muted me-2"></i>
                                    <span class="text-muted">Created:</span>
                                    <span class="ms-2 fw-medium">
                                        {{ $group->created_at->format('M d, Y') }}
                                    </span>
                                    <span class="badge bg-light text-muted ms-2">
                                        {{ $group->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div
                            class="card-footer bg-transparent border-top d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                Last updated {{ $group->updated_at?->diffForHumans() }}
                            </small>
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-check-circle me-1"></i>
                                Active
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <div class="empty-state">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h4 class="fw-semibold">No Groups Found</h4>
                                <p class="text-muted mb-4">
                                    @if (request()->has('search'))
                                        Try adjusting your search
                                    @else
                                        Get started by creating your first group
                                    @endif
                                </p>
                                @if (!request()->has('search'))
                                    <a href="{{ route('groups.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>
                                        Create First Group
                                    </a>
                                @else
                                    <a href="{{ route('groups.index') }}" class="btn btn-outline-secondary">
                                        Clear Search
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($groups->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-6">
                <div class="text-muted">
                    Showing {{ $groups->firstItem() ?? 0 }} to {{ $groups->lastItem() ?? 0 }}
                    of {{ $groups->total() }} groups
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0">
                        <li class="page-item {{ $groups->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $groups->previousPageUrl() }}">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>

                        @foreach (range(1, min(5, $groups->lastPage())) as $page)
                            @if (abs($page - $groups->currentPage()) <= 2 || $page == 1 || $page == $groups->lastPage())
                                <li class="page-item {{ $groups->currentPage() == $page ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $groups->url($page) }}">{{ $page }}</a>
                                </li>
                            @elseif(abs($page - $groups->currentPage()) == 3)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                        @endforeach

                        <li class="page-item {{ !$groups->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $groups->nextPageUrl() }}">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        @endif
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

        .group-card {
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .group-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
            border-color: var(--bs-primary);
        }

        .group-icon {
            width: 44px;
            height: 44px;
        }

        .empty-state {
            padding: 3rem 0;
        }

        .dropdown-menu {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Delete group functionality
            document.querySelectorAll('.btn-delete').forEach(button => {
                button.addEventListener('click', function() {
                    const groupId = this.dataset.id;
                    const groupName = this.dataset.name;

                    if (confirm(
                            `Are you sure you want to delete group "${groupName}"? This action cannot be undone.`
                            )) {
                        // Submit delete form via AJAX
                        fetch(`/groups/${groupId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            }
                        }).then(response => {
                            if (response.ok) {
                                location.reload();
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
