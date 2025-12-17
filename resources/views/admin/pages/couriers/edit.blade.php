@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Courier - {{ $courier->name }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.couriers.update', $courier->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Courier Name *</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $courier->name) }}">
                </div>

                <div class="form-group">
                    <label for="base_url">Base API URL *</label>
                    <input type="url" name="base_url" class="form-control" required value="{{ old('base_url', $courier->base_url) }}">
                </div>

                <div class="form-group">
                    <label for="create_order_endpoint">Create Order Endpoint *</label>
                    <input type="text" name="create_order_endpoint" class="form-control" required value="{{ old('create_order_endpoint', $courier->create_order_endpoint) }}">
                </div>

                <div class="form-group">
                    <label for="api_key">API Key *</label>
                    <input type="text" name="api_key" class="form-control" required value="{{ old('api_key', $courier->api_key) }}">
                </div>

                <div class="form-group">
                    <label for="secret_key">API Secret *</label>
                    <input type="text" name="secret_key" class="form-control" required value="{{ old('secret_key', $courier->secret_key) }}">
                </div>

                <div class="form-group">
                    <label for="headers">Optional Headers (JSON)</label>
                    <textarea name="headers" class="form-control" rows="3">{{ old('headers', $courier->headers) }}</textarea>
                </div>

                <div class="form-group">
                    <label for="is_active">Status</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ isset($courier) && $courier->is_active ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ isset($courier) && !$courier->is_active ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Update Courier</button>
                <a href="{{ route('admin.couriers.index') }}" class="btn btn-secondary mt-3">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
