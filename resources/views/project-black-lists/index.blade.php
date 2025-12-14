@extends('layouts.sideBar')

@section('title', 'Project Black Lists')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Project Black Lists</h3>
                    <a href="{{ route('project-black-lists.create') }}" class="btn btn-primary float-right">Add to Black List</a>
                </div>
                <div class="card-header">
                    <form method="GET" action="{{ route('project-black-lists.index') }}" class="form-inline">
                        <div class="form-group mr-2">
                            <input type="text" name="search" class="form-control" placeholder="Search by project, user, or reason" value="{{ $filters['search'] ?? '' }}">
                        </div>
                        <div class="form-group mr-2">
                            <select name="project_id" class="form-control">
                                <option value="">All Projects</option>
                                @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ ($filters['project_id'] ?? '') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-secondary">Filter</button>
                        <a href="{{ route('project-black-lists.index') }}" class="btn btn-outline-secondary ml-2">Clear</a>
                    </form>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Project</th>
                                <th>User</th>
                                <th>Reason</th>
                                <th>Created By</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projectBlackLists as $projectBlackList)
                            <tr>
                                <td>{{ $projectBlackList->id }}</td>
                                <td>{{ $projectBlackList->project->name }}</td>
                                <td>{{ $projectBlackList->user->name }}</td>
                                <td>{{ $projectBlackList->reason }}</td>
                                <td>{{ $projectBlackList->creator->name }}</td>
                                <td>{{ $projectBlackList->created_at }}</td>
                                <td>
                                    <a href="{{ route('project-black-lists.show', $projectBlackList->id) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                                    <form action="{{ route('project-black-lists.destroy', $projectBlackList->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
