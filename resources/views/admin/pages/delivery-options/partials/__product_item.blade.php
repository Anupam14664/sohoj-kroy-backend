@props(['product', 'selected'])

<div class="col-md-12 mb-3 product-item"
     data-id="{{ $product->id }}"
     data-name="{{ strtolower($product->name) }}"
     data-sku="{{ strtolower($product->sku) }}"
     data-variant-skus="{{ $product->variants->flatMap->options->pluck('sku')->implode(',') }}">
    <div class="custom-control custom-checkbox">
        <input type="checkbox" class="custom-control-input product-checkbox"
               id="product-{{ $product->id }}-{{ $selected ? 'selected' : 'available' }}"
               name="products[]"
               value="{{ $product->id }}"
               {{ $selected ? 'checked' : '' }}>
        <label class="custom-control-label" for="product-{{ $product->id }}-{{ $selected ? 'selected' : 'available' }}">
            {{ $product->name }}
            <div class="small text-muted">
                SKU: {{ $product->sku }}
            </div>
            @if($product->has_variants)
                <div class="small text-muted mt-1">
                    Variant SKUs: {{ $product->variants->flatMap->options->pluck('sku')->implode(', ') }}
                </div>
            @endif
        </label>
    </div>
</div>