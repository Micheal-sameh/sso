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

                        <!-- Group Members -->
                        <div class="col-md-6">
                            <h6 class="mb-3 text-primary border-bottom pb-2">
                                <i class="fas fa-users me-2"></i>
                                {{ __('messages.group_members') }}
                                <button type="button" class="btn btn-sm btn-outline-primary ms-2" data-bs-toggle="modal" data-bs-target="#manageMembersModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </h6>
                            <form id="manageMembersForm" method="POST" action="{{ route('groups.manageMembers', $group->id) }}">
                                @csrf
                                <div class="list-group list-group-flush">
                                    @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="user_{{ $user->id }}" name="user_ids[]" value="{{ $user->id }}" {{ $group->users->contains($user->id) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="user_{{ $user->id }}">
                                                    <div class="fw-semibold">{{ $user->name }}</div>
                                                    <small class="text-muted">{{ strtoupper($user->membership_code) ?? $user->membership_code }}</small>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">{{ __('messages.save_changes') }}</button>
                            </form>
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

<!-- Manage Members Modal -->
<div class="modal fade" id="manageMembersModal" tabindex="-1" aria-labelledby="manageMembersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageMembersModalLabel">{{ __('messages.manage_group_members') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" id="userSearch" class="form-control" placeholder="{{ __('messages.search_users') }}">
                </div>
                <div class="d-flex align-items-center mb-2">
                    <div class="form-check me-3">
                        <input class="form-check-input" type="checkbox" id="selectVisibleUsers">
                        <label class="form-check-label small" for="selectVisibleUsers">{{ __('messages.select_visible') }}</label>
                    </div>
                    <div class="small text-muted">{{ __('messages.selected') }}: <span id="selectedUsersCount">0</span></div>
                </div>
                <div id="usersList" class="list-group list-group-flush" style="max-height:400px; overflow:auto;"></div>
                <nav id="usersPagination" class="mt-3"></nav>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                <button type="button" id="saveMembersBtn" class="btn btn-primary">{{ __('messages.save_changes') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    const usersUrl = "{{ route('groups.users', $group->id) }}";
    const manageUrl = "{{ route('groups.manageMembers', $group->id) }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Persist selection across pages
    const selectedUserIds = new Set(@json($group->users->pluck('id')));

    let currentQuery = '';
    let currentPage = 1;

    function updateUserSelectedCount() {
        document.getElementById('selectedUsersCount').textContent = selectedUserIds.size;
    }

    function renderUsers(data) {
        const container = document.getElementById('usersList');
        container.innerHTML = '';

        if (!data || data.length === 0) {
            container.innerHTML = '<div class="text-muted p-3">{{ __('messages.no_users_found') }}</div>';
            document.getElementById('usersPagination').innerHTML = '';
            return;
        }

        data.forEach(user => {
            if (user.is_member) selectedUserIds.add(user.id);

            const item = document.createElement('div');
            item.className = 'list-group-item d-flex justify-content-between align-items-center px-0';

            const checked = selectedUserIds.has(user.id) ? 'checked' : '';

            const html = `
                <div class="form-check">
                    <input class="form-check-input user-checkbox" type="checkbox" id="user_${user.id}" value="${user.id}" ${checked}>
                    <label class="form-check-label" for="user_${user.id}">
                        <div class="fw-semibold">${escapeHtml(user.name)}</div>
                        <small class="text-muted">${escapeHtml(user.membership_code || user.membership_code)}</small>
                    </label>
                </div>
            `;

            item.innerHTML = html;
            container.appendChild(item);
        });

        const visibleIds = Array.from(container.querySelectorAll('.user-checkbox')).map(cb => parseInt(cb.value));
        const allVisibleSelected = visibleIds.length > 0 && visibleIds.every(id => selectedUserIds.has(id));
        document.getElementById('selectVisibleUsers').checked = allVisibleSelected;

        updateUserSelectedCount();
    }

    function renderPagination(meta) {
        const nav = document.getElementById('usersPagination');
        nav.innerHTML = '';

        if (!meta || meta.last_page <= 1) return;

        const ul = document.createElement('ul');
        ul.className = 'pagination';

        const prevLi = document.createElement('li');
        prevLi.className = 'page-item ' + (meta.current_page === 1 ? 'disabled' : '');
        prevLi.innerHTML = `<a class="page-link" href="#" data-page="${meta.current_page - 1}">&laquo;</a>`;
        ul.appendChild(prevLi);

        for (let i = 1; i <= meta.last_page; i++) {
            const li = document.createElement('li');
            li.className = 'page-item ' + (i === meta.current_page ? 'active' : '');
            li.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
            ul.appendChild(li);
        }

        const nextLi = document.createElement('li');
        nextLi.className = 'page-item ' + (meta.current_page === meta.last_page ? 'disabled' : '');
        nextLi.innerHTML = `<a class="page-link" href="#" data-page="${meta.current_page + 1}">&raquo;</a>`;
        ul.appendChild(nextLi);

        nav.appendChild(ul);

        nav.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e){
                e.preventDefault();
                const page = parseInt(this.getAttribute('data-page'));
                if (!isNaN(page)) loadUsers(page);
            });
        });
    }

    function loadUsers(page = 1) {
        currentPage = page;
        const q = currentQuery;
        const url = usersUrl + '?page=' + page + '&q=' + encodeURIComponent(q);

        fetch(url, { headers: {'X-Requested-With': 'XMLHttpRequest'} })
            .then(res => res.json())
            .then(json => {
                renderUsers(json.data);
                renderPagination({ current_page: json.current_page, last_page: json.last_page });
            })
            .catch(err => {
                console.error(err);
            });
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/[&<>"']/g, function(c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c]; });
    }

    function debounce(fn, delay){
        let t;
        return function(...args){
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), delay);
        }
    }

    const searchInput = document.getElementById('userSearch');
    const debouncedSearch = debounce(function(e){
        currentQuery = this.value.trim();
        loadUsers(1);
    }, 300);

    if (searchInput) searchInput.addEventListener('input', debouncedSearch);

    const manageModal = document.getElementById('manageMembersModal');
    manageModal.addEventListener('show.bs.modal', function(){
        currentQuery = '';
        searchInput.value = '';
        loadUsers(1);
    });

    // delegate checkbox change
    document.getElementById('usersList').addEventListener('change', function(e){
        if (e.target && e.target.classList.contains('user-checkbox')) {
            const id = parseInt(e.target.value);
            if (e.target.checked) selectedUserIds.add(id); else selectedUserIds.delete(id);
            updateUserSelectedCount();

            const container = document.getElementById('usersList');
            const visibleIds = Array.from(container.querySelectorAll('.user-checkbox')).map(cb => parseInt(cb.value));
            const allVisibleSelected = visibleIds.length > 0 && visibleIds.every(id => selectedUserIds.has(id));
            document.getElementById('selectVisibleUsers').checked = allVisibleSelected;
        }
    });

    document.getElementById('selectVisibleUsers').addEventListener('change', function(){
        const container = document.getElementById('usersList');
        const checkboxes = Array.from(container.querySelectorAll('.user-checkbox'));
        const check = this.checked;
        checkboxes.forEach(cb => {
            const id = parseInt(cb.value);
            cb.checked = check;
            if (check) selectedUserIds.add(id); else selectedUserIds.delete(id);
        });
        updateUserSelectedCount();
    });

    document.getElementById('saveMembersBtn').addEventListener('click', function(){
        const checkedArray = Array.from(selectedUserIds);

        fetch(manageUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ user_ids: checkedArray })
        })
        .then(res => {
            if (!res.ok) throw res;
            return res.json();
        })
        .then(() => {
            // close modal and reload
            const modal = bootstrap.Modal.getInstance(manageModal);
            modal.hide();
            location.reload();
        })
        .catch(err => {
            console.error(err);
            alert('{{ __('messages.error_occurred') }}');
        });
    });

    // initialize count
    updateUserSelectedCount();
})();
</script>

<script>
function removeMember(userId, userName) {
    if (confirm('{{ __('messages.confirm_remove_member') }}'.replace(':name', userName))) {
        fetch(`{{ route('groups.removeMember', $group->id) }}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ user_id: userId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '{{ __('messages.error_occurred') }}');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __('messages.error_occurred') }}');
        });
    }
}


</script>

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
