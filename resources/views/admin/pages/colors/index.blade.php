@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Colors Management</h3>
            <div class="card-tools">
                <a href="{{ route('admin.colors.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New Color
                </a>
            </div>
        </div>
        <div class="card-body">
            @include('admin.layouts.partials.__alerts')

            <table class="table table-striped table-hover table-head-bg-primary mt-4">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Color Code</th>
                        <th>Preview</th>
                        <th>Products</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($colors as $index=> $color)
                    <tr>
                        <td>{{ $index + 1}}</td>
                        <td>{{ $color->name }}</td>
                        <td>{{ $color->code }}</td>
                        <td>
                            <span class="color-preview" style="background-color: {{ $color->code }}"></span>
                        </td>
                        <td>{{ $color->products()->count() }}</td>
                        <td>
                           @include('admin.pages.colors.partials.__actions')
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No colors found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            <div class="mt-4">
                {{ $colors->links('admin.layouts.partials.__pagination') }}
            </div>
        </div>
    </div>
</div>
@endsection


<style>
    .color-preview {
        display: inline-block;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 1px solid #ddd;
    }
</style>
