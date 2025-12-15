@extends('layouts.sideBar')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>SSO Users</h4>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User ID</th>
                                <th>Created By</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ssoUsers as $ssoUser)
                            <tr>
                                <td>{{ $ssoUser->id }}</td>
                                <td><a href="{{route('users.show', $ssoUser->user_id)}}"> {{ $ssoUser->user->name }}</a></td>
                                <td><a href="{{route('users.show', $ssoUser->created_by)}}">{{ $ssoUser->createdBy->name }}</a></td>
                                <td>{{ $ssoUser->created_at->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    <form action="{{ route('sso-users.destroy', $ssoUser->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No SSO users found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-center pt-2">
    @if ($ssoUsers->hasPages())
        <nav>
            <ul class="pagination">
                {{-- Previous Page Link --}}
                @if ($ssoUsers->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $ssoUsers->previousPageUrl() }}" rel="prev">&laquo;</a>
                    </li>
                @endif

                @php
                    $current = $ssoUsers->currentPage();
                    $last = $ssoUsers->lastPage();
                    $start = max($current - 2, 2);
                    $end = min($current + 2, $last - 1);
                @endphp

                {{-- First page --}}
                <li class="page-item {{ $current === 1 ? 'active' : '' }}">
                    <a class="page-link" href="{{ $ssoUsers->url(1) }}">1</a>
                </li>

                {{-- Dots before start --}}
                @if ($start > 2)
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                @endif

                {{-- Page range --}}
                @for ($page = $start; $page <= $end; $page++)
                    <li class="page-item {{ $current === $page ? 'active' : '' }}">
                        <a class="page-link" href="{{ $ssoUsers->url($page) }}">{{ $page }}</a>
                    </li>
                @endfor

                {{-- Dots after end --}}
                @if ($end < $last - 1)
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                @endif

                {{-- Last page --}}
                @if ($last > 1)
                    <li class="page-item {{ $current === $last ? 'active' : '' }}">
                        <a class="page-link" href="{{ $ssoUsers->url($last) }}">{{ $last }}</a>
                    </li>
                @endif

                {{-- Next Page Link --}}
                @if ($ssoUsers->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $ssoUsers->nextPageUrl() }}" rel="next">&raquo;</a>
                    </li>
                @else
                    <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                @endif
            </ul>
        </nav>
    @endif
</div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection
