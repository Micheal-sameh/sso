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
                    {{ $ssoUsers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
