@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Incomplete Orders</h3>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">Back to All Orders</a>
        </div>

        @include('admin.modal.confirmationmodal')
        @include('admin.modal.successmodal')

        <div class="card-body">
            @include('admin.layouts.partials.__alerts')
            <form id="bulkActionForm" method="POST">
                @csrf
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        <button type="button" class="btn btn-sm btn-danger" id="bulkDeleteBtn">
                            <i class="fas fa-trash"></i> Delete Selected
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-head-bg-primary mt-4">
                        <thead>
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="selectAllCheckbox">
                                </th>
                                <th>Order #</th>
                                <th>Image</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>IP Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                            <tr>
                                <td>
                                    <input type="checkbox" class="order-checkbox" value="{{ $order->id }}">
                                </td>
                                <td>{{ $order->order_number }}</td>
                                    <td>
                                        @if($order->items->isNotEmpty() && $order->items[0]->product && $order->items[0]->product->main_image)
                                            <img src="{{ asset('storage/'.$order->items[0]->product->main_image) }}"
                                                alt="{{ $order->items[0]->product->name }}"
                                                width="50"
                                                class="img-thumbnail">
                                        @else
                                            <span class="text-muted">No image</span>
                                        @endif
                                    </td>
                                    <td>{{ $order->name }}</td>
                                    <td>{{ $order->phone }}</td>
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                    <td>&#2547;{{ number_format($order->total, 2) }}</td>
                                    <td>
                                        <span class="badge
                                            @if($order->status == 'delivered') badge-success
                                            @elseif($order->status == 'cancelled') badge-danger
                                            @elseif($order->status == 'hold') badge-info
                                            @elseif($order->status == 'incomplete') badge-secondary
                                            @elseif($order->status == 'courier_delivered') badge-primary
                                            @else badge-warning
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                        </span>
                                    </td>

                                    <td>{{ $order->ip_address }}</td>
                                    <td>
                                        @include('admin.pages.orders.partials.__actions')
                                    </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">No incomplete orders found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
            <div class="mt-3">
                {{ $orders->links('admin.layouts.partials.__pagination') }}
            </div>
        </div>
    </div>
</div>
@endsection


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {

        // select all checkbox
        $('#selectAllCheckbox').on('change', function() {
            $('.order-checkbox').prop('checked', $(this).prop('checked'));
        });

        $('#bulkDeleteBtn').click(function() {
            const selectedOrders = $('.order-checkbox:checked');

            if(selectedOrders.length === 0) {
                alert('Please select at least one order to delete');
                return;
            }

            // set modal text dynamically
            $('#confirmModal .modal-title').text('Are You Sure?');
            $('#confirmModal .modal-body p').html(`Do you really want to delete <strong>${selectedOrders.length}</strong> orders? This cannot be undone.`);

            // show confirmation modal
            $('#confirmModal').modal('show');

            // remove previous click handlers
            $('#confirmButton').off('click').on('click', function() {

                const orderIds = selectedOrders.map(function() {
                    return $(this).val();
                }).get();

                $.ajax({
                    url: "{{ route('admin.orders.bulk-delete') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        order_ids: orderIds
                    },
                    success: function(response) {
                        $('#confirmModal').modal('hide');

                        // remove deleted rows from table
                        selectedOrders.closest('tr').remove();

                        // show success modal
                        $('#successModal .modal-body p').html(response.message);
                        $('#successModal').modal('show');
                    },
                    error: function(xhr) {
                        $('#confirmModal').modal('hide');
                        alert(xhr.responseJSON?.message || 'Something went wrong');
                    }
                });

            });

        });
    });
</script>






<style>
    .color-preview {
        display: inline-block;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 1px solid #ddd;
    }
    .container form{
        width: 100% !important;
        background: none !important;
        border: none !important;
        margin-bottom: 0 !important;
    }
    @media (max-width: 576px) {
        .form-label {
            font-size: 0.75rem;
        }
        .btn-sm i {
            display: none;
        }
    }

    .align-items-end {
        align-items: flex-end;
    }

    .order-checkbox {
        cursor: pointer;
    }

    #selectAllCheckbox {
        cursor: pointer;
    }

    #selectAllCheckbox {
        margin: 0;
        vertical-align: middle;
    }

    .order-checkbox {
        margin: 0;
        vertical-align: middle;
    }

    td:first-child {
        text-align: center;
    }
</style>
