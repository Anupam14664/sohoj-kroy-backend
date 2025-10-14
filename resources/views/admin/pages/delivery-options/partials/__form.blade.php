@props(['deliveryOption' => null])

<div class="form-group">
    <label for="name">Delivery Option Name</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror"
           id="name" name="name" value="{{ old('name', $deliveryOption->name ?? '') }}" required>
    @error('name')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="charge">Delivery Charge</label>
    <input type="number" step="1" class="form-control @error('charge') is-invalid @enderror"
       id="charge" name="charge" value="{{ old('charge', isset($deliveryOption->charge) ? intval($deliveryOption->charge) : '') }}" required>
    @error('charge')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <div class="form-check">
        <input type="checkbox" class="form-check-input" id="is_free_for_products"
               name="is_free_for_products" value="1"
               {{ old('is_free_for_products', $deliveryOption->is_free_for_products ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_free_for_products">Enable Free Delivery for Specific Products</label>
        <small class="form-text text-muted">
            When checked, you can select which products qualify for free delivery
        </small>
    </div>
</div>

<div class="form-group">
    <div class="form-check">
        <input type="checkbox" class="form-check-input" id="is_active"
               name="is_active" value="1"
               {{ old('is_active', $deliveryOption->is_active ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">Active</label>
    </div>
</div>
