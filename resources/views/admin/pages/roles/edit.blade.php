@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h2>Edit Role: {{ $role->name }}</h2>

    <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Role Name</label>
            <input type="text" name="name" value="{{ $role->name }}" class="form-control" required>
            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label>Permissions</label><br>
            @foreach ($permissions as $perm)
                <label class="me-3">
                    <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                        {{ in_array($perm->name, $rolePermissions) ? 'checked' : '' }}>
                    {{ $perm->name }}
                </label>
            @endforeach
        </div>

        <button type="submit" class="btn btn-success">Update Role</button>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection
