@extends('layouts.sideBar')

@section('title', 'Projects')

@section('content')
    <div class="container-fluid px-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-6">
            <div>
                <h1 class="h3 mb-1 fw-semibold">Projects</h1>
                <p class="text-muted mb-0">Manage and monitor all registered projects</p>
            </div>
            <div>
                <a href="{{ route('projects.create') }}" class="btn btn-primary d-flex align-items-center">
                    <i class="fas fa-plus me-2"></i>
                    Add Project
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
                                <i class="fas fa-project-diagram fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Projects</h6>
                                <h3 class="fw-bold mb-0">{{ $projects->total() }}</h3>
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
                                <i class="fas fa-calendar-alt fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">This Month</h6>
                                <h3 class="fw-bold mb-0">{{ $thisMonthCount ?? '0' }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-stat border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-warning bg-opacity-10 text-warning rounded-circle p-3 me-3">
                                <i class="fas fa-users fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Active Creators</h6>
                                <h3 class="fw-bold mb-0">{{ $uniqueCreatorsCount ?? '0' }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-stat border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-info bg-opacity-10 text-info rounded-circle p-3 me-3">
                                <i class="fas fa-key fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">API Keys</h6>
                                <h3 class="fw-bold mb-0">{{ $projects->total() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card border-0 shadow-sm mb-6">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('projects.index') }}" class="row g-3 align-items-end">
                    <div class="col-lg-5 col-md-6">
                        <label class="form-label fw-semibold">Search Projects</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0"
                                placeholder="Project name, secret key, or creator..."
                                value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-lg-5 col-md-6">
                        <label class="form-label fw-semibold">Date Range</label>
                        <div class="input-group">
                            <input type="date" name="date_from" class="form-control"
                                value="{{ $filters['date_from'] ?? '' }}" placeholder="From">
                            <span class="input-group-text">to</span>
                            <input type="date" name="date_to" class="form-control"
                                value="{{ $filters['date_to'] ?? '' }}" placeholder="To">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-12">
                        <button type="submit"
                            class="btn btn-primary w-100 d-flex align-items-center justify-content-center">
                            <i class="fas fa-filter me-2"></i>
                            Filter
                        </button>
                        @if (request()->hasAny(['search', 'date_from', 'date_to']))
                            <a href="{{ route('projects.index') }}"
                                class="btn btn-outline-secondary w-100 mt-2 d-flex align-items-center justify-content-center">
                                <i class="fas fa-times me-2"></i>
                                Clear
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
        <!-- Projects Grid -->
        <div class="row" id="projectsGrid">
            @forelse($projects as $project)
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card border-0 shadow-sm h-100 project-card" data-project-id="{{ $project->id }}"
                        data-project-name="{{ $project->name }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div
                                            class="project-icon bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3">
                                            <i class="fas fa-project-diagram"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-semibold mb-0">{{ $project->name }}</h5>
                                            <small class="text-muted">ID: {{ $project->id }}</small>
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
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <button class="dropdown-item text-danger btn-delete"
                                                data-id="{{ $project->id }}" data-name="{{ $project->name }}">
                                                <i class="fas fa-trash me-2"></i>Delete
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="project-info mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-user-circle text-muted me-2"></i>
                                    <span class="text-muted">Created by:</span>
                                    <span class="ms-2 fw-medium">
                                        {{ $project->createdBy?->name ?? $project->created_by }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar text-muted me-2"></i>
                                    <span class="text-muted">Created:</span>
                                    <span class="ms-2 fw-medium">
                                        {{ $project->created_at->format('M d, Y') }}
                                    </span>
                                    <span class="badge bg-light text-muted ms-2">
                                        {{ $project->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>

                            <!-- Secret Key (Hidden by default) -->
                            {{-- <div class="secret-key-section mt-3">
                                <button class="btn btn-sm btn-outline-secondary w-100 toggle-secret-key" type="button"
                                    data-project-id="{{ $project->id }}">
                                    <i class="fas fa-key me-2"></i>
                                    <span class="show-text">Show Secret Key</span>
                                    <span class="hide-text d-none">Hide Secret Key</span>
                                </button>
                                <div class="secret-key-display mt-2 d-none" id="secretKey-{{ $project->id }}">
                                    <div class="input-group input-group-sm">
                                        <input type="password" class="form-control secret-key-input"
                                            value="{{ $project->secret_key }}" readonly>
                                        <button class="btn btn-outline-secondary copy-secret-key" type="button"
                                            data-key="{{ $project->secret_key }}">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary toggle-key-visibility" type="button">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted mt-1 d-block">Keep this key secure</small>
                                </div>
                            </div> --}}
                        </div>
                        <div
                            class="card-footer bg-transparent border-top d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                Last updated {{ $project?->updated_at?->diffForHumans() }}
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
                                <i class="fas fa-project-diagram fa-3x text-muted mb-3"></i>
                                <h4 class="fw-semibold">No Projects Found</h4>
                                <p class="text-muted mb-4">
                                    @if (request()->hasAny(['search', 'date_from', 'date_to']))
                                        Try adjusting your filters
                                    @else
                                        Get started by creating your first project
                                    @endif
                                </p>
                                @if (!request()->hasAny(['search', 'date_from', 'date_to']))
                                    <a href="{{ route('projects.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>
                                        Create First Project
                                    </a>
                                @else
                                    <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">
                                        Clear Filters
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
        <!-- Pagination -->
        @if ($projects->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-6">
                <div class="text-muted">
                    Showing {{ $projects->firstItem() ?? 0 }} to {{ $projects->lastItem() ?? 0 }}
                    of {{ $projects->total() }} projects
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0">
                        <li class="page-item {{ $projects->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $projects->previousPageUrl() }}">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>

                        @foreach (range(1, min(5, $projects->lastPage())) as $page)
                            @if (abs($page - $projects->currentPage()) <= 2 || $page == 1 || $page == $projects->lastPage())
                                <li class="page-item {{ $projects->currentPage() == $page ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $projects->url($page) }}">{{ $page }}</a>
                                </li>
                            @elseif(abs($page - $projects->currentPage()) == 3)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                        @endforeach

                        <li class="page-item {{ !$projects->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $projects->nextPageUrl() }}">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        @endif
    </div>

    <!-- New Project Success Modal -->
    <div class="modal fade" id="secretKeyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Project Created Successfully
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="alert alert-success bg-success bg-opacity-10 border-success border-opacity-25 mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle fa-lg text-success me-3"></i>
                            <div>
                                <p class="mb-0">Your project has been created successfully. Save the secret key below for
                                    API access.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Project Secret Key</label>
                        <div class="input-group">
                            <input type="text" class="form-control secret-key-display-input" id="secretKeyInput"
                                value="{{ session('secret_key') ?? '' }}" readonly>
                            <button class="btn btn-outline-primary" type="button" id="copySecretKeyBtn">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button class="btn btn-outline-secondary" type="button" id="toggleKeyVisibility">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">
                            <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                            This key will only be shown once. Please save it securely.
                        </div>
                    </div>

                    <div class="alert alert-warning bg-warning bg-opacity-10 border-warning border-opacity-25">
                        <div class="d-flex">
                            <i class="fas fa-shield-alt text-warning me-3"></i>
                            <div>
                                <h6 class="fw-semibold mb-2">Security Notice</h6>
                                <ul class="mb-0 small">
                                    <li>Store this key in a secure location</li>
                                    <li>Never commit this key to version control</li>
                                    <li>Rotate keys regularly for security</li>
                                    <li>Use environment variables in production</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        <i class="fas fa-check me-2"></i>
                        I've Saved the Key
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick View Modal -->
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold" id="quickViewTitle">Project Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="quickViewContent">
                    <!-- Content loaded via AJAX -->
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

        .project-card {
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .project-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
            border-color: var(--bs-primary);
        }

        .project-icon {
            width: 44px;
            height: 44px;
        }

        .secret-key-input {
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
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
            // Show secret key modal if exists
            @if (session('secret_key'))
                const secretKeyModal = new bootstrap.Modal(document.getElementById('secretKeyModal'));
                secretKeyModal.show();
            @endif

            // Copy secret key functionality
            const copySecretKeyBtn = document.getElementById('copySecretKeyBtn');
            if (copySecretKeyBtn) {
                copySecretKeyBtn.addEventListener('click', async function() {
                    const secretKeyInput = document.getElementById('secretKeyInput');
                    if (!secretKeyInput) return;

                    try {
                        await navigator.clipboard.writeText(secretKeyInput.value);

                        // Visual feedback
                        const originalIcon = this.innerHTML;
                        this.innerHTML = '<i class="fas fa-check"></i>';
                        this.classList.remove('btn-outline-primary');
                        this.classList.add('btn-success');

                        setTimeout(() => {
                            this.innerHTML = originalIcon;
                            this.classList.remove('btn-success');
                            this.classList.add('btn-outline-primary');
                        }, 2000);
                    } catch (err) {
                        console.error('Failed to copy:', err);
                        // Fallback for older browsers
                        secretKeyInput.select();
                        document.execCommand('copy');
                    }
                });
            }

            // Toggle key visibility
            const toggleKeyVisibility = document.getElementById('toggleKeyVisibility');
            if (toggleKeyVisibility) {
                toggleKeyVisibility.addEventListener('click', function() {
                    const input = document.getElementById('secretKeyInput');
                    const icon = this.querySelector('i');

                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            }

            // Project card interactions
            document.querySelectorAll('.toggle-secret-key').forEach(button => {
                button.addEventListener('click', function() {
                    const projectId = this.dataset.projectId;
                    const displayDiv = document.getElementById(`secretKey-${projectId}`);
                    const showText = this.querySelector('.show-text');
                    const hideText = this.querySelector('.hide-text');

                    if (displayDiv.classList.contains('d-none')) {
                        displayDiv.classList.remove('d-none');
                        showText.classList.add('d-none');
                        hideText.classList.remove('d-none');
                    } else {
                        displayDiv.classList.add('d-none');
                        showText.classList.remove('d-none');
                        hideText.classList.add('d-none');
                    }
                });
            });

            // Copy individual project keys
            document.querySelectorAll('.copy-secret-key').forEach(button => {
                button.addEventListener('click', async function() {
                    const key = this.dataset.key;
                    try {
                        await navigator.clipboard.writeText(key);

                        // Visual feedback
                        const originalIcon = this.innerHTML;
                        this.innerHTML = '<i class="fas fa-check"></i>';
                        this.classList.remove('btn-outline-secondary');
                        this.classList.add('btn-success');

                        setTimeout(() => {
                            this.innerHTML = originalIcon;
                            this.classList.remove('btn-success');
                            this.classList.add('btn-outline-secondary');
                        }, 2000);
                    } catch (err) {
                        console.error('Failed to copy:', err);
                    }
                });
            });

            // Toggle individual key visibility
            document.querySelectorAll('.toggle-key-visibility').forEach(button => {
                button.addEventListener('click', function() {
                    const input = this.closest('.input-group').querySelector('.secret-key-input');
                    const icon = this.querySelector('i');

                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });

            // Quick view functionality
            document.querySelectorAll('.project-card').forEach(card => {
                card.addEventListener('dblclick', function() {
                    const projectId = this.dataset.projectId;
                    loadQuickView(projectId);
                });
            });

            async function loadQuickView(projectId) {
                try {
                    const response = await fetch(`/projects/${projectId}/quick-view`);
                    if (response.ok) {
                        const content = await response.text();
                        document.getElementById('quickViewContent').innerHTML = content;
                        document.getElementById('quickViewTitle').textContent =
                            `Project: ${document.querySelector(`[data-project-id="${projectId}"]`).dataset.projectName}`;

                        const modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
                        modal.show();
                    }
                } catch (error) {
                    console.error('Error loading quick view:', error);
                }
            }

            // Delete project functionality
            document.querySelectorAll('.btn-delete').forEach(button => {
                button.addEventListener('click', function() {
                    const projectId = this.dataset.id;
                    const projectName = this.dataset.name;

                    if (confirm(
                            `Are you sure you want to delete project "${projectName}"? This action cannot be undone.`
                            )) {
                        // Submit delete form via AJAX
                        fetch(`/projects/${projectId}`, {
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

            // Filter animation
            const filterForm = document.querySelector('form[method="GET"]');
            if (filterForm) {
                filterForm.addEventListener('submit', function(e) {
                    document.getElementById('loadingOverlay')?.classList.add('active');
                });
            }
        });
    </script>
@endpush
