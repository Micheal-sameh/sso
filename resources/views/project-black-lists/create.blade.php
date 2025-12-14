@extends('layouts.sideBar')

@section('title', 'Add to Project Black List')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add to Project Black List</h3>
                    <a href="{{ route('project-black-lists.index') }}" class="btn btn-secondary float-right">Back</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('project-black-lists.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="project_id">Project</label>
                            <select name="project_id" id="project_id" class="form-control" required>
                                <option value="">Select Project</option>
                                @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="user_id">User</label>
                            <select name="user_id" id="user_id" class="form-control" required>
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ isset($selectedUserId) && $selectedUserId == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="reason">Reason</label>
                            <textarea name="reason" id="reason" class="form-control" rows="3" placeholder="Enter reason for blacklisting"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Add to Black List</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
