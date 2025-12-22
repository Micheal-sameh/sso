@extends('layouts.sideBar')

@section('title', 'Create Group')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Create Group</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('groups.store') }}" method="POST">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="name">Name</label>
                                <input type="text" name="name" class="form-control" id="name" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="abbreviation">Abbreviation</label>
                                <input type="text" name="abbreviation" class="form-control" id="abbreviation" maxlength="10" required>
                                <small class="form-text text-muted">Maximum 10 characters</small>
                            </div>
                            <button type="submit" class="btn btn-primary">Create Group</button>
                            <a href="{{ route('groups.index') }}" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
