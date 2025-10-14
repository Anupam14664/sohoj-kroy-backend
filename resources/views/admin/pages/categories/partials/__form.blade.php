<div class="form-group mb-3">
    <label for="name">Name *</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror"
           id="name" name="name"
           value="{{ old('name', $category->name ?? '') }}" required>
    @error('name')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>

<div class="form-group mb-3">
    <label for="parent_id">Parent Category</label>
    <select class="form-control @error('parent_id') is-invalid @enderror"
            id="parent_id" name="parent_id">
            <option value="">-- No Parent --</option>
        @foreach($parentCategories as $parent)
            <option value="{{ $parent->id }}"
                {{ old('parent_id', $category->parent_id ?? '') == $parent->id ? 'selected' : '' }}>
                {{ $parent->name }}
            </option>
        @endforeach
    </select>
    @error('parent_id')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>

<div class="form-group mb-3">
    <label for="image">Category Image</label>
    <div class="custom-file">
        <input type="file" class="custom-file-input @error('image') is-invalid @enderror"
               id="image" name="image">
        <label class="custom-file-label" for="image">
            Choose file
        </label>
        @error('image')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    @if(isset($category) && $category->image)
        <div class="mt-2">
            <small>Current image:</small>
            <img src="{{ asset('public/storage/'.$category->image) }}" width="50" alt="Category Image" class="img-thumbnail">
        </div>
    @elseif(isset($category))
        <div class="mt-2">
            <small>No image</small>
        </div>
    @endif
</div>