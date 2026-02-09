@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">

        <div class="card-header mt-4">
            <h3 class="card-title mt-4">Fraud Checker</h3>
        </div>
    {{-- Alerts --}}
    @include('admin.layouts.partials.__alerts')

    {{-- ================= SEARCH BOX ================= --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.courier.check') }}" style="width: 50%; background: none; border: none;">
                @csrf
                <div class="row align-items-end">
                    <div class="col-md-7">
                        <label class="font-weight-bold">Customer Phone Number</label>
                        <input type="text"
                               name="phone"
                               value="{{ old('phone', $phone ?? '') }}"
                               class="form-control"
                               placeholder="01XXXXXXXXX"
                               required>
                    </div>
                    <div class="col-md-5">
                        <button type="submit" class="btn btn-primary btn-block mt-4">
                            <i class="fas fa-search"></i> Check Number
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    {{-- ================= END SEARCH BOX ================= --}}

    {{-- ================= PAGE HEADER ================= --}}
    <div class="row mb-3">
        <div class="col-md-12">
            <h3 class="text-primary">
                <i class="fas fa-truck"></i> Courier Customer Report
            </h3>
        </div>
    </div>
    {{-- ================= END PAGE HEADER ================= --}}

    {{-- ================= SUMMARY CARDS ================= --}}
    @if(!empty($response) && isset($response['data']['summary']))
    <div class="row mb-4">

        <div class="col-md-3">
            <div class="card bg-info text-white shadow">
                <div class="card-body text-center">
                    <h6>Total Parcel</h6>
                    <h2>{{ $response['data']['summary']['total_parcel'] }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-success text-white shadow">
                <div class="card-body text-center">
                    <h6>Success Parcel</h6>
                    <h2>{{ $response['data']['summary']['success_parcel'] }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-danger text-white shadow">
                <div class="card-body text-center">
                    <h6>Cancelled Parcel</h6>
                    <h2>{{ $response['data']['summary']['cancelled_parcel'] }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-warning text-white shadow">
                <div class="card-body text-center">
                    <h6>Success Ratio</h6>
                    <h2>{{ $response['data']['summary']['success_ratio'] }}%</h2>
                </div>
            </div>
        </div>

    </div>
    @endif
    {{-- ================= END SUMMARY CARDS ================= --}}

    {{-- ================= COURIER CARDS ================= --}}
    @if(!empty($response) && isset($response['data']))
    <div class="row">

        @foreach($response['data'] as $key => $courier)
            @if($key !== 'summary')
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow border-0">

                    <div class="card-header bg-light text-center">
                        <img src="{{ $courier['logo'] }}"
                             alt="{{ $courier['name'] }}"
                             style="height:40px">
                        <h5 class="mt-2 mb-0">{{ $courier['name'] }}</h5>
                    </div>

                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Total Parcel</span>
                                <strong>{{ $courier['total_parcel'] }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between text-success">
                                <span>Success Parcel</span>
                                <strong>{{ $courier['success_parcel'] }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between text-danger">
                                <span>Cancelled Parcel</span>
                                <strong>{{ $courier['cancelled_parcel'] }}</strong>
                            </li>
                        </ul>
                    </div>

                    <div class="card-footer text-center">
                        <span class="badge
                            {{ $courier['success_ratio'] >= 80 ? 'badge-success' : 'badge-warning' }}">
                            Success Ratio : {{ $courier['success_ratio'] }}%
                        </span>
                    </div>

                </div>
            </div>
            @endif
        @endforeach

    </div>
    @endif
    {{-- ================= END COURIER CARDS ================= --}}

    {{-- ================= EMPTY STATE ================= --}}
    @if(empty($response))
        <div class="text-center text-muted mt-5">
            <i class="fas fa-search fa-2x mb-2"></i>
            <p>Enter a phone number above and click <strong>Check Courier</strong></p>
        </div>
    @endif

</div>
@endsection
