@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h1>Homepage Sections</h1>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped table-hover table-head-bg-primary mt-4">
                <thead>
                    <tr>
                        <th>Position</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Categories</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sections as $section)
                    <tr>
                        <td>{{ $section->position }}</td>
                        <td>{{ $section->name }}</td>
                        <td>
                            <span class="badge bg-{{ $section->is_active ? 'success' : 'danger' }}">
                                {{ $section->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @foreach($section->categories as $category)
                            <span class="badge bg-secondary">{{ $category->name }}</span>
                            @endforeach
                        </td>
                        <td>
                            <a href="{{ route('admin.homepage-sections.edit', $section->id) }}" class="btn btn-sm btn-primary">
                                Edit
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection