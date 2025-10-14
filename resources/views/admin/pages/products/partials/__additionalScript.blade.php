<script>
    document.addEventListener('DOMContentLoaded', function() {
        const variantsContainer = document.getElementById('variants-container');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let variantCounter = {{ isset($product) && $product ? $product->variants->count() : 0 }};

        // Initialize existing variants
        initializeExistingVariants();

        // Add variant button handler
        document.getElementById('add-variant').addEventListener('click', async function(e) {
            e.preventDefault();
            const variantIndex = variantCounter++;

            try {
                // Show loading state
                const originalText = e.target.innerHTML;
                e.target.disabled = true;
                e.target.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';

                const response = await fetch(`{{ route('admin.products.variant') }}?index=${variantIndex}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                const data = await response.json();
                const div = document.createElement('div');
                div.innerHTML = data.html;
                const newVariant = div.firstElementChild;
                variantsContainer.appendChild(newVariant);

                // Initialize components for new variant
                initializeVariantComponents(newVariant);
                newVariant.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

            } catch (error) {
                console.error('Error adding variant:', error);
                showToast('Failed to add variant. Please try again.', 'error');
            } finally {
                // Reset button state ALWAYS
                e.target.disabled = false;
                e.target.innerHTML = 'Add Variant';
;
            }

        });

        // Event delegation for dynamic elements
        variantsContainer.addEventListener('click', function(e) {
            // Remove variant
            if (e.target.closest('.remove-variant')) {
                e.preventDefault();
                const card = e.target.closest('.variant-card');

                if (!confirm('Are you sure you want to remove this variant?')) return;

                card.style.transition = 'opacity 0.3s ease';
                card.style.opacity = '0';

                setTimeout(() => {
                    card.remove();
                    reindexVariants();
                }, 300);
            }

            // Add option
            if (e.target.closest('.add-option')) {
                e.preventDefault();
                const button = e.target.closest('.add-option');
                const variantIndex = button.dataset.variant;
                const optionsContainer = document.getElementById(`options-container-${variantIndex}`);
                const optionIndex = optionsContainer.children.length;

                fetch(`{{ route('admin.products.option') }}?variant=${variantIndex}&index=${optionIndex}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    const div = document.createElement('div');
                    div.innerHTML = data.html;
                    optionsContainer.appendChild(div.firstElementChild);
                    div.firstElementChild.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                })
                .catch(error => {
                    console.error('Error adding option:', error);
                    showToast('Failed to add option. Please try again.', 'error');
                });
            }

            // Remove option
            if (e.target.closest('.remove-option')) {
                e.preventDefault();
                const row = e.target.closest('.option-row');
                const container = row.parentElement;

                if (container.children.length <= 1) {
                    showToast('You must have at least one option.', 'warning');
                    return;
                }

                row.style.transition = 'opacity 0.3s ease';
                row.style.opacity = '0';

                setTimeout(() => {
                    row.remove();
                    reindexOptions(container);
                }, 300);
            }
        });

        // Helper functions
        function initializeExistingVariants() {
            document.querySelectorAll('.variant-card').forEach(card => {
                initializeVariantComponents(card);
            });
        }

        function initializeVariantComponents(element) {
            // Initialize select2 if available
            if (typeof $.fn.select2 === 'function') {
                $(element).find('.select2').select2({
                    width: '100%',
                    theme: 'bootstrap4'
                });
            }

            // Initialize file input previews
            element.querySelectorAll('input[type="file"]').forEach(input => {
                input.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    const preview = input.closest('.form-group').querySelector('.image-preview');
                    if (!preview) return;

                    const reader = new FileReader();
                    reader.onload = function(event) {
                        preview.src = event.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                });
            });
        }

        function reindexVariants() {
            document.querySelectorAll('.variant-card').forEach((card, index) => {
                // Update all variant indices in the form
                card.querySelectorAll('[name^="variants["]').forEach(input => {
                    input.name = input.name.replace(/variants\[\d+\]/, `variants[${index}]`);
                });

                // Update the variant index in the add option button
                const addOptionBtn = card.querySelector('.add-option');
                if (addOptionBtn) {
                    addOptionBtn.dataset.variant = index;
                }

                // Update the options container ID
                const optionsContainer = card.querySelector('.options-container');
                if (optionsContainer) {
                    optionsContainer.id = `options-container-${index}`;
                }
            });
        }

        function reindexOptions(container) {
            container.querySelectorAll('.option-row').forEach((row, index) => {
                row.querySelectorAll('[name*="[options]"]').forEach(input => {
                    input.name = input.name.replace(/options\]\[\d+\]/, `options][${index}]`);
                });
            });
        }

        function showToast(message, type = 'success') {
            // Toast implementation (same as before)
        }
    });
</script>

{{-- <script>
    document.addEventListener('DOMContentLoaded', function() {
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');
        let manuallyEdited = false;

        // Auto-generate slug when name changes, but only if slug wasn't manually edited
        nameInput.addEventListener('input', function() {
            if (!manuallyEdited) {
                slugInput.value = slugify(nameInput.value);
            }
        });

        slugInput.addEventListener('input', function() {
            manuallyEdited = true;
        });

        function slugify(text) {
            return text.toString().toLowerCase()
                .replace(/\s+/g, '-')
                .replace(/[^\w\-]+/g, '')
                .replace(/\-\-+/g, '-')
                .replace(/^-+/, '')
                .replace(/-+$/, '');
        }
    });
</script> --}}

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');
        let manuallyEdited = false;

        // Auto-generate slug when name changes, but only if slug wasn't manually edited
        nameInput.addEventListener('input', function() {
            if (!manuallyEdited) {
                slugInput.value = slugify(nameInput.value);
            }
        });

        slugInput.addEventListener('input', function() {
            manuallyEdited = true;
        });

        function slugify(text) {
            // First, transliterate Bangla → Avro-style English
            const transliterated = avroTransliterate(text);

            // Then, generate slug
            return transliterated
                .toString()
                .toLowerCase()
                .replace(/\s+/g, '-')        // Replace spaces with -
                .replace(/[^\w\-]+/g, '')   // Remove all non-word chars
                .replace(/\-\-+/g, '-')     // Replace multiple - with single -
                .replace(/^-+/, '')         // Trim - from start
                .replace(/-+$/, '');        // Trim - from end
        }

        function avroTransliterate(text) {
            const map = {
                'অ': 'o', 'আ': 'a', 'ই': 'i', 'ঈ': 'ee', 'উ': 'u', 'ঊ': 'oo',
                'ঋ': 'ri', 'এ': 'e', 'ঐ': 'oi', 'ও': 'o', 'ঔ': 'ou',

                'ক': 'k', 'খ': 'kh', 'গ': 'g', 'ঘ': 'gh', 'ঙ': 'ng',
                'চ': 'ch', 'ছ': 'chh', 'জ': 'j', 'ঝ': 'jh', 'ঞ': 'n',
                'ট': 't', 'ঠ': 'th', 'ড': 'd', 'ঢ': 'dh', 'ণ': 'n',
                'ত': 't', 'থ': 'th', 'দ': 'd', 'ধ': 'dh', 'ন': 'n',
                'প': 'p', 'ফ': 'ph', 'ব': 'b', 'ভ': 'bh', 'ম': 'm',
                'য': 'j', 'র': 'r', 'ল': 'l', 'শ': 'sh', 'ষ': 'sh', 'স': 's',
                'হ': 'h', 'ড়': 'r', 'ঢ়': 'rh', 'য়': 'y',

                'া': 'a', 'ি': 'i', 'ী': 'ee', 'ু': 'u', 'ূ': 'oo',
                'ে': 'e', 'ৈ': 'oi', 'ো': 'o', 'ৌ': 'ou',

                'ং': 'ng', 'ঃ': 'h', 'ঁ': 'n'
            };

            let result = '';
            for (let char of text) {
                result += map[char] !== undefined ? map[char] : char;
            }
            return result;
        }
    });
</script>


<style>
    @media (min-width: 768px) {
    .col-md-3 {
        margin: -19px;
        margin-top: 10px !important;
        margin-left: 1px;
    }
    .trash-btn{
        width: 13% !important;
    }
    .toast-body{
        color: red;
    }
}
</style>



<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('product-form');
        const galleryInput = document.getElementById('gallery_images');
        const galleryPreview = document.getElementById('gallery-preview');
        const addMoreBtn = document.getElementById('add-more-images');
        const errorContainer = document.getElementById('gallery-error');

        // Internal store of files
        let galleryFiles = [];

        let existingImagesCount = {{ isset($product) && $product->images ? $product->images->count() : 0 }};

        // Create hidden file inputs container
        const hiddenFileInputsContainer = document.createElement('div');
        hiddenFileInputsContainer.style.display = 'none';
        form.appendChild(hiddenFileInputsContainer);

        if (addMoreBtn) {
            addMoreBtn.addEventListener('click', () => galleryInput.click());
        }

        if (galleryInput) {
            galleryInput.addEventListener('change', function (e) {
                const newFiles = Array.from(e.target.files).filter(f => f.type.startsWith('image/'));

                if (newFiles.length === 0) {
                    showError('Only image files are allowed.');
                    return;
                }

                galleryFiles.push(...newFiles);
                updatePreview();
                updateHiddenFileInputs();
                clearError(); // Clear error when files are selected

                // Reset the input to allow re-selection
                galleryInput.value = '';
            });
        }

        function showError(message) {
            errorContainer.innerHTML = `<span class="text-danger">${message}</span>`;
        }

        function clearError() {
            errorContainer.innerHTML = '';
        }

        function updatePreview() {
            // Clear only the newly added images preview (not existing ones)
            const newPreviews = galleryPreview.querySelectorAll('[data-new-image]');
            newPreviews.forEach(el => el.remove());

            galleryFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const col = document.createElement('div');
                    col.className = 'col-md-3 mb-3';
                    col.setAttribute('data-new-image', 'true');
                    col.innerHTML = `
                        <div class="card">
                            <img src="${e.target.result}" class="card-img-top" style="height: 150px; object-fit: cover;">
                            <div class="card-body p-2">
                                <button type="button" class="btn btn-danger btn-sm remove-image" data-index="${index}">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    `;
                    galleryPreview.appendChild(col);
                };
                reader.readAsDataURL(file);
            });
        }

        function updateHiddenFileInputs() {
            hiddenFileInputsContainer.innerHTML = '';

            galleryFiles.forEach((file, i) => {
                const dt = new DataTransfer();
                dt.items.add(file);

                const newInput = document.createElement('input');
                newInput.type = 'file';
                newInput.name = 'gallery_images[]';
                newInput.files = dt.files;

                hiddenFileInputsContainer.appendChild(newInput);
            });
        }

        // Add form validation before submit
        form.addEventListener('submit', function(e) {
            const removedCount = document.getElementById('removed_images').value
                ? document.getElementById('removed_images').value.split(',').length
                : 0;
            const totalImages = galleryFiles.length + existingImagesCount - removedCount;

            if (totalImages <= 0) {
                e.preventDefault();
                showError('Please select at least one gallery image.');
                // Scroll to the error message
                errorContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
        });

        galleryPreview.addEventListener('click', function (e) {
            const btn = e.target.closest('.remove-image');
            if (btn) {
                if (btn.hasAttribute('data-index')) {
                    // Remove new image
                    const index = parseInt(btn.dataset.index);
                    galleryFiles.splice(index, 1);
                    updatePreview();
                    updateHiddenFileInputs();

                    // Check if we need to show error after removal
                    const removedCount = document.getElementById('removed_images').value
                        ? document.getElementById('removed_images').value.split(',').length
                        : 0;
                    if (galleryFiles.length === 0 && existingImagesCount - removedCount <= 0) {
                        showError('Please select at least one gallery image.');
                    }
                }
            }
        });
    });

    function removeExistingImage(button, imageId) {
        // Add to removed images list
        const removedInput = document.getElementById('removed_images');
        let removed = removedInput.value ? removedInput.value.split(',') : [];
        removed.push(imageId);
        removedInput.value = removed.join(',');

        // Remove the image element
        button.closest('.existing-image').remove();

        // Check if we need to show error after removal
        const existingCount = document.querySelectorAll('.existing-image').length;
        const galleryFiles = document.querySelectorAll('[data-new-image]').length;
        const errorContainer = document.getElementById('gallery-error');

        if (existingCount + galleryFiles <= 0) {
            errorContainer.innerHTML = '<span class="text-danger">Please select at least one gallery image.</span>';
        }
    }
</script>

<script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>

<script>

    // Initialize CKEditor with full image upload capabilities
    if (document.getElementById('description')) {
        CKEDITOR.replace('description', {
            toolbar: [
                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Blockquote'] },
                { name: 'links', items: ['Link', 'Unlink'] },
                { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar'] },
                { name: 'document', items: ['Source'] },
                '/',
                { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
                { name: 'colors', items: ['TextColor', 'BGColor'] },
                { name: 'tools', items: ['Maximize'] }
            ],
            height: 300,
            // Enable enhanced image upload features
            extraPlugins: 'uploadimage,image2',
            removePlugins: 'image',
            // Upload configuration
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            filebrowserImageUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}?type=Images",
            uploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            // Image dialog configuration
            imageUpload_maxWidth: 1200,
            imageUpload_maxHeight: 1200,
            imageUpload_maxSize: 2, // MB
            // Allow pasting images directly
            pasteFromWordRemoveStyles: false,
            pasteFromWordRemoveFontStyles: false,
            // Enable drag and drop
            uploadDrop: true
        });
    }

    // Update CKEditor content before form submission
    document.getElementById('product-form').addEventListener('submit', function() {
        for (var instance in CKEDITOR.instances) {
            CKEDITOR.instances[instance].updateElement();
        }
    });
    </script>

<style>
    .cke_notifications_area {
        display: none !important;
    }
</style>
