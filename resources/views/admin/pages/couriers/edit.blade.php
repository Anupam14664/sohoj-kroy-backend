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

                <!-- Courier Name -->
                <div class="form-group">
                    <label for="name">Courier Name *</label>
                    <input type="text" name="name" id="name" class="form-control" required value="{{ old('name', $courier->name) }}">
                </div>

                <!-- Merchant ID -->
                <div class="form-group">
                    <label for="merchant_id">Merchant ID *</label>
                    <input type="number" name="merchant_id" id="merchant_id" class="form-control" required value="{{ old('merchant_id', $courier->merchant_id) }}">
                </div>

                <!-- Base URL -->
                <div class="form-group">
                    <label for="base_url">Base API URL *</label>
                    <input type="url" name="base_url" id="base_url" class="form-control" required value="{{ old('base_url', $courier->base_url) }}">
                </div>

                <!-- Create Order Endpoint -->
                <div class="form-group">
                    <label for="create_order_endpoint">Create Order Endpoint *</label>
                    <input type="text" name="create_order_endpoint" id="create_order_endpoint" class="form-control" required value="{{ old('create_order_endpoint', $courier->create_order_endpoint) }}">
                </div>

                <!-- Authentication Fields -->
                <div class="form-group">
                    <label for="api_key">API Key</label>
                    <input type="text" name="api_key" id="api_key" class="form-control" value="{{ old('api_key', $courier->api_key) }}">
                </div>

                <div class="form-group">
                    <label for="secret_key">Secret Key</label>
                    <input type="text" name="secret_key" id="secret_key" class="form-control" value="{{ old('secret_key', $courier->secret_key) }}">
                </div>

                <div class="form-group">
                    <label for="client_id">Client ID</label>
                    <input type="text" name="client_id" id="client_id" class="form-control" value="{{ old('client_id', $courier->client_id) }}">
                </div>

                <div class="form-group">
                    <label for="client_secret">Client Secret</label>
                    <input type="text" name="client_secret" id="client_secret" class="form-control" value="{{ old('client_secret', $courier->client_secret) }}">
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control" value="{{ old('username', $courier->username) }}">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="text" name="password" id="password" class="form-control" value="{{ old('password', $courier->password) }}">
                </div>

                <div class="form-group">
                    <label for="auth_endpoint">Auth Endpoint</label>
                    <input type="text" name="auth_endpoint" id="auth_endpoint" class="form-control" value="{{ old('auth_endpoint', $courier->auth_endpoint) }}">
                </div>

                <!-- Optional Headers -->
                <div class="form-group">
                    <label for="headers">Optional Headers (JSON)</label>
                    <textarea name="headers" id="headers" class="form-control" rows="3">{{ old('headers', $courier->headers) }}</textarea>
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label for="is_active">Status</label>
                    <select name="is_active" id="is_active" class="form-control">
                        <option value="1" {{ old('is_active', $courier->is_active) == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active', $courier->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Buttons -->
                <button type="submit" class="btn btn-primary mt-3">Update Courier</button>
                <a href="{{ route('admin.couriers.index') }}" class="btn btn-secondary mt-3">Cancel</a>

            </form>
        </div>
    </div>
</div>
@endsection
