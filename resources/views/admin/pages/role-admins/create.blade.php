@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h2>Create New Admin</h2>

    <form action="{{ route('admin.admins.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Name</label>
            <input name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input name="email" type="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input name="password" type="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Assign Roles</label><br>
            @foreach($roles as $role)
                <label class="me-3">
                    <input type="checkbox" name="roles[]" value="{{ $role->name }}"> {{ $role->name }}
                </label>
            @endforeach
        </div>

        <button type="submit" class="btn btn-success">Create</button>
        <a href="{{ route('admin.admins.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection
