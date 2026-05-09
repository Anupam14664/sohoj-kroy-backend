@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h2>Create Page</h2>
    <form id="pageForm" action="{{ route('admin.pages.store') }}" method="POST" enctype="multipart/form-data" style="width: 100%">
        @csrf

        <!-- Page Name -->
        <div class="mb-3">
            <label class="form-label">Page Name</label>
            <input name="name" id="pageName" class="form-control" required>
        </div>

        <!-- Slug -->
        <div class="mb-3">
            <label class="form-label">Slug</label>
            <input name="slug" id="slug" class="form-control" >
        </div>

<!-- Product Select -->
<div class="mb-3 position-relative">
    <label class="form-label">Select Product</label>

    <input type="text" id="productSearch" class="form-control"
           placeholder="Type to search products...">

    <!-- Dropdown search results -->
    <div id="productResults"
         class="list-group position-absolute w-100 mt-1"
         style="z-index:1000; max-height:220px; overflow-y:auto;">
    </div>

    <!-- Hidden input for selected product ID -->
    <input type="hidden" id="product_id" name="product_id">

    <!-- Selected product preview (hidden by default) -->
    <div id="selectedProductPreview"
         class="border rounded p-2 mt-2 d-flex align-items-center gap-3"
         style="display:none !important; background:#f8f9fa;" hidden>

        <img id="selectedProductImage"
             style="width:70px;height:70px;object-fit:cover;border-radius:6px;" >

        <div class="flex-grow-1">
            <div id="selectedProductName" class="fw-bold"></div>
            <div id="selectedProductSku" class="text-muted small"></div>
        </div>

        {{-- <button type="button"
                id="clearSelectedProduct"
                class="btn btn-sm btn-outline-danger">
            Clear
        </button> --}}
    </div>
</div>


        <!-- Status -->
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="1" selected>Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>

        <div class="row">
            <!-- Section List -->
            <div class="col-md-3">
                <h5>Available Sections</h5>
                <div id="section-list" class="list-group">
                    <div class="list-group-item draggable-section" data-type="hero_product_banner">Hero Product Banner</div>
                    <div class="list-group-item draggable-section" data-type="product_video_slide">Product Video Slide</div>
                    <div class="list-group-item draggable-section" data-type="product_feature">Product Feature (image)</div>
                    <div class="list-group-item draggable-section" data-type="premium_product_promotion">Premium Product Promotion</div>
                    <div class="list-group-item draggable-section" data-type="why_choose_us">Why Choose Us</div>
                    <div class="list-group-item draggable-section" data-type="featured_product">Featured Product</div>
                    <div class="list-group-item draggable-section" data-type="product_highlight">Product Highlight</div>
                    <div class="list-group-item draggable-section" data-type="certification">Certification</div>
                    <div class="list-group-item draggable-section" data-type="customer_review">Customer Review</div>
                    <div class="list-group-item draggable-section" data-type="exclusive_product">Exclusive Product</div>
                    <div class="list-group-item draggable-section" data-type="faq">FAQ</div>
                </div>
            </div>

            <!-- Canvas -->
            <div class="col-md-5">
                <h5>Page Canvas</h5>
                <div id="canvas" class="border p-3" style="min-height:400px; background:#f8f9fa;">
                    <p class="text-muted" >Drag sections here</p>
                </div>
                <div class="mt-3 d-flex gap-2">
                    {{-- <button type="button" id="btnPreview" class="btn btn-secondary">Preview JSON</button> --}}
                    {{-- <button type="submit" class="btn btn-primary">Save Page</button> --}}

                    <button type="button" id="savePageBtn" class="btn btn-primary">Save Page</button>
                    @include('admin.modal.savecofirmmodal')

                </div>
            </div>

            <!-- Section Settings -->
            <div class="col-md-4">
                <h5>Section Settings</h5>
                <div id="section-settings">
                    <p class="text-muted">Select a section in canvas to edit settings</p>
                </div>
            </div>
        </div>

        <input type="hidden" name="sections_json" id="sections_json">
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const uploadUrl = "{{ route('admin.pages.upload.media') }}";
        const csrf = "{{ csrf_token() }}";

        const sectionList = document.getElementById('section-list');
        const canvas = document.getElementById('canvas');
        const settingsPane = document.getElementById('section-settings');
        const slugInput = document.getElementById('slug');
        const pageNameInput = document.getElementById('pageName');

        pageNameInput.addEventListener('input', () => {
            slugInput.value = pageNameInput.value.trim().toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
        });

        new Sortable(canvas, { group:'page', animation:150, onEnd:updateSectionsJSON });
        new Sortable(sectionList, { group:{name:'page',pull:'clone',put:false}, sort:false });

        document.querySelectorAll('.draggable-section').forEach(el=>{
            el.addEventListener('click',()=>addSection(el.dataset.type));
        });

        // Initialize section settings based on type
        function initializeSectionSettings(type) {
            const baseSettings = {
                cta: '',
                heading: '',
                description: ''
            };

            switch(type) {
                case 'hero_product_banner':
                    return {
                        ...baseSettings,
                        image: null,
                        countdownDate: null
                    };
                case 'product_video_slide':
                    return {
                        ...baseSettings,
                        videos: []
                    };
                case 'product_feature':
                case 'certification':
                case 'customer_review':
                    return {
                        ...baseSettings,
                        images: []
                    };
                case 'premium_product_promotion':
                case 'featured_product':
                case 'exclusive_product':
                    return {
                        ...baseSettings,
                        image: null
                    };
                case 'why_choose_us':
                    return {
                        ...baseSettings,
                        items: []
                    };
                case 'product_highlight':
                    return {
                        ...baseSettings,
                        image: null,
                        points: []
                    };
                case 'faq':
                    return {
                        heading: '',
                        faqs: []
                    };
                default:
                    return baseSettings;
            }
        }

        function addSection(type){
            const id = 's_' + Date.now();
            const el = document.createElement('div');
            el.className = 'card mb-2 section-card';
            el.dataset.type = type;
            el.dataset.id = id;

            // PROPERLY INITIALIZE SETTINGS
            el._settings = initializeSectionSettings(type);

            el.innerHTML = `
                <div class="card-body p-2 d-flex justify-content-between align-items-center">
                    <div><strong>${type.replace(/_/g,' ')}</strong></div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-edit">Edit</button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove">Remove</button>
                    </div>
                </div>`;
            el.querySelector('.btn-remove').onclick = () => { el.remove(); updateSectionsJSON(); };
            el.querySelector('.btn-edit').onclick = () => openSettings(el);
            canvas.appendChild(el);
            updateSectionsJSON();
        }

        // ---------- SETTINGS PANEL ----------
        function openSettings(card){
            const type = card.dataset.type;

            const sizeNote = {
                hero_product_banner: "Recommended: 1080×1080px (PNG or JPG)",
                product_feature: "Recommended: 1080×1080px (PNG or JPG)",
                customer_review: "Recommended: 1080×1350px (PNG or JPG)",
                certification: "Recommended: 1220×1024px (PNG or JPG)",
                premium_product_promotion: "Recommended: 1080×1080px PNG",
                featured_product: "Recommended: 1080×1080 PNG",
                product_highlight: "Recommended: 1080×1080 (PNG or JPG)",
                exclusive_product: "Recommended: 1080×1080 PNG",
                why_choose_us: "Recommended: 80×80 PNG"
            };

            let html = `<h6 class="mb-2">Edit: ${type.replace(/_/g,' ')}</h6>`;

            const uploadInput = (id='fileInput', multiple=false) =>
                `<input type="file" id="${id}" class="form-control" ${multiple ? 'multiple' : ''} accept="image/png, image/jpeg, image/jpg">`;

            const uploadPreview = (url) => url ? `<img src="${url}" class="img-fluid mt-2" style="max-height:100px;">` : '';

            // Section-specific fields
            switch(type){
                case 'hero_product_banner':
                    html += `
                        <div class="mt-2">
                            <label>Heading</label>
                            <input type="text" id="heroHeading" class="form-control" value="${card._settings.heading || ''}">
                        </div>
                        <div class="mt-2">
                            <label>Description</label>
                            <textarea id="heroDescription" class="form-control">${card._settings.description || ''}</textarea>
                        </div>
                        <div class="mt-2 countdown-wrapper">
                            <label>Countdown Date</label>
                            <input type="date" id="countdownDate" class="form-control countdown-date"
                                value="${card._settings.countdownDate ? card._settings.countdownDate.split('T')[0] : ''}">
                        </div>
                        <div class="mt-2 mb-2 countdown-wrapper">
                            <label>Countdown Time</label>
                            <input type="time" id="countdownTime" class="form-control countdown-time"
                                value="${card._settings.countdownDate ? card._settings.countdownDate.split('T')[1]?.slice(0,5) : ''}">
                        </div>
                        <label>Image</label>
                        <input type="file" id="heroImg" class="form-control" accept="image/png, image/jpg, image/jpeg">
                        <small class="text-muted" style="font-size:12px;">${sizeNote[type]}</small>
                        ${uploadPreview(card._settings.image)}
                        <div id="uploadStatus" class="text-muted small mt-1"></div>
                        <div class="mt-2">
                            <label>CTA Text</label>
                            <input type="text" id="cta" class="form-control" value="${card._settings.cta||''}">
                        </div>`;
                    break;

                case 'product_video_slide':
                    html += `
                        <div>
                            <label>YouTube Links</label>
                            <div id="videoList">
                                ${(card._settings.videos || []).map(video =>
                                    `<div class="d-flex justify-content-between align-items-center mb-1 p-2 border rounded">
                                        <span class="small">${video}</span>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-video">Remove</button>
                                    </div>`
                                ).join('')}
                            </div>
                            <input type="text" id="newVideo" class="form-control mt-1" placeholder="Enter YouTube URL">
                            <button id="addVideo" type="button" class="btn btn-sm btn-outline-primary mt-1">Add Video</button>
                        </div>
                        <div class="mt-2">
                            <label>CTA Text</label>
                            <input type="text" id="cta" class="form-control" value="${card._settings.cta||''}">
                        </div>`;
                    break;

                case 'product_feature':
                case 'certification':
                case 'customer_review':
                    html += `
                        ${uploadInput('multiImage', true)}
                        <small class="text-muted" style="font-size:12px;">${sizeNote[type]}</small>
                        <div id="imagePreview" class="d-flex flex-wrap gap-2 mt-2">
                            ${(card._settings.images || []).map(img => `
                                <div class="img-wrapper">
                                    <img src="${img}" style="height:50px; border-radius:4px;">
                                    <div class="img-close" data-img="${img}">✖</div>
                                </div>
                            `).join('')}
                        </div>
                        <div id="uploadStatus" class="text-muted small mt-1"></div>
                        <div class="mt-2">
                            <label>CTA Text</label>
                            <input type="text" id="cta" class="form-control" value="${card._settings.cta||''}">
                        </div>`;
                    break;

                case 'premium_product_promotion':
                case 'featured_product':
                    html += `
                        <div class="mb-2">
                            <label>Single Image</label>
                            <input type="file" id="featuredImage" class="form-control" accept="image/png">
                            <small class="text-muted">${sizeNote[type]}</small>
                            ${card._settings.image ? `<img src="${card._settings.image}" class="mt-2" style="max-height:100px;">` : ''}
                        </div>
                        <div class="mb-2">
                            <label>Heading</label>
                            <input type="text" id="heading" class="form-control" value="${card._settings.heading||''}">
                        </div>
                        <div class="mb-2">
                            <label>Description</label>
                            <textarea id="description" class="form-control">${card._settings.description||''}</textarea>
                        </div>
                        <div class="mb-2">
                            <label>CTA Text</label>
                            <input type="text" id="cta" class="form-control" value="${card._settings.cta||''}">
                        </div>`;
                    break;

                case 'product_highlight':
                    html += `
                        <div class="mb-2">
                            <label>Product Image</label>
                            <input type="file" id="highlightImage" class="form-control" accept="image/png">
                            <small class="text-muted" style="font-size:12px;">${sizeNote[type]}</small>
                            ${card._settings.image ? `<img src="${card._settings.image}" class="mt-2" style="max-height:100px;">` : ''}
                        </div>
                        <div class="mb-2">
                            <label>Heading</label>
                            <input type="text" id="heading" class="form-control" value="${card._settings.heading||''}">
                        </div>
                        <div class="mb-2">
                            <label>Description</label>
                            <textarea id="description" class="form-control">${card._settings.description||''}</textarea>
                        </div>
                        <div class="mb-2">
                            <label>CTA Text</label>
                            <input type="text" id="cta" class="form-control" value="${card._settings.cta||''}">
                        </div>
                        <div>
                            <label>Short Points</label>
                            <div id="pointsList">
                                ${(card._settings.points || []).map(point =>
                                    `<div class="d-flex justify-content-between align-items-center mb-1 p-2 border rounded">
                                        <span>${point}</span>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-point">Remove</button>
                                    </div>`
                                ).join('')}
                            </div>
                            <input type="text" id="newPoint" class="form-control mt-1" placeholder="Add short description">
                            <button id="addPoint" type="button" class="btn btn-sm btn-outline-primary mt-1">Add</button>
                        </div>`;
                    break;

                case 'why_choose_us':
                    html += `
                        <div>
                            <label>Heading</label>
                            <input type="text" id="heading" class="form-control" value="${card._settings.heading||''}">
                        </div>
                        <div id="whyList" class="mt-2">
                            ${(card._settings.items || []).map((item, index) => `
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="${item.image}" style="height:40px; width:40px; object-fit:cover;">
                                        <span>${item.desc}</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-why" data-index="${index}">Remove</button>
                                </div>
                            `).join('')}
                        </div>
                        <input type="file" id="whyImage" class="form-control" accept="image/png">
                        <small class="text-muted" style="font-size:12px;">${sizeNote[type]}</small>
                        <input type="text" id="whyDesc" class="form-control mt-1" placeholder="Description">
                        <button id="addWhy" type="button" class="btn btn-sm btn-outline-primary mt-1">Add Item</button>`;
                    break;

                case 'exclusive_product':
                    html += `
                        <div class="mb-2">
                            <label>Product Image</label>
                            <input type="file" id="exclusiveImage" class="form-control" accept="image/png">
                            <small class="text-muted" style="font-size:12px;">${sizeNote[type]}</small>
                            ${card._settings.image ? `<img src="${card._settings.image}" class="mt-2" style="max-height:100px;">` : ''}
                        </div>
                        <div class="mb-2">
                            <label>Heading</label>
                            <input type="text" id="heading" class="form-control" value="${card._settings.heading || ''}">
                        </div>
                        <div class="mb-2">
                            <label>Description</label>
                            <textarea id="description" class="form-control">${card._settings.description || ''}</textarea>
                        </div>
                        <div class="mb-2">
                            <label>CTA 1 Text</label>
                            <input type="text" id="cta1Text" class="form-control" value="${card._settings.cta1Text || ''}">
                        </div>
                        <div class="mb-2">
                            <label>CTA 2 Text</label>
                            <input type="text" id="cta2Text" class="form-control" value="${card._settings.cta2Text || ''}">
                        </div>`;
                    break;

                case 'faq':
                    html += `
                        <div>
                            <label>Heading</label>
                            <input type="text" id="heading" class="form-control" value="${card._settings.heading||''}">
                        </div>
                        <div id="faqList" class="mt-2">
                            ${(card._settings.faqs || []).map((faq, index) => `
                                <div class="mb-2 p-2 border rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>Q: ${faq.q}</strong>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-faq" data-index="${index}">Remove</button>
                                    </div>
                                    <div class="mt-1">A: ${faq.a}</div>
                                </div>
                            `).join('')}
                        </div>
                        <input type="text" id="faqQ" class="form-control mt-1" placeholder="Question">
                        <textarea id="faqA" class="form-control mt-1" rows="3" placeholder="Answer"></textarea>
                        <button id="addFaq" type="button" class="btn btn-sm btn-outline-primary mt-1">Add FAQ</button>`;
                    break;
            }

            html += `<div class="mt-3 d-flex gap-2">
                        <button type="button" id="saveSettings" class="btn btn-primary btn-sm">Save</button>
                        <button type="button" id="cancelSettings" class="btn btn-secondary btn-sm">Cancel</button>
                    </div>`;
            settingsPane.innerHTML = html;

            // Setup event listeners for each section type
            setupSectionEvents(card, type);
        }

        function setupSectionEvents(card, type) {
            // Hero banner image upload
            if(type === 'hero_product_banner'){
                const input = document.getElementById('heroImg');
                const status = document.getElementById('uploadStatus');

                input.onchange = async (e) => {
                    const f = e.target.files[0];
                    if(!f) return;

                    const fd = new FormData();
                    fd.append('file', f);
                    status.textContent = 'Uploading...';

                    try {
                        const res = await fetch(uploadUrl, {
                            method: 'POST',
                            headers: {'X-CSRF-TOKEN': csrf},
                            body: fd
                        });
                        const d = await res.json();

                        if(d.success){
                            card._settings.image = d.url;
                            status.innerHTML = `<img src="${d.url}" class="mt-2" style="max-height:100px;"> Uploaded`;
                        }
                    } catch (error) {
                        status.textContent = 'Upload failed';
                    }
                };
            }

            // Multiple image upload
            if (['product_feature', 'certification', 'customer_review'].includes(type)) {
                const input = document.getElementById('multiImage');
                const preview = document.getElementById('imagePreview');
                const status = document.getElementById('uploadStatus');

                // Remove image handler
                preview.addEventListener('click', (e) => {
                    if (e.target.classList.contains('img-close')) {
                        const imgUrl = e.target.getAttribute('data-img');
                        card._settings.images = card._settings.images.filter(img => img !== imgUrl);
                        e.target.parentElement.remove();
                    }
                });

                input.onchange = async (e) => {
                    for (const f of e.target.files) {
                        const fd = new FormData();
                        fd.append('file', f);
                        status.textContent = 'Uploading...';

                        try {
                            const res = await fetch(uploadUrl, {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': csrf },
                                body: fd
                            });
                            const d = await res.json();

                            if (d.success) {
                                card._settings.images.push(d.url);

                                const wrap = document.createElement('div');
                                wrap.className = 'img-wrapper';
                                wrap.innerHTML = `
                                    <img src="${d.url}" style="height:50px; border-radius:4px;">
                                    <div class="img-close" data-img="${d.url}">✖</div>
                                `;
                                preview.appendChild(wrap);
                            }
                        } catch (error) {
                            status.textContent = 'Upload failed';
                        }
                    }
                    status.textContent = 'Upload completed';
                };
            }

            // Video management
            if(type === 'product_video_slide'){
                const list = document.getElementById('videoList');

                // Remove video handler
                list.addEventListener('click', (e) => {
                    if (e.target.classList.contains('remove-video')) {
                        const videoDiv = e.target.closest('.d-flex');
                        const videoText = videoDiv.querySelector('span').textContent;
                        card._settings.videos = card._settings.videos.filter(v => v !== videoText);
                        videoDiv.remove();
                    }
                });

                document.getElementById('addVideo').onclick = () => {
                    const val = document.getElementById('newVideo').value.trim();
                    if(val && !card._settings.videos.includes(val)) {
                        card._settings.videos.push(val);

                        const div = document.createElement('div');
                        div.className = 'd-flex justify-content-between align-items-center mb-1';
                        div.innerHTML = `
                            <span class="flex-grow-1 me-2 text-break">${val}</span>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-video flex-shrink-0">Remove</button>
                        `;
                        list.appendChild(div);
                        document.getElementById('newVideo').value = '';
                    }
                };
            }
            // Why choose us items
            if(type === 'why_choose_us'){
                const list = document.getElementById('whyList');

                // Remove item handler
                list.addEventListener('click', (e) => {
                    if (e.target.classList.contains('remove-why')) {
                        const index = parseInt(e.target.getAttribute('data-index'));
                        card._settings.items.splice(index, 1);

                        // SAVE HEADING BEFORE REFRESH
                        const headingInput = document.getElementById('heading');
                        if(headingInput){
                            card._settings.heading = headingInput.value;
                        }

                        openSettings(card);
                    }
                });

                document.getElementById('addWhy').onclick = async () => {

                    const addBtn = document.getElementById('addWhy');

                    // SAVE HEADING BEFORE UPLOAD
                    const headingInput = document.getElementById('heading');
                    if(headingInput){
                        card._settings.heading = headingInput.value;
                    }

                    const f = document.getElementById('whyImage').files[0];
                    const d = document.getElementById('whyDesc').value.trim();

                    if(!f || !d) return;

                    const fd = new FormData();
                    fd.append('file', f);

                    try {

                        // LOADING STATE
                        addBtn.disabled = true;
                        addBtn.innerHTML = `
                            <span class="spinner-border spinner-border-sm me-1"></span>
                            Uploading...
                        `;

                        const res = await fetch(uploadUrl, {
                            method: 'POST',
                            headers: {'X-CSRF-TOKEN': csrf},
                            body: fd
                        });

                        const j = await res.json();

                        if(j.success){

                            card._settings.items.push({
                                image: j.url,
                                desc: d
                            });

                            openSettings(card);
                        }

                    } catch (error) {

                        console.error('Upload failed:', error);

                    } finally {

                        addBtn.disabled = false;
                        addBtn.innerHTML = 'Add Item';
                    }
                };
            }


            // Single image upload handlers
            if (['premium_product_promotion', 'featured_product', 'product_highlight', 'exclusive_product'].includes(type)) {
                const inputId = type === 'exclusive_product' ? 'exclusiveImage' :
                               type === 'product_highlight' ? 'highlightImage' : 'featuredImage';

                const input = document.getElementById(inputId);

                input.onchange = async (e) => {
                    const f = e.target.files[0];
                    if(!f) return;

                    const fd = new FormData();
                    fd.append('file', f);

                    try {
                        const res = await fetch(uploadUrl, {
                            method: 'POST',
                            headers: {'X-CSRF-TOKEN': csrf},
                            body: fd
                        });
                        const d = await res.json();

                        if(d.success){
                            card._settings.image = d.url;
                            openSettings(card); // Refresh to show new image
                        }
                    } catch (error) {
                        console.error('Upload failed:', error);
                    }
                };
            }

            // Product highlight points
            if(type === 'product_highlight'){
                const list = document.getElementById('pointsList');

                // Remove point handler
                list.addEventListener('click', (e) => {
                    if (e.target.classList.contains('remove-point')) {
                        const pointDiv = e.target.closest('.d-flex');
                        const pointText = pointDiv.querySelector('span').textContent;
                        card._settings.points = card._settings.points.filter(p => p !== pointText);
                        pointDiv.remove();
                    }
                });

                document.getElementById('addPoint').onclick = () => {
                    const val = document.getElementById('newPoint').value.trim();
                    if(val && !card._settings.points.includes(val)) {
                        card._settings.points.push(val);

                        const div = document.createElement('div');
                        div.className = 'd-flex justify-content-between align-items-center mb-1';
                        div.innerHTML = `
                            <span class="flex-grow-1 me-2">${val}</span>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-point flex-shrink-0">Remove</button>
                        `;
                        list.appendChild(div);
                        document.getElementById('newPoint').value = '';
                    }
                };
            }


            // FAQ management
            if(type === 'faq'){
                const list = document.getElementById('faqList');

                // Remove FAQ handler
                list.addEventListener('click', (e) => {
                    if (e.target.classList.contains('remove-faq')) {
                        const index = parseInt(e.target.getAttribute('data-index'));
                        card._settings.faqs.splice(index, 1);
                        openSettings(card);
                    }
                });

                document.getElementById('addFaq').onclick = () => {
                    const q = document.getElementById('faqQ').value.trim();
                    const a = document.getElementById('faqA').value.trim();

                    if(q && a){
                        card._settings.faqs.push({q, a});
                        openSettings(card);
                    }
                };
            }

            // Save settings button
            document.getElementById('saveSettings').onclick = () => {
                saveSectionSettings(card, type);
            };

            document.getElementById('cancelSettings').onclick = () => {
                settingsPane.innerHTML = '<p class="text-muted">Select a section in canvas to edit settings</p>';
            };
        }

        function saveSectionSettings(card, type) {
            // Common fields
            if (document.getElementById('cta')) card._settings.cta = document.getElementById('cta').value;
            if (document.getElementById('heading')) card._settings.heading = document.getElementById('heading').value;
            if (document.getElementById('description')) card._settings.description = document.getElementById('description').value;

            // Exclusive product fields
            if (document.getElementById('cta1Text')) card._settings.cta1Text = document.getElementById('cta1Text').value;
            if (document.getElementById('cta2Text')) card._settings.cta2Text = document.getElementById('cta2Text').value;

            // Hero banner specific
            if (type === 'hero_product_banner') {
                card._settings.heading = document.getElementById('heroHeading').value;
                card._settings.description = document.getElementById('heroDescription').value;

                const date = document.getElementById('countdownDate').value;
                const time = document.getElementById('countdownTime').value;

                if (date && time) {
                    card._settings.countdownDate = `${date}T${time}:00`;
                } else if (date) {
                    card._settings.countdownDate = `${date}T00:00:00`;
                } else {
                    card._settings.countdownDate = null;
                }
            }

            settingsPane.innerHTML = '<p class="text-success">Settings Saved</p>';
            updateSectionsJSON();
        }

        function updateSectionsJSON() {
            const sections = [];

            canvas.querySelectorAll('.section-card').forEach((card, index) => {
                // Ensure settings is properly initialized
                if (!card._settings || typeof card._settings !== "object") {
                    card._settings = initializeSectionSettings(card.dataset.type);
                }

                // Clean empty values
                const cleanedSettings = {};
                Object.keys(card._settings).forEach(key => {
                    const val = card._settings[key];

                    // Skip null values
                    if (val === null) return;

                    // Skip empty arrays
                    if (Array.isArray(val) && val.length === 0) return;

                    // Skip empty strings
                    if (typeof val === 'string' && val.trim() === '') return;

                    cleanedSettings[key] = val;
                });

                const finalSettings = Object.keys(cleanedSettings).length === 0 ? null : cleanedSettings;

                sections.push({
                    id: card.dataset.id,
                    type: card.dataset.type,
                    position: index,
                    settings: finalSettings,
                });
            });

            document.getElementById('sections_json').value = JSON.stringify(sections);
        }
    });
</script>

<script>

const products = @json($products);


const productSearch  = document.getElementById('productSearch');
const productResults = document.getElementById('productResults');
const productIdInput = document.getElementById('product_id');

const previewBox  = document.getElementById('selectedProductPreview');
const previewImg  = document.getElementById('selectedProductImage');
const previewName = document.getElementById('selectedProductName');
const previewSku  = document.getElementById('selectedProductSku');

function showSelectedProductPreview(product) {
    previewImg.src = product.image_url ?? '/images/no-image.png';
    previewName.textContent = product.name;
    previewSku.textContent = 'SKU: ' + (product.sku ?? 'N/A');
    previewBox.style.display = 'flex';
    productIdInput.value = product.id;
}

productSearch.addEventListener('input', () => {
    const query = productSearch.value.toLowerCase().trim();
    productResults.innerHTML = '';

    if (!query) return;

    const matches = products.filter(p => p.name.toLowerCase().includes(query));

    matches.forEach(p => {
        const imgSrc = p.image_url ?? '/images/no-image.png';

        const item = document.createElement('div');
        item.className = 'list-group-item list-group-item-action d-flex align-items-center gap-2';

        item.innerHTML = `
            <img src="${imgSrc}" style="width:45px;height:45px;object-fit:cover;border-radius:4px;">
            <div class="flex-grow-1">
                <div class="fw-semibold">${p.name}</div>
                <div class="small text-muted">SKU: ${p.sku ?? 'N/A'}</div>
            </div>
        `;


        item.onclick = () => {
            productSearch.value = p.name;
            productResults.innerHTML = '';
            showSelectedProductPreview(p);
        };

        productResults.appendChild(item);
    });
});


document.getElementById('clearSelectedProduct').onclick = () => {
    productSearch.value = '';
    productIdInput.value = '';
    previewBox.style.display = 'none';
    previewImg.src = '';
    previewName.textContent = '';
    previewSku.textContent = '';
    productResults.innerHTML = '';
};


document.addEventListener('click', (e) => {
    if (!productResults.contains(e.target) && e.target !== productSearch) {
        productResults.innerHTML = '';
    }
});
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        const saveBtn = document.getElementById("savePageBtn");
        const form = document.getElementById("pageForm");

        saveBtn.addEventListener("click", function () {
            const pageName = document.getElementById("pageName").value.trim();
            const slug = document.getElementById("slug").value.trim();
            const productId = document.getElementById("product_id").value.trim();

            if (!pageName || !slug || !productId) {
                let missingFields = [];
                if (!pageName) missingFields.push("Page Name");
                if (!slug) missingFields.push("Slug");
                if (!productId) missingFields.push("Product");

                document.getElementById("warningText").textContent =
                    "Please fill the following fields first: " + missingFields.join(", ");

                let warningModal = new bootstrap.Modal(document.getElementById('warningModal'));
                warningModal.show();
                return;
            }
            let modal = new bootstrap.Modal(document.getElementById('saveConfirmModal'));
            modal.show();

            document.querySelector("#saveConfirmModal .btn-success").onclick = function () {
                form.submit();
            };
        });

    });

</script>

<style>
    .img-wrapper {
        position: relative;
        display: inline-block;
    }
    .img-wrapper img {
        height: 50px;
        border-radius: 4px;
    }
    .img-close {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ff4d4d;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
<style>
    .img-wrapper {
        position: relative;
        display: inline-block;
        margin: 5px;
    }
    .img-wrapper img {
        height: 50px;
        width: 50px;
        object-fit: cover;
        border-radius: 4px;
    }
    .img-close {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #ff4d4d;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        z-index: 10;
    }

    /* Video list items styling */
    #videoList .d-flex {
        position: relative;
        padding: 8px 12px;
        margin-bottom: 5px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background: #f8f9fa;
    }

    #videoList .remove-video {
        position: static;
        margin-left: 10px;
        flex-shrink: 0;
    }

    #videoList span {
        flex: 1;
        word-break: break-all;
        font-size: 0.875rem;
    }

    /* Points list items styling */
    #pointsList .d-flex {
        position: relative;
        padding: 8px 12px;
        margin-bottom: 5px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background: #f8f9fa;
    }

    #pointsList .remove-point {
        position: static;
        margin-left: 10px;
        flex-shrink: 0;
    }

    /* Why choose us items styling */
    #whyList .d-flex {
        position: relative;
        padding: 8px 12px;
        margin-bottom: 5px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background: #f8f9fa;
    }

    #whyList .remove-why {
        position: static;
        margin-left: 10px;
        flex-shrink: 0;
    }

    #whyList .d-flex > div {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    #whyList img {
        height: 40px;
        width: 40px;
        object-fit: cover;
        border-radius: 4px;
        flex-shrink: 0;
    }

    /* FAQ items styling */
    #faqList .border {
        position: relative;
        padding: 12px;
        margin-bottom: 8px;
        border: 1px solid #dee2e6 !important;
        border-radius: 6px;
        background: #f8f9fa;
    }

    #faqList .remove-faq {
        position: static;
        margin-left: 10px;
        flex-shrink: 0;
    }

    /* Settings panel scroll for long content */
    #section-settings {
        max-height: 500px;
        overflow-y: auto;
        padding-right: 5px;
    }

    #section-settings::-webkit-scrollbar {
        width: 6px;
    }

    #section-settings::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    #section-settings::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    #section-settings::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>

@endsection
