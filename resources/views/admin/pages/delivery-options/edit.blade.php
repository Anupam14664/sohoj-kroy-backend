@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3>Edit Delivery Options: {{ $deliveryOption->name }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.delivery-options.update', $deliveryOption->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.pages.delivery-options.partials.__form', ['deliveryOption' => $deliveryOption])

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.delivery-options.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
