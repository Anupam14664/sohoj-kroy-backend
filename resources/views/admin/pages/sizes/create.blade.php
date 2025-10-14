@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3>Create New Size</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.sizes.store') }}" method="POST">
                @csrf

                <div class="form-group mb-3">
                    <label for="type">Size Type *</label>
                    <select class="form-control @error('type') is-invalid @enderror"
                            id="type" name="type" required>
                        <option value="">Select Type</option>
                        <option value="numeric" {{ old('type') == 'numeric' ? 'selected' : '' }}>Numeric (e.g., 36, 38, 40)</option>
                        <option value="text" {{ old('type') == 'text' ? 'selected' : '' }}>Text (e.g., S, M, L, XL)</option>
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
                           id="name" name="name" value="{{ old('name') }}"
                           placeholder="e.g., '36' or 'XL'" required>
                    <small class="form-text text-muted">
                        The actual size code used in the system
                    </small>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="display_name">Display Name</label>
                    <input type="text" class="form-control @error('display_name') is-invalid @enderror"
                           id="display_name" name="display_name" value="{{ old('display_name') }}"
                           placeholder="e.g., 'Extra Large' for 'XL'">
                    <small class="form-text text-muted">
                        Optional friendly name for display purposes
                    </small>
                    @error('display_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- <div class="form-group mb-3">
                    <label for="sort_order">Sort Order</label>
                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                           id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}">
                    <small class="form-text text-muted">
                        Lower numbers appear first
                    </small>
                    @error('sort_order')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div> --}}

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Size
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