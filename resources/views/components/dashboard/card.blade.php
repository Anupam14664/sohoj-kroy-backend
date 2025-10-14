@props(['title', 'value', 'icon', 'color'])

<div class="col-md-4 col-lg-3">
    <div class="card text-white {{ $color }} shadow-sm h-100">
        <div class="card-body d-flex align-items-center justify-content-between">
            <div>
                <h6 class="text-uppercase fw-bold">{{ $title }}</h6>
                <h4 class="fw-bold">{{ $value }}</h4>
            </div>
            <i class="{{ $icon }} fa-2x opacity-75"></i>
        </div>
    </div>
</div>
