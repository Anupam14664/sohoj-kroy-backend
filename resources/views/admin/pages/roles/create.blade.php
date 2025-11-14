@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h2>Create New Role</h2>

    <form action="{{ route('admin.roles.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Role Name</label>
            <input type="text" name="name" class="form-control" required>
            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label>Permissions</label><br>
            @foreach ($permissions as $perm)
                <label class="me-3">
                    <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"> {{ $perm->name }}
                </label>
            @endforeach
        </div>

        <button type="submit" class="btn btn-success">Create Role</button>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection
