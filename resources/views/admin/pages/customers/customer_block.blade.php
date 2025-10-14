@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Block Customers</h4>
            <div>
                <a href="{{ route('admin.customers.index') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Customers
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5>Block by Phone Number</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.customers.block') }}">
                                @csrf
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="text" name="phone" id="phone" class="form-control"
                                           placeholder="Enter phone number (e.g. 017XXXXXXXX)" required
                                           value="{{ old('phone') }}">
                                    @error('phone')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="phone_reason">Reason (Optional)</label>
                                    <textarea name="reason" id="phone_reason" class="form-control" rows="3">{{ old('reason') }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-ban"></i> Block Phone Number
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5>Block by IP Address</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.customers.block') }}">
                                @csrf
                                <div class="form-group">
                                    <label for="ip_address">IP Address</label>
                                    <input type="text" name="ip_address" id="ip_address" class="form-control"
                                           placeholder="Enter IP address (e.g. 192.168.1.1)" required
                                           value="{{ old('ip_address') }}">
                                    @error('ip_address')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="ip_reason">Reason (Optional)</label>
                                    <textarea name="reason" id="ip_reason" class="form-control" rows="3">{{ old('reason') }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-ban"></i> Block IP Address
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-warning">
                    <h5>Currently Blocked Customers</h5>
                </div>
                <div class="card-body">

                    <form method="GET" action="{{ route('admin.customers.blocked') }}" class="mb-4">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control"
                                   placeholder="Search by phone or IP address"
                                   value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">Search</button>
                                @if(request('search'))
                                    <a href="{{ route('admin.customers.blocked') }}" class="btn btn-secondary">Clear</a>
                                @endif
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-head-bg-primary mt-4">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Value</th>
                                    <th>Reason</th>
                                    <th>Blocked By</th>
                                    <th>Blocked At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($blockedCustomers as $blocked)
                                <tr>
                                    <td>
                                        @if($blocked->phone)
                                            <span class="badge badge-primary">Phone</span>
                                        @elseif($blocked->ip_address)
                                            <span class="badge badge-info">IP</span>
                                        @else
                                            <span class="badge badge-secondary">Unknown</span>
                                        @endif
                                    </td>
                                    <td>{{ $blocked->phone ?? $blocked->ip_address }}</td>
                                    <td>{{ $blocked->reason ?? 'Not specified' }}</td>
                                    <td>{{ $blocked->blocker->name ?? 'ADMIN' }}</td>
                                    <td>{{ $blocked->created_at->format('M d, Y h:i A') }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.customers.unblock') }}" class="d-inline">
                                            @csrf
                                            @method('POST')
                                            <input type="hidden" name="id" value="{{ $blocked->id }}">
                                            <button type="submit" class="btn btn-sm btn-success" title="Unblock">
                                                <i class="fas fa-check"></i> Unblock
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No blocked customers found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $blockedCustomers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
$(document).ready(function() {
    // Confirm before unblocking
    $('form[action="{{ route('admin.customers.unblock') }}"]').submit(function(e) {
        e.preventDefault();
        var form = $(this);

        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to unblock this customer/IP",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, unblock it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.unbind('submit').submit();
            }
        });
    });
});
</script>

<style>
    .card-header h5 {
        margin-bottom: 0;
    }
    .badge {
        font-size: 0.85rem;
        padding: 0.35em 0.65em;
    }
    .table th {
        white-space: nowrap;
    }
    .alert {
        margin-bottom: 20px;
    }
</style>