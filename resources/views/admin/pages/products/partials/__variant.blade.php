<div class="variant-card card mb-3" data-variant-id="{{ $variant->id ?? '' }}">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Variant #<span class="variant-number">{{ $variantIndex + 1 }}</span></span>
        <button type="button" class="btn btn-sm btn-danger remove-variant">Remove</button>
    </div>

    <div class="card-body">
        @if(isset($variant->id))
            <input type="hidden" name="variants[{{ $variantIndex }}][id]" value="{{ $variant->id }}">
        @endif

        <div class="form-group">
            <label>Color *</label>
            <select name="variants[{{ $variantIndex }}][color_id]" class="form-control select2">
                <option value="">Select Color</option>
                @foreach($colors as $color)
                    <option value="{{ $color->id }}"
                        {{ (isset($variant->color_id) ? $variant->color_id : old("variants.$variantIndex.color_id")) == $color->id ? 'selected' : '' }}>
                        {{ $color->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Variant Image</label>
            <input type="file" name="variants[{{ $variantIndex }}][image]" class="form-control-file" accept="image/*">
            @if(isset($variant->image) && $variant->image)
                <div class="mt-2">
                    <img src="{{ asset('storage/'.$variant->image) }}" class="img-thumbnail image-preview" style="max-height: 100px; display: block;">
                    <input type="hidden" name="variants[{{ $variantIndex }}][existing_image]" value="{{ $variant->image }}">
                </div>
            @else
                <img src="" class="img-thumbnail image-preview" style="max-height: 100px; display: none;">
            @endif
        </div>

        <div class="options-container mt-3" id="options-container-{{ $variantIndex }}">
            @if(isset($variant->options) && count($variant->options) > 0)
                @foreach($variant->options as $optionIndex => $option)
                    @include('admin.pages.products.partials.__option', [
                        'variantIndex' => $variantIndex,
                        'optionIndex' => $optionIndex,
                        'option' => $option,
                        'sizes' => $sizes
                    ])
                @endforeach
            @else
                {{-- Always include at least one option row --}}
                @include('admin.pages.products.partials.__option', [
                    'variantIndex' => $variantIndex,
                    'optionIndex' => 0,
                    'option' => [],
                    'sizes' => $sizes
                ])
            @endif
        </div>

        <button type="button" class="btn btn-sm btn-secondary add-option mt-3" data-variant="{{ $variantIndex }}">
            <i class="fas fa-plus"></i> Add Option
        </button>
    </div>
</div>
