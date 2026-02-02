@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Create Courier</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.couriers.store') }}" method="POST">
                @csrf

                <!-- Courier Name -->
                <div class="form-group">
                    <label for="name">Courier Name *</label>
                    <input type="text" name="name" id="name" class="form-control" required value="{{ old('name') }}">
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Merchant ID -->
                <div class="form-group">
                    <label for="merchant_id">Merchant ID *</label>
                    <input type="number" name="merchant_id" id="merchant_id" class="form-control" required value="{{ old('merchant_id') }}">
                    @error('merchant_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Type -->
                <div class="form-group">
                    <label for="type">Courier Type *</label>
                    <select name="type" id="type" class="form-control" required>
                        <option value="">Select Type</option>
                        <option value="pathao" {{ old('type') == 'pathao' ? 'selected' : '' }}>Pathao</option>
                        <option value="steadfast" {{ old('type') == 'steadfast' ? 'selected' : '' }}>Steadfast</option>
                    </select>
                    @error('type')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Base URL -->
                <div class="form-group">
                    <label for="base_url">Base API URL *</label>
                    <input type="url" name="base_url" id="base_url" class="form-control" required value="{{ old('base_url') }}">
                    @error('base_url')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Create Order Endpoint -->
                <div class="form-group">
                    <label for="create_order_endpoint">Create Order Endpoint</label>
                    <input type="text" name="create_order_endpoint" id="create_order_endpoint" class="form-control" value="{{ old('create_order_endpoint') }}">
                    @error('create_order_endpoint')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Pathao Fields -->
                <div id="pathao-fields" style="display: none;">
                    <div class="form-group">
                        <label for="store_id">Store ID *</label>
                        <input type="text" name="store_id" id="store_id" class="form-control" value="{{ old('store_id') }}">
                        @error('store_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="client_id">Client ID *</label>
                        <input type="text" name="client_id" id="client_id" class="form-control" value="{{ old('client_id') }}">
                        @error('client_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="client_secret">Client Secret *</label>
                        <input type="text" name="client_secret" id="client_secret" class="form-control" value="{{ old('client_secret') }}">
                        @error('client_secret')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" name="username" id="username" class="form-control" value="{{ old('username') }}">
                        @error('username')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="text" name="password" id="password" class="form-control" value="{{ old('password') }}">
                        @error('password')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="auth_endpoint">Auth Endpoint *</label>
                        <input type="text" name="auth_endpoint" id="auth_endpoint" class="form-control" value="{{ old('auth_endpoint') }}">
                        @error('auth_endpoint')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Steadfast Fields -->
                <div id="steadfast-fields" style="display: none;">
                    <div class="form-group">
                        <label for="api_key">API Key *</label>
                        <input type="text" name="api_key" id="api_key" class="form-control" value="{{ old('api_key') }}">
                        @error('api_key')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="secret_key">Secret Key *</label>
                        <input type="text" name="secret_key" id="secret_key" class="form-control" value="{{ old('secret_key') }}">
                        @error('secret_key')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Optional Headers -->
                <div class="form-group">
                    <label for="headers">Optional Headers (JSON)</label>
                    <textarea name="headers" id="headers" class="form-control" rows="3">{{ old('headers') }}</textarea>
                    @error('headers')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label for="is_active">Status</label>
                    <select name="is_active" id="is_active" class="form-control">
                        <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active', 1) == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('is_active')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Buttons -->
                <button type="submit" class="btn btn-primary mt-3">Save Courier</button>
                <a href="{{ route('admin.couriers.index') }}" class="btn btn-secondary mt-3">Cancel</a>

            </form>
        </div>
    </div>
</div>

<script>
    function toggleFields() {
        const type = document.getElementById('type').value;
        document.getElementById('pathao-fields').style.display = type === 'pathao' ? 'block' : 'none';
        document.getElementById('steadfast-fields').style.display = type === 'steadfast' ? 'block' : 'none';
    }

    document.getElementById('type').addEventListener('change', toggleFields);
    window.onload = toggleFields;
</script>
@endsection
