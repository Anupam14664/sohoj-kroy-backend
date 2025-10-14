@extends('admin.layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>{{ isset($homepageSection) ? 'Edit' : 'Create' }} Homepage Section</h2>
        </div>
        <div class="card-body">
            <form action="{{ isset($homepageSection) ? route('admin.homepage-sections.update', $homepageSection->id) : route('admin.homepage-sections.store') }}" method="POST">
                @csrf
                @if(isset($homepageSection))
                @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="name" class="form-label">Section Name</label>
                    <input type="text" class="form-control" id="name" name="name"
                           value="{{ old('name', $homepageSection->name ?? '') }}" required>
                </div>

                <div class="mb-3">
                    <label for="position" class="form-label">Position (1-5)</label>
                    <input type="number" class="form-control" id="position" name="position" min="1" max="5"
                           value="{{ old('position', $homepageSection->position ?? '') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Select Categories</label>
                    <select name="categories[]" id="categories" class="form-control select2" multiple required>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ isset($selectedCategories) && in_array($category->id, $selectedCategories) ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    {{ isset($homepageSection) ? 'Update' : 'Create' }} Section
                </button>
            </form>
        </div>
    </div>
</div>


@endsection

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select categories",
            allowClear: true
        });
    });
</script>