@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h2>Edit Admin: {{ $admin->name }}</h2>

    <form action="{{ route('admin.admins.update', $admin->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Name</label>
            <input name="name" class="form-control" value="{{ $admin->name }}" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input name="email" type="email" class="form-control" value="{{ $admin->email }}" required>
        </div>

        <div class="mb-3">
            <label>New Password (optional)</label>
            <input name="password" type="password" class="form-control">
        </div>

        <div class="mb-3">
            <label>Assign Roles</label><br>
            @foreach($roles as $role)
                <label class="me-3">
                    <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                        {{ in_array($role->name, $adminRoles) ? 'checked' : '' }}>
                    {{ $role->name }}
                </label>
            @endforeach
        </div>

        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('admin.admins.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection
