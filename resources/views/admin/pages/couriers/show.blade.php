@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3>Courier Details - {{ $courier->name }}</h3>
            <a href="{{ route('admin.couriers.index') }}" class="btn btn-secondary">Back to List</a>
        </div>

        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th>ID</th>
                    <td>{{ $courier->id }}</td>
                </tr>

                <tr>
                    <th>Name</th>
                    <td>{{ $courier->name }}</td>
                </tr>

                <tr>
                    <th>Base API URL</th>
                    <td>{{ $courier->base_url }}</td>
                </tr>

                <tr>
                    <th>Create Order Endpoint</th>
                    <td>{{ $courier->create_order_endpoint }}</td>
                </tr>

                {{-- Common API fields --}}
                @if($courier->name === 'Steadfast')
                <tr>
                    <th>API Key</th>
                    <td>{{ $courier->api_key ?? '-' }}</td>
                </tr>

                <tr>
                    <th>API Secret</th>
                    <td>{{ $courier->secret_key ?? '-' }}</td>
                </tr>
                @endif
                {{-- Pathao Fields --}}
                @if($courier->name === 'Pathao')
                    <tr>
                        <th>Client ID</th>
                        <td>{{ $courier->client_id }}</td>
                    </tr>
                    <tr>
                        <th>Client Secret</th>
                        <td>{{ $courier->client_secret }}</td>
                    </tr>
                    <tr>
                        <th>Username</th>
                        <td>{{ $courier->username }}</td>
                    </tr>
                    <tr>
                        <th>Password</th>
                        <td>{{ $courier->password }}</td>
                    </tr>
                    <tr>
                        <th>Auth Token Endpoint</th>
                        <td>{{ $courier->auth_endpoint }}</td>
                    </tr>
                @endif

                <tr>
                    <th>Headers</th>
                    <td>
                        @if($courier->headers)
                            <pre>{{ json_encode($courier->headers, JSON_PRETTY_PRINT) }}</pre>
                        @else
                            -
                        @endif
                    </td>
                </tr>

                <tr>
                    <th>Status</th>
                    <td>
                        @if($courier->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </td>
                </tr>

                {{-- <tr>
                    <th>Created At</th>
                    <td>{{ $courier->created_at }}</td>
                </tr>

                <tr>
                    <th>Updated At</th>
                    <td>{{ $courier->updated_at }}</td>
                </tr> --}}
            </table>
        </div>
    </div>
</div>
@endsection
