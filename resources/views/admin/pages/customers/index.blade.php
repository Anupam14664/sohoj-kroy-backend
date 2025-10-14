@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Customer List</h4>
            {{-- <div>
                <button id="exportSelectedBtn" class="btn btn-success btn-sm" disabled>
                    <i class="fas fa-file-export"></i> Export Selected
                </button>
                <button id="exportAllBtn" class="btn btn-primary btn-sm">
                    <i class="fas fa-file-export"></i> Export All
                </button>
            </div> --}}
        </div>
        <div class="card-body">
                    <div class="table-responsive">
                @php
                    $districts = config('bd_location');
                    $selDist = request('district'); // Filter value
                    $selThana = request('thana');   // Filter value
                @endphp
                <form method="GET" action="{{ route('admin.customers.index') }}" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <input type="text" name="phone" class="form-control" placeholder="Phone" value="{{ request('phone') }}">
                    </div>
                   <div class="col-md-3">
                        <select id="district" name="district" class="form-control">
                            <option value="">Select District</option>
                            @foreach($districts as $d => $thanas)
                                <option value="{{ $d }}" {{ $selDist == $d ? 'selected' : '' }}>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select id="thana" name="thana" class="form-control" {{ $selDist ? '' : 'disabled' }}>
                            <option value="">Select Thana</option>
                            @if($selDist && isset($districts[$selDist]))
                                @foreach($districts[$selDist] as $th)
                                    <option value="{{ $th }}" {{ $selThana == $th ? 'selected' : '' }}>{{ $th }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </form>

                <div class="row">
                    <div class="card-tools d-flex justify-content-between">
                        <div class="mt-4 ">
                            <button id="exportSelectedBtn" class="btn btn-primary btn-sm ml-4">
                                <i class="fas fa-file-export"></i> Export Excel
                            </button>
                            {{-- <button id="exportAllBtn" class="btn btn-primary btn-sm">
                                <i class="fas fa-file-export"></i> Export All
                            </button> --}}
                        </div>
                        <div class=" col-md-3">
                            <form method="GET" action="{{ route('admin.customers.index') }}" class="form-inline">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" class="form-control px-1"
                                           placeholder="Search..."
                                           value="{{ request('search') }}"
                                           title="For ID search, use exact ID number (e.g. 101)">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary ">
                                            <i class="fas fa-search py-1"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <form id="exportForm" action="{{ route('admin.customers.export') }}" method="POST">
                    @csrf
                    <table class="table table-striped table-hover table-head-bg-primary mt-4">
                        <thead class="thead-light">
                            <tr>
                                <th width="40px">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th class="text-center">Orders</th>
                                <th class="text-center">Products</th>
                                <th class="text-right">Total Spent</th>
                                <th class="text-center">Last Order</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected_customers[]" value="{{ $customer['phone'] }}" class="customer-checkbox">
                                </td>
                                <td>{{ $customer['customer_id'] }}</td>
                                <td>{{ $customer['name'] }}</td>
                                <td>{{ $customer['phone'] }}</td>
                                <td>{{ Str::limit($customer['primary_address'], 30) }}</td>
                                <td class="text-center">{{ $customer['order_count'] }}</td>
                                <td class="text-center">{{ $customer['total_products'] }}</td>
                                <td class="text-right">&#2547;{{ $customer['total_spent'] }}</td>
                                <td class="text-center">{{ $customer['last_order_at'] }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.customers.orders_detail', ['phone' => $customer['phone']]) }}"
                                    class="btn btn-sm btn-info" title="View Orders">
                                        <i class="fas fa-list"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center">No customers found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <input type="hidden" name="export_type" id="exportType">
                    <input type="hidden" name="phone_filter" value="{{ request('phone') }}">
                    <input type="hidden" name="district_filter" value="{{ request('district') }}">
                    <input type="hidden" name="thana_filter" value="{{ request('thana') }}">
                    <input type="hidden" name="search_filter" value="{{ request('search') }}">
                </form>
            </div>
            <div class="card-footer clearfix">
                <div class="mt-4">
                    {{ $customers->links('admin.layouts.partials.__pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Select all checkbox
        $('#selectAll').change(function() {
            $('.customer-checkbox').prop('checked', $(this).prop('checked'));
            toggleExportButton();
        });

        // Individual checkbox change
        $('.customer-checkbox').change(function() {
            if (!$(this).prop('checked')) {
                $('#selectAll').prop('checked', false);
            }
            toggleExportButton();
        });

        // Toggle export button based on selection
        function toggleExportButton() {
            const anyChecked = $('.customer-checkbox:checked').length > 0;
            $('#exportSelectedBtn').prop('disabled', !anyChecked);
        }

        // Export selected customers
        $('#exportSelectedBtn').click(function() {
            $('#exportType').val('selected');
            $('#exportForm').submit();
        });

        // Export all customers (with current filters)
        $('#exportAllBtn').click(function() {
            $('#exportType').val('all');
            $('#exportForm').submit();
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
        background: none !important;
        width: 100% !important;
        border: none !important;
        margin-bottom: -20px !important;
    }
    .card-tools form input{
        background: none !important;
        padding-right: -30px !important;
        border-radius: 6px !important;
    }
    /* .container form .input-group-append i{
        margin-left: -40px;
        margin-top: 6px;
    } */
</style>
<script>
    const districts = @json($districts); // Config data

    $(document).ready(function() {
        $('#district').change(function() {
            const selectedDistrict = $(this).val();
            const $thana = $('#thana');

            $thana.empty().append('<option value="">Select Thana</option>');

            if(selectedDistrict && districts[selectedDistrict]) {
                districts[selectedDistrict].forEach(function(th) {
                    $thana.append('<option value="'+th+'">'+th+'</option>');
                });
                $thana.prop('disabled', false);
            } else {
                $thana.prop('disabled', true);
            }
        });
    });
</script>
