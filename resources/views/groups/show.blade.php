@extends('layouts.sideBar')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1 class="h3 mb-2 text-dark">
                    <i class="fas fa-users me-2"></i>
                    {{ __('messages.group_details') }}
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('groups.index') }}">{{ __('messages.groups') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $group->name }}</li>
                    </ol>
                </nav>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('groups.index') }}" class="btn btn-outline-secondary d-flex align-items-center">
                    <i class="fas fa-arrow-left me-2"></i>
                    {{ __('messages.back_to_list') }}
                </a>
                <a href="{{ route('groups.edit', $group->id) }}" class="btn btn-primary d-flex align-items-center">
                    <i class="fas fa-edit me-2"></i>
                    {{ __('messages.edit_group') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Group Information Card -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-0 px-4 py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('messages.group_information') }}
                    </h5>
                </div>

                <div class="card-body">
                    <div class="row g-4">
                        <!-- Group Details -->
                        <div class="col-md-6">
                            <h6 class="mb-3 text-primary border-bottom pb-2">
                                <i class="fas fa-users me-2"></i>
                                {{ __('messages.group_details') }}
                            </h6>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <small class="text-muted">{{ __('messages.group_name') }}</small>
                                        <div class="fw-semibold">{{ $group->name }}</div>
                                    </div>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-tag"></i>
                                    </span>
                                </div>

                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <small class="text-muted">{{ __('messages.abbreviation') }}</small>
                                        <div class="fw-semibold">{{ $group->abbreviation }}</div>
                                    </div>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-hashtag"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Group Statistics -->
                        <div class="col-md-6">
                            <h6 class="mb-3 text-primary border-bottom pb-2">
                                <i class="fas fa-chart-bar me-2"></i>
                                {{ __('messages.group_statistics') }}
                            </h6>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <small class="text-muted">{{ __('messages.created_at') }}</small>
                                        <div class="fw-semibold">{{ $group->created_at->format('F d, Y') }}</div>
                                        <small class="text-muted">{{ $group->created_at->diffForHumans() }}</small>
                                    </div>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-calendar-plus"></i>
                                    </span>
                                </div>

                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <small class="text-muted">{{ __('messages.last_updated') }}</small>
                                        <div class="fw-semibold">{{ $group->updated_at->format('F d, Y') }}</div>
                                        <small class="text-muted">{{ $group->updated_at->diffForHumans() }}</small>
                                    </div>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-calendar-check"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Footer with Action Buttons -->
                <div class="card-footer bg-light border-0 px-4 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <i class="fas fa-clock me-1"></i>
                            {{ __('messages.last_updated') }}: {{ $group->updated_at->diffForHumans() }}
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('groups.edit', $group->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit me-1"></i>
                                {{ __('messages.edit') }}
                            </a>
                            <form action="{{ route('groups.destroy', $group->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('{{ __('messages.confirm_delete_group') }}')">
                                    <i class="fas fa-trash me-1"></i>
                                    {{ __('messages.delete') }}
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

    @media (max-width: 768px) {
        .page-header {
            padding: 1rem;
        }

        .card-body {
            padding: 1rem !important;
        }

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
@endsection
