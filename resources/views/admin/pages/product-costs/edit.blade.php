@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3>Edit Size: {{ $size->display_name ?? $size->name }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.sizes.update', $size->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group mb-3">
                    <label for="type">Size Type *</label>
                    <select class="form-control @error('type') is-invalid @enderror"
                            id="type" name="type" required>
                        <option value="numeric" {{ old('type', $size->type) == 'numeric' ? 'selected' : '' }}>Numeric</option>
                        <option value="text" {{ old('type', $size->type) == 'text' ? 'selected' : '' }}>Text</option>
                    </select>
                    @error('type')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="name">Size Code *</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name', $size->name) }}" required>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="display_name">Display Name</label>
                    <input type="text" class="form-control @error('display_name') is-invalid @enderror"
                           id="display_name" name="display_name"
                           value="{{ old('display_name', $size->display_name) }}">
                    @error('display_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- <div class="form-group mb-3">
                    <label for="sort_order">Sort Order</label>
                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                           id="sort_order" name="sort_order"
                           value="{{ old('sort_order', $size->sort_order) }}">
                    @error('sort_order')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div> --}}

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Size
                    </button>
                    <a href="{{ route('admin.sizes.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection