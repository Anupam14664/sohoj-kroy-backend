@extends('admin.layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>Edit Homepage Section {{ $homepageSection->position }}</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.homepage-sections.update', $homepageSection->id) }}" method="POST" id="sectionForm">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Section Name</label>
                    <input type="text" class="form-control" id="name" name="name"
                           value="{{ old('name', $homepageSection->name) }}" required>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                               {{ $homepageSection->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active Section</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Select Categories</label>
                    <select name="categories[]" id="categories" class="form-control select2" multiple="multiple" style="width: 100%;" required>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ in_array($category->id, $selectedCategories) ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('categories')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    Update Section
                </button>
            </form>
        </div>
    </div>
</div>
@endsection


<!-- Include Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Initialize Select2 -->
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select categories...",
            allowClear: true,
            width: '100%'
        });

        // Form submission handling
        $('#sectionForm').on('submit', function(e) {
            // Client-side validation
            if ($('#categories').val() === null || $('#categories').val().length === 0) {
                e.preventDefault();
                alert('Please select at least one category');
                $('#categories').next('.select2-container').find('.select2-selection').focus();
                return false;
            }
            return true;
        });
    });
</script>

<!-- Include Select2 JS after initialization script -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
