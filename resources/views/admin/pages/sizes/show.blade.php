@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3>Size Details: {{ $size->name }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th>Name</th>
                            <td>{{ $size->name }}</td>
                        </tr>
                        <tr>
                            <th>Products Using This Size</th>
                            <td>{{ $size->products->count() }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    @if($size->products->count() > 0)
                        <h5>Associated Products</h5>
                        <ul>
                            @foreach($size->products as $product)
                                <li>{{ $product->name }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('admin.sizes.edit', $size->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('admin.sizes.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>
@endsection