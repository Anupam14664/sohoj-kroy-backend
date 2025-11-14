@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h2>Admin Management</h2>
    <a href="{{ route('admin.admins.create') }}" class="btn btn-primary mb-3">Add New Admin</a>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>SL</th>
                <th>Name</th>
                <th>Email</th>
                <th>Roles</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($admins as $key => $admin)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $admin->name }}</td>
                <td>{{ $admin->email }}</td>
                <td>
                    @foreach($admin->roles as $role)
                        <span class="badge bg-info text-dark">{{ $role->name }}</span>
                    @endforeach
                </td>
                <td>
                    <a href="{{ route('admin.admins.edit', $admin->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    @if(!$admin->hasRole('Super Admin'))
                        <form action="{{ route('admin.admins.destroy', $admin->id) }}" method="POST" style="display:inline-block">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Are you sure?')" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
