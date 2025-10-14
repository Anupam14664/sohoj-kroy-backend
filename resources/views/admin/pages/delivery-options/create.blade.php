@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3>Create New Delivery Options</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.delivery-options.store') }}" method="POST">
                @csrf
                @include('admin.pages.delivery-options.partials.__form')

                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.delivery-options.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection