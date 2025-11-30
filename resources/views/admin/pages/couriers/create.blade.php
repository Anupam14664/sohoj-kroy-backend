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

                {{-- Courier Name --}}
                <div class="form-group">
                    <label for="name">Courier Name *</label>
                    <select name="name" id="courier_name" class="form-control" required>
                        <option value="">-- Select Courier --</option>
                        <option value="Steadfast">Steadfast</option>
                        <option value="Pathao">Pathao</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="base_url">Base API URL *</label>
                    <input type="url" name="base_url" class="form-control" required value="{{ old('base_url') }}">
                </div>

                <div class="form-group">
                    <label for="create_order_endpoint">Create Order Endpoint *</label>
                    <input type="text" name="create_order_endpoint" class="form-control" required value="{{ old('create_order_endpoint') }}">
                </div>

                 {{-- Common API fields --}}
                <div id="steadfast_fields" style="display:block;">
                    <div class="form-group">
                        <label for="api_key">API Key</label>
                        <input type="text" name="api_key" class="form-control" value="{{ old('api_key') }}">
                    </div>

                    <div class="form-group">
                        <label for="secret_key">API Secret</label>
                        <input type="text" name="secret_key" class="form-control" value="{{ old('secret_key') }}">
                    </div>

                </div>
                {{-- ===================== --}}
                {{-- PATHAO EXTRA FIELDS --}}
                {{-- ===================== --}}
                <div id="pathao_fields" style="display:none;">

                    <div class="form-group">
                        <label for="client_id">Client ID</label>
                        <input type="text" name="client_id" class="form-control" value="{{ old('client_id') }}">
                    </div>

                    <div class="form-group">
                        <label for="client_secret">Client Secret</label>
                        <input type="text" name="client_secret" class="form-control" value="{{ old('client_secret') }}">
                    </div>

                    <div class="form-group">
                        <label for="username">Pathao Username</label>
                        <input type="text" name="username" class="form-control" value="{{ old('username') }}">
                    </div>

                    <div class="form-group">
                        <label for="password">Pathao Password</label>
                        <input type="text" name="password" class="form-control" value="{{ old('password') }}">
                    </div>

                    <div class="form-group">
                        <label for="auth_endpoint">Auth Token Endpoint</label>
                        <input type="text" name="auth_endpoint" class="form-control" value="{{ old('auth_endpoint') }}">
                    </div>

                </div>

                {{-- Optional Headers --}}
                <div class="form-group">
                    <label for="headers">Optional Headers (JSON)</label>
                    <textarea name="headers" class="form-control" rows="3">{{ old('headers') }}</textarea>
                </div>

                {{-- Status --}}
                <div class="form-group">
                    <label for="is_active">Status</label>
                    <select name="is_active" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Save Courier</button>
                <a href="{{ route('admin.couriers.index') }}" class="btn btn-secondary mt-3">Cancel</a>
            </form>
        </div>
    </div>
</div>


<script>
    function toggleFields() {
        const courier = document.getElementById('courier_name').value;

        if (courier === 'Pathao') {
            document.getElementById('pathao_fields').style.display = 'block';
            document.getElementById('steadfast_fields').style.display = 'none';
        } else {
            document.getElementById('pathao_fields').style.display = 'none';
        }
    }

    document.getElementById('courier_name').addEventListener('change', toggleFields);
</script>


@endsection

