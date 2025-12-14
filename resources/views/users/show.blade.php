@extends('layouts.sideBar')
@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1 class="h3 mb-2 text-dark">
                    <i class="fas fa-user-circle me-2"></i>
                    {{ __('messages.user_details') }}
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">{{ __('messages.users') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
                    </ol>
                </nav>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary d-flex align-items-center">
                    <i class="fas fa-arrow-left me-2"></i>
                    {{ __('messages.back_to_list') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Profile Card -->
        <div class="col-lg-4 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <!-- Profile Avatar -->
                    <div class="mb-4">
                        @if($user->profile_photo)
                        <div class="avatar-xxl mx-auto mb-3">
                            <img src="{{ asset('storage/' . $user->profile_photo) }}"
                                 alt="{{ $user->name }}"
                                 class="rounded-circle border border-4 border-primary"
                                 width="150"
                                 height="150"
                                 loading="lazy"
                                 onerror="this.src='{{ asset('default-avatar.png') }}'">
                        </div>
                        @else
                        <div class="avatar-xxl mx-auto mb-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 150px; height: 150px; font-size: 3rem;">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        @endif

                        <h3 class="h5 mb-2">{{ $user->name }}</h3>
                        <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
                            @if($user->is_admin)
                            <span class="badge bg-danger bg-opacity-10 text-danger">
                                <i class="fas fa-shield-alt me-1"></i>{{ __('messages.administrator') }}
                            </span>
                            @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                <i class="fas fa-user me-1"></i>{{ __('messages.user') }}
                            </span>
                            @endif

                            @if($user->status)
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="fas fa-circle me-1 small"></i>{{ __('messages.active') }}
                            </span>
                            @else
                            <span class="badge bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-circle me-1 small"></i>{{ __('messages.inactive') }}
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <div class="bg-light rounded p-3">
                                <div class="text-primary mb-1">
                                    <i class="fas fa-calendar-check fa-lg"></i>
                                </div>
                                <div class="h5 mb-0">{{ $user->created_at->format('M Y') }}</div>
                                <small class="text-muted">{{ __('messages.joined') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded p-3">
                                <div class="text-success mb-1">
                                    <i class="fas fa-clock fa-lg"></i>
                                </div>
                                <div class="h5 mb-0">
                                    {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : __('messages.never') }}
                                </div>
                                <small class="text-muted">{{ __('messages.last_login') }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Actions -->
                    <div class="d-grid gap-2">
                        <a href="mailto:{{ $user->email }}" class="btn btn-outline-primary d-flex align-items-center justify-content-center">
                            <i class="fas fa-envelope me-2"></i>
                            {{ __('messages.send_email') }}
                        </a>
                        @if($user->phone)
                        <a href="tel:{{ $user->phone }}" class="btn btn-outline-success d-flex align-items-center justify-content-center">
                            <i class="fas fa-phone me-2"></i>
                            {{ __('messages.call') }}
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: User Details -->
        <div class="col-lg-8 col-xl-9">
            <!-- User Information Card -->
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light border-0 px-4 py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('messages.user_information') }}
                    </h5>
                </div>

                <div class="card-body">
                    <div class="row g-4">
                        <!-- Personal Information -->
                        <div class="col-md-6">
                            <h6 class="mb-3 text-primary border-bottom pb-2">
                                <i class="fas fa-id-card me-2"></i>
                                {{ __('messages.personal_information') }}
                            </h6>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <small class="text-muted">{{ __('messages.full_name') }}</small>
                                        <div class="fw-semibold">{{ $user->name }}</div>
                                    </div>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-user"></i>
                                    </span>
                                </div>

                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <small class="text-muted">{{ __('messages.email_address') }}</small>
                                        <div class="fw-semibold">{{ $user->email }}</div>
                                    </div>
                                    @if($user->email_verified_at)
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        <i class="fas fa-check-circle me-1"></i>{{ __('messages.verified') }}
                                    </span>
                                    @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning">
                                        <i class="fas fa-clock me-1"></i>{{ __('messages.unverified') }}
                                    </span>
                                    @endif
                                </div>

                                @if($user->phone)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <small class="text-muted">{{ __('messages.phone_number') }}</small>
                                        <div class="fw-semibold">{{ $user->phone }}</div>
                                    </div>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-phone"></i>
                                    </span>
                                </div>
                                @endif

                                @if($user->address)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <small class="text-muted">{{ __('messages.address') }}</small>
                                        <div class="fw-semibold">{{ $user->address }}</div>
                                    </div>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </span>
                                </div>
                                @endif

                                @if($user->date_of_birth)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <small class="text-muted">{{ __('messages.date_of_birth') }}</small>
                                        <div class="fw-semibold">
                                            {{ \Carbon\Carbon::parse($user->date_of_birth)->format('F d, Y') }}
                                            <small class="text-muted ms-1">
                                                ({{ \Carbon\Carbon::parse($user->date_of_birth)->age }} {{ __('messages.years_old') }})
                                            </small>
                                        </div>
                                    </div>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-birthday-cake"></i>
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Account Information -->
                        <div class="col-md-6">
                            <h6 class="mb-3 text-primary border-bottom pb-2">
                                <i class="fas fa-cog me-2"></i>
                                {{ __('messages.account_information') }}
                            </h6>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <small class="text-muted">{{ __('messages.membership_code') }}</small>
                                        <div class="fw-semibold">#{{ strtoupper($user->membership_code) }}</div>
                                    </div>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-hashtag"></i>
                                    </span>
                                </div>

                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <small class="text-muted">{{ __('messages.account_status') }}</small>
                                        <div>
                                            @if($user->status)
                                            <span class="badge bg-success bg-opacity-10 text-success">
                                                <i class="fas fa-check-circle me-1"></i>{{ __('messages.active') }}
                                            </span>
                                            @else
                                            <span class="badge bg-warning bg-opacity-10 text-warning">
                                                <i class="fas fa-ban me-1"></i>{{ __('messages.inactive') }}
                                            </span>
                                            @endif
                                            @if($user->is_banned)
                                            <span class="badge bg-danger bg-opacity-10 text-danger ms-2">
                                                <i class="fas fa-gavel me-1"></i>{{ __('messages.banned') }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-user-check"></i>
                                    </span>
                                </div>

                                </div>

                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <small class="text-muted">{{ __('messages.member_since') }}</small>
                                        <div class="fw-semibold">
                                            {{ $user->created_at->format('F d, Y') }}
                                            <small class="text-muted d-block">
                                                {{ $user->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                    </div>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-calendar-plus"></i>
                                    </span>
                                </div>

                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <small class="text-muted">{{ __('messages.last_updated') }}</small>
                                        <div class="fw-semibold">
                                            {{ $user->updated_at->format('F d, Y') }}
                                            <small class="text-muted d-block">
                                                {{ $user->updated_at->diffForHumans() }}
                                            </small>
                                        </div>
                                    </div>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-calendar-check"></i>
                                    </span>
                                </div>

                                @if($user->last_login_at)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <small class="text-muted">{{ __('messages.last_login') }}</small>
                                        <div class="fw-semibold">
                                            {{ $user->last_login_at->format('F d, Y h:i A') }}
                                            <small class="text-muted d-block">
                                                {{ $user->last_login_at->diffForHumans() }}
                                            </small>
                                        </div>
                                    </div>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information Section -->
                    @if($user->bio || $user->website || $user->social_links)
                    <div class="row g-4 mt-4">
                        <div class="col-12">
                            <h6 class="mb-3 text-primary border-bottom pb-2">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('messages.additional_information') }}
                            </h6>
                            <div class="row g-3">
                                @if($user->bio)
                                <div class="col-12">
                                    <label class="form-label text-muted small">{{ __('messages.biography') }}</label>
                                    <div class="card bg-light border-0">
                                        <div class="card-body">
                                            {{ $user->bio }}
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if($user->website)
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">{{ __('messages.website') }}</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-globe text-primary me-2"></i>
                                        <a href="{{ $user->website }}" target="_blank" class="text-decoration-none">
                                            {{ $user->website }}
                                        </a>
                                    </div>
                                </div>
                                @endif

                                @if($user->social_links)
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">{{ __('messages.social_links') }}</label>
                                    <div class="d-flex gap-2">
                                        @php
                                            $socialLinks = json_decode($user->social_links, true) ?? [];
                                        @endphp
                                        @foreach($socialLinks as $platform => $link)
                                            @if($link)
                                            <a href="{{ $link }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                <i class="fab fa-{{ $platform }} me-1"></i>
                                                {{ ucfirst($platform) }}
                                            </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Recent Actions Section -->
                    <div class="row g-4 mt-4">
                        <div class="col-12">
                            <h6 class="mb-3 text-primary border-bottom pb-2">
                                <i class="fas fa-history me-2"></i>
                                {{ __('messages.recent_actions') }}
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.date') }}</th>
                                            <th>{{ __('messages.action') }}</th>
                                            <th>{{ __('messages.details') }}</th>
                                            <th>{{ __('messages.ip_address') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($user->loginLogs && $user->loginLogs->count() > 0)
                                            @foreach($user->loginLogs->take(5) as $log)
                                            <tr>
                                                <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        {{ __('messages.login') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <small>{{ $log->user_agent }}</small>
                                                </td>
                                                <td><code>{{ $log->ip_address }}</code></td>
                                            </tr>
                                            @endforeach
                                        @else
                                        <tr>
                                            <td colspan="4" class="text-center py-3">
                                                <div class="text-muted">
                                                    <i class="fas fa-history fa-lg opacity-25 me-2"></i>
                                                    {{ __('messages.no_recent_actions') }}
                                                </div>
                                            </td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            @if($user->loginLogs && $user->loginLogs->count() > 5)
                            <div class="text-center mt-2">
                                <a href="#" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>
                                    {{ __('messages.view_all_actions') }}
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Family Members Section -->
                    <div class="row g-4 mt-4">
                        <div class="col-12">
                            <h6 class="mb-3 text-primary border-bottom pb-2">
                                <i class="fas fa-users me-2"></i>
                                {{ __('messages.family_members') }}
                            </h6>
                            @if($familyMembers && $familyMembers->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>{{ __('messages.name') }}</th>
                                                <th>{{ __('messages.membership_code') }}</th>
                                                <th>{{ __('messages.status') }}</th>
                                                <th>{{ __('messages.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($familyMembers as $member)
                                            <tr>
                                                <td>{{ $member->name }}</td>
                                                <td><code>{{ strtoupper($member->membership_code) }}</code></td>
                                                <td>
                                                    @if($member->status)
                                                    <span class="badge bg-success bg-opacity-10 text-success">
                                                        <i class="fas fa-circle me-1 small"></i>{{ __('messages.active') }}
                                                    </span>
                                                    @else
                                                    <span class="badge bg-warning bg-opacity-10 text-warning">
                                                        <i class="fas fa-circle me-1 small"></i>{{ __('messages.inactive') }}
                                                    </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('users.show', $member->id) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye me-1"></i>
                                                        {{ __('messages.view') }}
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-users fa-lg opacity-25 me-2"></i>
                                        {{ __('messages.no_family_members') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Card Footer with Action Buttons -->
                <div class="card-footer bg-light border-0 px-4 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <i class="fas fa-clock me-1"></i>
                            {{ __('messages.last_updated') }}: {{ $user->updated_at->diffForHumans() }}
                        </div>
                        <div class="d-flex gap-2">
                            <!-- Quick Status Toggle -->
                            @if(auth()->user()->is_admin && auth()->id() !== $user->id)
                            <form action="{{ route('users.toggle-status', $user->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-{{ $user->is_active ? 'warning' : 'success' }}">
                                    <i class="fas fa-power-off me-1"></i>
                                    {{ $user->is_active ? __('messages.deactivate') : __('messages.activate') }}
                                </button>
                            </form>


                            @endif

                            <!-- Send Reset Password Email -->
                            @if(auth()->user()->is_admin)
                            <form action="{{ route('users.send-reset-link', $user->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-key me-1"></i>
                                    {{ __('messages.send_reset_link') }}
                                </button>
                            </form>
                            @endif

                            <!-- Add to Black List -->
                            {{-- @if(auth()->user()->is_admin) --}}
                            <a href="{{ route('project-black-lists.create', ['user_id' => $user->id]) }}" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-ban me-1"></i>
                                Add to Black List
                            </a>
                            {{-- @endif --}}

                            <!-- Add to SSO Users -->
                            <form action="{{ route('sso-users.store') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-user-plus me-1"></i>
                                    Add to SSO Users
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-xxl {
        width: 150px;
        height: 150px;
        margin: 0 auto;
    }

    .list-group-item {
        border: none;
        padding: 15px 0;
        border-bottom: 1px solid var(--bs-border-color) !important;
    }

    .list-group-item:last-child {
        border-bottom: none !important;
    }

    .card {
        border-radius: 12px;
    }

    .badge {
        padding: 0.5em 0.9em;
        font-weight: 500;
    }

    .border-bottom {
        border-bottom: 2px solid var(--bs-primary) !important;
    }

    .table-sm td, .table-sm th {
        padding: 0.75rem 0.5rem;
    }

    code {
        background: var(--bs-light);
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.875em;
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
            font-size: 0.875rem;
        }

        .card-footer .btn {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    }

    @media (max-width: 576px) {
        .card-footer .d-flex {
            flex-direction: column;
            gap: 0.5rem;
            align-items: stretch;
        }

        .card-footer .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(tooltip => new bootstrap.Tooltip(tooltip));

        // Handle status toggle confirmation
        const statusToggleForms = document.querySelectorAll('form[action*="toggle-status"]');
        statusToggleForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const button = this.querySelector('button[type="submit"]');
                const action = button.textContent.includes('Deactivate') ?
                    '{{ __("messages.confirm_deactivate_user") }}' :
                    '{{ __("messages.confirm_activate_user") }}';

                if (confirm(action)) {
                    this.submit();
                }
            });
        });

        // Handle admin toggle confirmation
        const adminToggleForms = document.querySelectorAll('form[action*="toggle-admin"]');
        adminToggleForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const button = this.querySelector('button[type="submit"]');
                const action = button.textContent.includes('Remove Admin') ?
                    '{{ __("messages.confirm_remove_admin") }}' :
                    '{{ __("messages.confirm_make_admin") }}';

                if (confirm(action)) {
                    this.submit();
                }
            });
        });

        // Handle reset password link
        const resetForms = document.querySelectorAll('form[action*="send-reset-link"]');
        resetForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const button = this.querySelector('button[type="submit"]');
                const originalText = button.innerHTML;

                if (confirm('{{ __("messages.confirm_send_reset_link") }}')) {
                    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';
                    button.disabled = true;

                    fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('{{ __("messages.reset_link_sent") }}');
                        } else {
                            alert('{{ __("messages.error_sending_reset_link") }}');
                        }
                    })
                    .catch(error => {
                        alert('{{ __("messages.error_sending_reset_link") }}');
                    })
                    .finally(() => {
                        button.innerHTML = originalText;
                        button.disabled = false;
                    });
                }
            });
        });

        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    });
</script>
@endpush
@endsection