@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">All Orders</h3>
                <button class="btn btn-sm btn-secondary text-white"><a href="{{ route('admin.orders.create') }}" class=" text-white flex-grow-1">Create New Order</a></button>
            </div>
        </div>
        <div class="card-body">
            @include('admin.modal.confirmationmodal')
            @include('admin.modal.successmodal')

            <div id="alert-container">
                @include('admin.layouts.partials.__alerts')
            </div>

            <div class="row mb-4">
                <div class="col-md-12 d-flex align-items-center">
                    {{-- Existing Filters --}}
                    <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3 flex-grow-1" id="autoSubmitForm">
                        <div class="col-md-2">
                            <input type="date" name="date_from" class="form-control auto-submit" value="{{ $dateFrom }}">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_to" class="form-control auto-submit" value="{{ $dateTo }}">
                        </div>
                        @if($status === 'delivered')
                            <div class="col-md-2">
                                <input type="text" name="district" class="form-control auto-submit" placeholder="District" value="{{ $district }}">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="thana" class="form-control auto-submit" placeholder="Thana" value="{{ $thana }}">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="product_search" class="form-control auto-submit" placeholder="Product Name / SKU" value="{{ $productSearch }}">
                            </div>
                        @endif
                        <div class="col-md-2">
                            <select name="status" class="form-control auto-submit">
                                <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Status</option>
                                <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="hold" {{ $status == 'hold' ? 'selected' : '' }}>Hold</option>
                                <option value="processing" {{ $status == 'processing' ? 'selected' : '' }}>Order Confirmed</option>
                                <option value="shipped" {{ $status == 'shipped' ? 'selected' : '' }}>Ready to Shipped</option>
                                <option value="courier_delivered" {{ $status == 'courier_delivered' ? 'selected' : '' }}>Courier Delivered</option>
                                <option value="delivered" {{ $status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary d-none">Filter</button>
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>

                    {{-- PDF Export Button --}}
                    @if(in_array($status, ['processing', 'shipped', 'courier_delivered', 'delivered']))
                    <div class="col-md-3 ms-2 d-flex align-items-center" style="margin-top:-30px;">
                        <button type="button" class="btn btn-success" id="exportPdfBtn">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Bulk Actions Form -->
            <form id="bulkActionForm" method="POST">
                @csrf
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        {{-- <button type="button" class="btn btn-sm btn-success" id="selectAllBtn">
                            <i class="fas fa-check-circle"></i> Select All
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" id="deselectAllBtn">
                            <i class="fas fa-times-circle"></i> Deselect All
                        </button> --}}
                        @if($status === 'cancelled')
                        <button type="button" class="btn btn-sm btn-danger" id="bulkDeleteBtn">
                            <i class="fas fa-trash"></i> Delete Selected
                        </button>
                        @endif
                    </div>
                    @if(in_array($status, ['processing', 'shipped', 'courier_delivered', 'delivered']))
                    <button type="button" class="btn btn-sm btn-primary" id="exportBtn">
                        <i class="fas fa-file-excel"></i> Export Selected
                    </button>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover table-head-bg-primary mt-4" width="100%" cellspacing="0">
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
                                @if(in_array($status, ['courier_delivered', 'delivered']))
                                    <th>Track Order</th>
                                @endif
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $index => $order)
                                @if($order->status === 'incomplete')
                                    @continue
                                @endif
                            <tr>
                                <td>
                                    @if(in_array($order->status, ['processing', 'shipped', 'courier_delivered', 'delivered']) || $order->status === 'cancelled')
                                        <input type="checkbox" name="order_ids[]" value="{{ $order->id }}"
                                               class="order-checkbox"
                                               data-status="{{ $order->status }}"
                                               data-date="{{ $order->created_at->format('Y-m-d') }}">
                                    @else
                                        <input type="checkbox" disabled class="order-checkbox">
                                    @endif
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
                                @if(in_array($status, ['shipped', 'courier_delivered', 'delivered']))
                                    <td>
                                        @php
                                            $trackUrl = $order->tracking_code
                                                ? "https://steadfast.com.bd/t/{$order->tracking_code}"
                                                : ($order->custom_link ?? null);
                                        @endphp

                                        @if($trackUrl)
                                            <a href="{{ $trackUrl }}" target="_blank" class="btn btn-sm btn-info">
                                                Track
                                            </a>
                                        @endif
                                    </td>
                                @endif
                                <td>
                                    @include('admin.pages.orders.partials.__actions')
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center">No Order found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
        <div class="card-footer clearfix">
            <div class="mt-4">
                {{ $orders->appends([
                    'status' => $status,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo
                ])->links('admin.layouts.partials.__pagination') }}
            </div>
        </div>
    </div>
</div>
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {


    $('#selectAllCheckbox').on('change', function() {
        const checked = $(this).prop('checked');
        $('.order-checkbox:not(:disabled)').prop('checked', checked);


        if(checked) {
            if($('#allFilteredInput').length === 0) {
                $('<input>').attr({
                    type: 'hidden',
                    id: 'allFilteredInput',
                    name: 'all_filtered',
                    value: true
                }).appendTo('#bulkActionForm');
            }
        } else {
            $('#allFilteredInput').remove();
        }
    });


    $('.order-checkbox').on('change', function() {
        const allChecked = $('.order-checkbox:not(:disabled)').length === $('.order-checkbox:checked:not(:disabled)').length;
        $('#selectAllCheckbox').prop('checked', allChecked);

        if(allChecked) {
            if($('#allFilteredInput').length === 0) {
                $('<input>').attr({
                    type: 'hidden',
                    id: 'allFilteredInput',
                    name: 'all_filtered',
                    value: true
                }).appendTo('#bulkActionForm');
            }
        } else {
            $('#allFilteredInput').remove();
        }
    });


    $('#selectAllBtn').click(function() {
        $('.order-checkbox:not(:disabled)').prop('checked', true);
        $('#selectAllCheckbox').prop('checked', true);

        if($('#allFilteredInput').length === 0) {
            $('<input>').attr({
                type: 'hidden',
                id: 'allFilteredInput',
                name: 'all_filtered',
                value: true
            }).appendTo('#bulkActionForm');
        }
    });

    $('#deselectAllBtn').click(function() {
        $('.order-checkbox:not(:disabled)').prop('checked', false);
        $('#selectAllCheckbox').prop('checked', false);
        $('#allFilteredInput').remove();
    });


    $('#exportBtn').click(function() {
        const selectedOrders = $('.order-checkbox:checked:not(:disabled)');

        const form = $('<form>', {
            method: 'POST',
            action: "{{ route('admin.orders.export') }}",
            target: '_blank'
        });

        form.append($('<input>', { type: 'hidden', name: '_token', value: "{{ csrf_token() }}" }));

        if($('#allFilteredInput').length) {

            form.append($('<input>', { type: 'hidden', name: 'all_filtered', value: true }));
            form.append($('<input>', { type: 'hidden', name: 'status', value: "{{ request('status', 'all') }}" }));
            form.append($('<input>', { type: 'hidden', name: 'date_from', value: "{{ request('date_from') }}" }));
            form.append($('<input>', { type: 'hidden', name: 'date_to', value: "{{ request('date_to') }}" }));
            form.append($('<input>', { type: 'hidden', name: 'district', value: "{{ request('district') }}" }));
            form.append($('<input>', { type: 'hidden', name: 'thana', value: "{{ request('thana') }}" }));
            form.append($('<input>', { type: 'hidden', name: 'product_search', value: "{{ request('product_search') }}" }));
        } else {
            if(selectedOrders.length === 0) {
                alert('Please select at least one order to export');
                return;
            }
            selectedOrders.each(function() {
                form.append($('<input>', { type: 'hidden', name: 'order_ids[]', value: $(this).val() }));
            });
        }

        $('body').append(form);
        form.submit();
    });



    $('#exportAllBtn').click(function() {
        const dateFrom = $('input[name="date_from"]').val();
        const dateTo = $('input[name="date_to"]').val();
        const status = $('select[name="status"]').val();
        const district = $('input[name="district"]').val();
        const thana = $('input[name="thana"]').val();
        const productSearch = $('input[name="product_search"]').val();

        const form = $('<form>', {
            method: 'POST',
            action: "{{ route('admin.orders.export') }}",
            target: '_blank'
        });

        form.append($('<input>', { type: 'hidden', name: '_token', value: "{{ csrf_token() }}" }));
        form.append($('<input>', { type: 'hidden', name: 'date_from', value: dateFrom }));
        form.append($('<input>', { type: 'hidden', name: 'date_to', value: dateTo }));
        form.append($('<input>', { type: 'hidden', name: 'status', value: status }));
        form.append($('<input>', { type: 'hidden', name: 'district', value: district }));
        form.append($('<input>', { type: 'hidden', name: 'thana', value: thana }));
        form.append($('<input>', { type: 'hidden', name: 'product_search', value: productSearch }));
        form.append($('<input>', { type: 'hidden', name: 'all_filtered', value: true }));

        $('body').append(form);
        form.submit();
    });


    $('#bulkDeleteBtn').click(function() {
        const selectedOrders = $('.order-checkbox:checked:not(:disabled)');

        if(selectedOrders.length === 0) {
            alert('Please select at least one order to delete');
            return;
        }

        $('#confirmModal').modal('show');

        $('#confirmModal .modal-title').text('Are You Sure?');
        $('#confirmModal .modal-body p').html(`Do you really want to delete <strong>${selectedOrders.length}</strong> orders? This process cannot be undone.`);

        $('#confirmButton').off('click');

        $('#confirmButton').on('click', function() {
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
                beforeSend: function() {
                    $('#confirmButton').html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
                },
                success: function(response) {
                    $('#confirmModal').modal('hide');
                    $('#successModal').modal('show');
                    $('#successModal .modal-title').text('Success');
                    $('#successModal .modal-body p').html(`<strong>${selectedOrders.length}</strong> orders have been deleted successfully.`);
                    setTimeout(function() { location.reload(); }, 2000);
                },
                error: function(xhr) {
                    $('#confirmModal').modal('hide');
                    $('#successModal').modal('show');
                    $('#successModal .modal-title').text('Error');
                    $('#successModal .modal-body p').html(`<div class="alert alert-danger">${xhr.responseJSON?.message || 'Something went wrong while deleting orders.'}</div>`);
                    $('#confirmButton').html('Delete');
                }
            });
        });
    });


    // ================================
    // 5. Auto-submit Filter Form
    // ================================
    $('.auto-submit').on('change', function() {
        setTimeout(function() {
            $('#filterForm').submit();
        }, 300);
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


<script>
    document.addEventListener('DOMContentLoaded', function() {

        function autoSubmit() {
            clearTimeout(window.autoSubmitTimeout);
            window.autoSubmitTimeout = setTimeout(function() {
                document.getElementById('autoSubmitForm').submit();
            }, 500);
        }

        document.querySelectorAll('.auto-submit').forEach(function(element) {

            if (element.tagName === 'SELECT') {
                element.addEventListener('change', autoSubmit);
            } else {
                element.addEventListener('change', autoSubmit);
                element.addEventListener('keyup', function(e) {
                    if (e.key === 'Enter') {
                        autoSubmit();
                    }
                });
            }
        });
    });
    </script>



<script>
    $(document).ready(function() {
        $('#exportPdfBtn').click(function() {
            const dateFrom = $('input[name="date_from"]').val();
            const dateTo = $('input[name="date_to"]').val();
            const status = $('select[name="status"]').val();
            const district = $('input[name="district"]').val();
            const thana = $('input[name="thana"]').val();
            const productSearch = $('input[name="product_search"]').val();

            const form = $('<form>', {
                method: 'POST',
                action: "{{ route('admin.orders.export.order') }}",
                target: '_blank'
            });

            form.append($('<input>', { type: 'hidden', name: '_token', value: "{{ csrf_token() }}" }));
            form.append($('<input>', { type: 'hidden', name: 'date_from', value: dateFrom }));
            form.append($('<input>', { type: 'hidden', name: 'date_to', value: dateTo }));
            form.append($('<input>', { type: 'hidden', name: 'status', value: status }));
            form.append($('<input>', { type: 'hidden', name: 'district', value: district }));
            form.append($('<input>', { type: 'hidden', name: 'thana', value: thana }));
            form.append($('<input>', { type: 'hidden', name: 'product_search', value: productSearch }));

            $('body').append(form);
            form.submit();
        });
    });
</script>
