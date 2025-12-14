@extends('layouts.sideBar')

@section('title', 'Project Black List Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Project Black List Details</h3>
                    <a href="{{ route('project-black-lists.index') }}" class="btn btn-secondary float-right">Back</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ID:</strong> {{ $projectBlackList->id }}</p>
                            <p><strong>Project:</strong> {{ $projectBlackList->project->name }}</p>
                            <p><strong>User:</strong> {{ $projectBlackList->user->name }}</p>
                            <p><strong>Reason:</strong> {{ $projectBlackList->reason }}</p>
                            <p><strong>Created By:</strong> {{ $projectBlackList->creator->name }}</p>
                            <p><strong>Created At:</strong> {{ $projectBlackList->created_at }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
