@extends('admin.layouts.app')

@section('title', 'Create Banner')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Create New Banner</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" id="banner-form">
                @csrf

                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                    @error('title')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="image">Image *</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input @error('image') is-invalid @enderror" id="image" name="image" required>
                        <label class="custom-file-label" for="image">Choose image (jpeg,png,jpg,gif, max:2MB)</label>
                    </div>
                    @error('image')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="page_type">Page Type *</label>
                    <select name="page_type" id="page_type" class="form-control @error('page_type') is-invalid @enderror" required>
                        <option value="">Select Page Type</option>
                        <option value="home" {{ old('page_type') == 'home' ? 'selected' : '' }}>Home Page</option>
                        <option value="offer" {{ old('page_type') == 'offer' ? 'selected' : '' }}>Offer Page</option>
                        <option value="campaign" {{ old('page_type') == 'campaign' ? 'selected' : '' }}>Campaign Page</option>
                    </select>
                    @error('page_type')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group" id="position-group" style="display: none;">
                    <label for="position">Position *</label>
                    <select name="position" id="position" class="form-control @error('position') is-invalid @enderror">
                        <option value="">Select Position</option>
                        <option value="left" {{ old('position') == 'left' ? 'selected' : '' }}>Left</option>
                        <option value="right" {{ old('position') == 'right' ? 'selected' : '' }}>Right</option>
                    </select>
                    @error('position')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="order">Order</label>
                    <input type="number" name="order" id="order" class="form-control @error('order') is-invalid @enderror" value="{{ old('order', 0) }}">
                    @error('order')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">Active</label>
                    </div>
                    @error('is_active')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Save Banner</button>
                    <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Debug element existence
        console.log('Page type element:', $('#page_type').length);
        console.log('Position group:', $('#position-group').length);
        console.log('Form element:', $('#banner-form').length);

        function handlePageTypeChange() {
            const pageType = $('#page_type').val();
            console.log('Page type changed to:', pageType);

            if (pageType === 'home') {
                $('#position-group').hide();
                $('#position').removeAttr('required').val('');
            } else {
                $('#position-group').show();
                $('#position').attr('required', 'required');
            }
        }

        // Initialize on load
        handlePageTypeChange();

        // Bind change event
        $('#page_type').on('change', handlePageTypeChange);

        // File input handling
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });

        // If there's old input, trigger change
        @if(old('page_type'))
            $('#page_type').val('{{ old('page_type') }}').trigger('change');
            $('#position').val('{{ old('position') }}');
        @endif
    });
</script>
