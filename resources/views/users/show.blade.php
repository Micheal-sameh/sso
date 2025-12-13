@extends('layouts.sideBar')

@section('title', 'User Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">User Details</h4>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-3">Basic Information</h5>
                        <table class="table table-borderless">
                            <tr>
                                <th width="150">ID:</th>
                                <td>{{ $user->id }}</td>
                            </tr>
                            <tr>
                                <th>Name:</th>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge {{ $user->status == 1 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $user->status == 1 ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Created At:</th>
                                <td>{{ $user->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Updated At:</th>
                                <td>{{ $user->updated_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h5 class="mb-3">AR Tokens</h5>
                        @if($user->arToken)
                            <table class="table table-borderless">
                                @foreach($user->arToken->toArray() as $key => $value)
                                    @if($key !== 'id' && $key !== 'user_id' && $key !== 'created_at' && $key !== 'updated_at' && !empty($value))
                                        <tr>
                                            <th width="150">{{ ucfirst($key) }} Token:</th>
                                            <td>
                                                <code class="small">{{ $value }}</code>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </table>
                        @else
                            <p class="text-muted">No AR tokens found.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
