@extends('admin.layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Category Details: {{ $category->name }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    @if($category->image)
                        <img src="{{ Storage::url($category->image) }}" class="img-fluid" alt="Category Image">
                    @else
                        <div class="text-muted">No image available</div>
                    @endif
                </div>
                <div class="col-md-8">
                    <table class="table table-bordered">
                        <tr>
                            <th>Name</th>
                            <td>{{ $category->name }}</td>
                        </tr>
                        <tr>
                            <th>Slug</th>
                            <td>{{ $category->slug }}</td>
                        </tr>
                        <tr>
                            <th>Parent Category</th>
                            <td>{{ $category->parent->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Products Count</th>
                            <td>{{ $category->products->count() }}</td>
                        </tr>
                        <tr>
                            <th>Subcategories</th>
                            <td>
                                @forelse($category->children as $child)
                                    <span class="badge badge-primary">{{ $child->name }}</span>
                                @empty
                                    No subcategories
                                @endforelse
                            </td>
                        </tr>
                    </table>

                    <div class="mt-4">
                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-light mx-4">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection