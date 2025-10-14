@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0 text-gray-800">Edit Banner</h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Banner Information</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                        <div class="form-group">
                            <label for="title">Banner Title *</label>
                            <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $banner->title) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="page_type">Page Type *</label>
                            <select name="page_type" id="page_type" class="form-control" required>
                                <option value="home" {{ $banner->page_type == 'home' ? 'selected' : '' }}>Home Page</option>
                                <option value="offer" {{ $banner->page_type == 'offer' ? 'selected' : '' }}>Offer Page</option>
                                <option value="campaign" {{ $banner->page_type == 'campaign' ? 'selected' : '' }}>Campaign Page</option>
                            </select>
                        </div>

                     <div class="form-group" id="position-container" style="{{ in_array($banner->page_type, ['offer', 'campaign']) ? '' : 'display: none;' }}">
                        <label for="position">Position *</label>
                        <select name="position" id="position" class="form-control">
                            <option value="left" {{ $banner->position == 'left' ? 'selected' : '' }}>Left Side</option>
                            <option value="right" {{ $banner->position == 'right' ? 'selected' : '' }}>Right Side</option>
                        </select>
                    </div>
                    {{-- <div class="col-md-6">
                        <div class="form-group">
                            <label for="order">Display Order</label>
                            <input type="number" name="order" id="order" class="form-control" value="{{ old('order', $banner->order) }}">
                        </div>
                    </div> --}}
                <div class="form-group">
                    <label>Current Image</label><br>
                    <img src="{{$banner->image }}" alt="{{ $banner->title }}" style="max-height: 150px;" class="mb-2">
                </div>

                <div class="form-group">
                    <label for="image">Change Banner Image</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="image" name="image" accept="image/*">
                        <label class="custom-file-label" for="image">Choose new file...</label>
                    </div>
                    <small class="form-text text-muted">Leave blank to keep current image</small>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ $banner->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Active Banner
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Banner
                </button>
            </form>
        </div>
    </div>
</div>
@endsection


<script>
    $(document).ready(function() {
        // Show/hide position field based on page type
        $('#page_type').change(function() {
            if ($(this).val() === 'offer' || $(this).val() === 'campaign') {
                $('#position-container').show();
                $('#position').prop('required', true);
            } else {
                $('#position-container').hide();
                $('#position').prop('required', false);
            }
        });

        // Show filename for file input
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    });
</script>
