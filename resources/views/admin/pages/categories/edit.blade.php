@extends('admin.layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Edit Category: {{ $category->name }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.categories.update', $category->slug) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.pages.categories.partials.__form', ['category' => $category])

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Category
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-light mx-4">
                        <i class="fas fa-arrow-left"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // File input label update
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        var fileName = document.getElementById("image").files[0].name;
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });
</script>
@endsection