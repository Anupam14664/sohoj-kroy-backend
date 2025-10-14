@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3>Color Details: {{ $color->name }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th>Name</th>
                            <td>{{ $color->name }}</td>
                        </tr>
                        <tr>
                            <th>Color Code</th>
                            <td>{{ $color->code }}</td>
                        </tr>
                        <tr>
                            <th>Color Preview</th>
                            <td>
                                <span class="color-preview" style="background-color: {{ $color->code }}"></span>
                            </td>
                        </tr>
                        <tr>
                            <th>Products Using This Color</th>
                            <td>{{ $color->products()->count() }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('admin.colors.edit', $color->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('admin.colors.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .color-preview {
        display: inline-block;
        width: 50px;
        height: 50px;
        border-radius: 4px;
        border: 1px solid #ddd;
    }
</style>
@endsection