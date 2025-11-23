@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h2>Edit Page</h2>
    <form id="pageForm" action="{{ route('admin.pages.update', $page->id) }}" method="POST" enctype="multipart/form-data" style="width: 100%">
        @csrf
        @method('PUT')

        <!-- Page Name -->
        <div class="mb-3">
            <label class="form-label">Page Name</label>
            <input name="name" id="pageName" class="form-control" value="{{ old('name', $page->name) }}" required>
        </div>

        <!-- Slug -->
        <div class="mb-3">
            <label class="form-label">Slug</label>
            <input name="slug" id="slug" class="form-control" value="{{ old('slug', $page->slug) }}">
        </div>

        <!-- Product Select -->
        <div class="mb-3">
            <label class="form-label">Select Product</label>
            <input type="text" id="productSearch" class="form-control" placeholder="Type to search products..." value="{{ $page->product ? $page->product->name : '' }}">
            <div id="productResults" class="list-group mt-1" style="max-height:200px; overflow-y:auto;"></div>
            <input type="hidden" name="product_id" id="product_id" value="{{ old('product_id', $page->product_id) }}">
        </div>

        <!-- Status -->
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="1" {{ old('status', $page->status) == 1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('status', $page->status) == 0 ? 'selected' : '' }}>Inactive</option>
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
                    <p class="text-muted" id="emptyMessage">Drag sections here</p>
                </div>
                <div class="mt-3 d-flex gap-2">
                    {{-- <button type="submit" class="btn btn-primary">Update Page</button> --}}
                    <button type="button" id="savePageBtn" class="btn btn-primary">Update Page</button>
                    @include('admin.modal.savecofirmmodal')

                    <a href="{{ route('admin.pages.index') }}" class="btn btn-secondary">Cancel</a>
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
        const emptyMessage = document.getElementById('emptyMessage');

        // Auto-generate slug from page name
        pageNameInput.addEventListener('input', () => {
            slugInput.value = pageNameInput.value.trim().toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
        });

        new Sortable(canvas, { group:'page', animation:150, onEnd:updateSectionsJSON });
        new Sortable(sectionList, { group:{name:'page',pull:'clone',put:false}, sort:false });

        document.querySelectorAll('.draggable-section').forEach(el=>{
            el.addEventListener('click',()=>addSection(el.dataset.type));
        });

        // Load existing sections from database
        const existingSections = {!! json_encode($page->sections->sortBy('position')->values()->all()) !!};
        console.log('Existing sections:', existingSections); // Debug

        if(existingSections && existingSections.length > 0) {
            existingSections.forEach(section => {
                loadSection(section);
            });
            emptyMessage.style.display = 'none';
        }

        function loadSection(sectionData) {
            const id = sectionData.id || 's_' + Date.now();
            const type = sectionData.type;

            // Parse settings if it's a string (from database)
            let settings = sectionData.settings || {};
            if (typeof settings === 'string') {
                try {
                    settings = JSON.parse(settings);
                } catch(e) {
                    console.error('Failed to parse settings:', e);
                    settings = {};
                }
            }

            const el = document.createElement('div');
            el.className = 'card mb-2 section-card';
            el.dataset.type = type;
            el.dataset.id = id;
            el._settings = settings;

            el.innerHTML = `
                <div class="card-body p-2 d-flex justify-content-between align-items-center">
                    <div><strong>${type.replace(/_/g,' ')}</strong></div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-edit">Edit</button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove">Remove</button>
                    </div>
                </div>`;

            el.querySelector('.btn-remove').onclick = () => {
                el.remove();
                if(canvas.querySelectorAll('.section-card').length === 0) {
                    emptyMessage.style.display = 'block';
                }
                updateSectionsJSON();
            };
            el.querySelector('.btn-edit').onclick = () => openSettings(el);

            canvas.appendChild(el);
            updateSectionsJSON();
        }

        function addSection(type){
            const id = 's_' + Date.now();
            const el = document.createElement('div');
            el.className = 'card mb-2 section-card';
            el.dataset.type = type;
            el.dataset.id = id;
            el._settings = {};
            el.innerHTML = `
                <div class="card-body p-2 d-flex justify-content-between align-items-center">
                    <div><strong>${type.replace(/_/g,' ')}</strong></div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-edit">Edit</button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove">Remove</button>
                    </div>
                </div>`;
            el.querySelector('.btn-remove').onclick = () => {
                el.remove();
                if(canvas.querySelectorAll('.section-card').length === 0) {
                    emptyMessage.style.display = 'block';
                }
                updateSectionsJSON();
            };
            el.querySelector('.btn-edit').onclick = () => openSettings(el);
            canvas.appendChild(el);
            emptyMessage.style.display = 'none';
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

            // Helper upload input function — add optional multiple param
            const uploadInput = (id='fileInput', multiple=false) => `<input type="file" id="${id}" class="form-control" ${multiple ? 'multiple' : ''} accept="image/png, image/jpeg, image/jpg">`;
            const uploadPreview = (url) => url ? `<img src="${url}" class="img-fluid mt-2" style="max-height:100px;">` : '';

            // Section-specific fields
            switch(type){
                case 'hero_product_banner':
                    const countdownDate = card._settings.countdownDate || '';
                    const dateValue = countdownDate ? countdownDate.split('T')[0] : '';
                    const timeValue = countdownDate ? countdownDate.split('T')[1]?.slice(0,5) : '';

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
                            <input type="date" id="countdownDate" class="form-control countdown-date" value="${dateValue}">
                        </div>

                        <div class="mt-2 mb-2 countdown-wrapper">
                            <label>Countdown Time</label>
                            <input type="time" id="countdownTime" class="form-control countdown-time" value="${timeValue}">
                        </div>
                        <label>Image <small class="text-muted" style="font-size:12px"></small></label>
                        <input type="file" id="heroImg" class="form-control" accept="image/png, image/jpg" >
                        <small class="text-muted" style="font-size:12px;">${sizeNote[type]}</small>
                        ${uploadPreview(card._settings.image)}
                        <div id="uploadStatus" class="text-muted small mt-1"></div>
                        <div class="mt-2"><label>CTA Text</label>
                        <input type="text" id="cta" class="form-control" value="${card._settings.cta||''}"></div>`;
                    break;

                case 'product_video_slide':
                    html += `<div><label>YouTube Links</label>
                        <div id="videoList"></div>
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
                        <div id="imagePreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                        <div id="uploadStatus" class="text-muted small mt-1"></div>
                        <div class="mt-2"><label>CTA Text</label><input type="text" id="cta" class="form-control" value="${card._settings.cta||''}"></div>`;
                    break;

                case 'premium_product_promotion':
                case 'featured_product':
                    html += `
                        <div class="mb-2"><label>Single Image</label>
                            <input type="file" id="featuredImage" class="form-control" accept="image/png">
                            <small class="text-muted">${sizeNote[type]}</small>
                            ${card._settings.image ? `<img src="${card._settings.image}" class="mt-2" style="max-height:100px;">` : ''}
                        </div>
                        <div class="mb-2"><label>Heading</label>
                            <input type="text" id="heading" class="form-control" value="${card._settings.heading||''}">
                        </div>

                        <div class="mb-2"><label>Description</label>
                            <textarea id="description" class="form-control">${card._settings.description||''}</textarea>
                        </div>

                        <div class="mb-2"><label>CTA Text</label>
                            <input type="text" id="cta" class="form-control" value="${card._settings.cta||''}">
                        </div>
                    `;
                    break;

                case 'product_highlight':
                    html += `
                        <div class="mb-2">
                            <label>Product Image</label>
                            <input type="file" id="highlightImage" class="form-control" accept="image/png">
                            <small class="text-muted" style="font-size:12px;">${sizeNote[type]}</small>
                            ${card._settings.image ? `<img src="${card._settings.image}" class="mt-2" style="max-height:100px;">` : ''}
                        </div>
                        <div class="mb-2"><label>Heading</label><input type="text" id="heading" class="form-control" value="${card._settings.heading||''}"></div>
                        <div class="mb-2"><label>Description</label><textarea id="description" class="form-control">${card._settings.description||''}</textarea></div>
                        <div class="mb-2"><label>CTA Text</label><input type="text" id="cta" class="form-control" value="${card._settings.cta||''}"></div>`;
                    if(type === 'product_highlight'){
                        html += `
                            <div><label>Short Points</label>
                                <div id="pointsList"></div>
                                <input type="text" id="newPoint" class="form-control mt-1" placeholder="Add short description">
                                <button id="addPoint" type="button" class="btn btn-sm btn-outline-primary mt-1">Add</button>
                            </div>`;
                    }
                    break;

                case 'why_choose_us':
                    html += `
                        <div><label>Heading</label><input type="text" id="heading" class="form-control" value="${card._settings.heading||''}"></div>
                        <div id="whyList" class="mt-2"></div>
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
                        </div>
                    `;
                    break;

                case 'faq':
                    html += `<div><label>Heading</label><input type="text" id="heading" class="form-control" value="${card._settings.heading||''}"></div>
                        <div id="faqList"></div>
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

            // ---------- Upload logic ----------
            if(type === 'hero_product_banner'){
                const input = document.getElementById('heroImg');
                const status = document.getElementById('uploadStatus');
                if (!card._settings.image || card._settings.image.length === 0) {
                    card._settings.image = null;
                }
                input.onchange = async (e)=>{
                    const f = e.target.files[0];
                    if(!f) return;
                    const fd = new FormData(); fd.append('file', f);
                    status.textContent='Uploading...';
                    const res = await fetch(uploadUrl,{method:'POST',headers:{'X-CSRF-TOKEN':csrf},body:fd});
                    const d=await res.json();
                    if(d.success){ card._settings.image=d.url; status.innerHTML=`<img src="${d.url}" class="mt-2" style="max-height:100px;"> Uploaded`; }
                };
            }

            if (['product_feature', 'certification', 'customer_review'].includes(type)) {
                const input = document.getElementById('multiImage');
                const preview = document.getElementById('imagePreview');
                const status = document.getElementById('uploadStatus');

                card._settings.images = card._settings.images || [];

                // Function to render one image box
                function renderImage(url, index = null) {
                    const wrap = document.createElement('div');
                    wrap.className = 'img-wrapper';

                    const img = document.createElement('img');
                    img.src = url;

                    const close = document.createElement('div');
                    close.className = 'img-close';
                    close.innerHTML = '✖';

                    close.onclick = () => {
                        wrap.remove();
                        card._settings.images = card._settings.images.filter(i => i !== url);
                    };

                    wrap.appendChild(img);
                    wrap.appendChild(close);
                    preview.appendChild(wrap);
                }

                // Render existing images
                preview.innerHTML = '';
                card._settings.images.forEach(renderImage);

                // Upload event
                input.onchange = async e => {
                    for (const f of e.target.files) {
                        const fd = new FormData();
                        fd.append('file', f);

                        status.textContent = 'Uploading...';

                        const res = await fetch(uploadUrl, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf },
                            body: fd
                        });

                        const d = await res.json();

                        if (d.success) {
                            card._settings.images.push(d.url);
                            renderImage(d.url);
                        }
                        status.textContent = 'Uploaded';
                    }
                };
            }

            if(type==='product_video_slide'){
                const list=document.getElementById('videoList');
                card._settings.videos=card._settings.videos||[];
                list.innerHTML = '';
                card._settings.videos.forEach((v, idx)=>{
                    const div=document.createElement('div');
                    div.className = 'd-flex justify-content-between align-items-center mb-1';
                    div.innerHTML = `
                        <span>${v}</span>
                        <button type="button" class="btn btn-sm btn-danger" data-idx="${idx}">Remove</button>
                    `;
                    div.querySelector('button').onclick = function() {
                        card._settings.videos.splice(this.dataset.idx, 1);
                        openSettings(card);
                    };
                    list.appendChild(div);
                });
                document.getElementById('addVideo').onclick=()=>{
                    const val=document.getElementById('newVideo').value.trim();
                    if(val){
                        card._settings.videos.push(val);
                        openSettings(card);
                        document.getElementById('newVideo').value='';
                    }
                };
            }

            if(type==='why_choose_us'){
                const list=document.getElementById('whyList');
                card._settings.items=card._settings.items||[];
                list.innerHTML = '';
                card._settings.items.forEach((i, idx)=>{
                    const div=document.createElement('div');
                    div.className = 'd-flex justify-content-between align-items-center mb-2';
                    div.innerHTML = `
                        <div><img src="${i.image}" style="height:40px;"> ${i.desc}</div>
                        <button type="button" class="btn btn-sm btn-danger" data-idx="${idx}">Remove</button>
                    `;
                    div.querySelector('button').onclick = function() {
                        card._settings.items.splice(this.dataset.idx, 1);
                        openSettings(card);
                    };
                    list.appendChild(div);
                });
                document.getElementById('addWhy').onclick=async()=>{
                    const f=document.getElementById('whyImage').files[0];
                    const d=document.getElementById('whyDesc').value;
                    if(!f||!d)return;
                    const fd=new FormData(); fd.append('file',f);
                    const res=await fetch(uploadUrl,{method:'POST',headers:{'X-CSRF-TOKEN':csrf},body:fd});
                    const j=await res.json();
                    if(j.success){
                        card._settings.items.push({image:j.url,desc:d});
                        openSettings(card);
                        document.getElementById('whyDesc').value='';
                    }
                };
            }

            if (type === 'premium_product_promotion') {
                const input = document.getElementById('featuredImage');
                input.onchange = async (e)=>{
                    const f = e.target.files[0];
                    if(!f) return;

                    const fd = new FormData();
                    fd.append('file', f);

                    const res = await fetch(uploadUrl, {
                        method: 'POST',
                        headers:{'X-CSRF-TOKEN': csrf},
                        body: fd
                    });

                    const d = await res.json();
                    if(d.success){
                        card._settings.image = d.url;
                        openSettings(card);
                    }
                };
            }

            if(type === 'featured_product'){
                const input = document.getElementById('featuredImage');
                input.onchange = async (e)=>{
                    const f = e.target.files[0];
                    if(!f) return;

                    const fd = new FormData();
                    fd.append('file', f);

                    const res = await fetch(uploadUrl, {
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN': csrf},
                        body: fd
                    });

                    const d = await res.json();
                    if(d.success){
                        card._settings.image = d.url;
                        openSettings(card);
                    }
                }
            }

            if(type==='product_highlight'){
                const list=document.getElementById('pointsList');
                card._settings.points=card._settings.points||[];
                list.innerHTML = '';
                card._settings.points.forEach((p, idx)=>{
                    const div=document.createElement('div');
                    div.className = 'd-flex justify-content-between align-items-center mb-1';
                    div.innerHTML = `
                        <span>${p}</span>
                        <button type="button" class="btn btn-sm btn-danger" data-idx="${idx}">Remove</button>
                    `;
                    div.querySelector('button').onclick = function() {
                        card._settings.points.splice(this.dataset.idx, 1);
                        openSettings(card);
                    };
                    list.appendChild(div);
                });
                document.getElementById('addPoint').onclick=()=>{
                    const val=document.getElementById('newPoint').value;
                    if(val){
                        card._settings.points.push(val);
                        openSettings(card);
                        document.getElementById('newPoint').value='';
                    }
                }
                const input = document.getElementById('highlightImage');
                input.onchange = async (e)=>{
                    const f = e.target.files[0];
                    if(!f) return;

                    const fd = new FormData();
                    fd.append('file', f);

                    const res = await fetch(uploadUrl, {
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN': csrf},
                        body: fd
                    });

                    const d = await res.json();
                    if(d.success){
                        card._settings.image = d.url;
                        openSettings(card);
                    }
                }
            }

            if(type === 'exclusive_product'){
                const input = document.getElementById('exclusiveImage');
                input.onchange = async (e)=>{
                    const f = e.target.files[0];
                    if(!f) return;

                    const fd = new FormData();
                    fd.append('file', f);

                    const res = await fetch(uploadUrl, {
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN': csrf},
                        body: fd
                    });

                    const d = await res.json();
                    if(d.success){
                        card._settings.image = d.url;
                        openSettings(card);
                    }
                }
            }

            if(type==='faq'){
                const list=document.getElementById('faqList');
                card._settings.faqs=card._settings.faqs||[];
                list.innerHTML = '';
                card._settings.faqs.forEach((f, idx)=>{
                    const div=document.createElement('div');
                    div.className = 'mb-2';
                    div.innerHTML = `
                        <div class="d-flex justify-content-between">
                            <div><strong>Q:</strong>${f.q}<br><strong>A:</strong>${f.a}</div>
                            <button type="button" class="btn btn-sm btn-danger" data-idx="${idx}">Remove</button>
                        </div>
                        <hr>
                    `;
                    div.querySelector('button').onclick = function() {
                        card._settings.faqs.splice(this.dataset.idx, 1);
                        openSettings(card);
                    };
                    list.appendChild(div);
                });
                document.getElementById('addFaq').onclick=()=>{
                    const q=document.getElementById('faqQ').value, a=document.getElementById('faqA').value;
                    if(q&&a){
                        card._settings.faqs.push({q,a});
                        openSettings(card);
                        document.getElementById('faqQ').value='';
                        document.getElementById('faqA').value='';
                    }
                };
            }

            document.getElementById('saveSettings').onclick = () => {
                if (document.getElementById('cta')) card._settings.cta = document.getElementById('cta').value;
                if (document.getElementById('heading')) card._settings.heading = document.getElementById('heading').value;
                if (document.getElementById('description')) card._settings.description = document.getElementById('description').value;
                if (document.getElementById('cta1Text')) card._settings.cta1Text = document.getElementById('cta1Text').value;
                if (document.getElementById('cta2Text')) card._settings.cta2Text = document.getElementById('cta2Text').value;

                if (card.dataset.type === 'hero_product_banner') {
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
            };

            document.getElementById('cancelSettings').onclick=()=>settingsPane.innerHTML='<p class="text-muted">Cancelled</p>';
        }

        function updateSectionsJSON() {
            const sections = [];

            canvas.querySelectorAll('.section-card').forEach((card, index) => {

                // Safety: settings অবশ্যই object হবে
                if (!card._settings || typeof card._settings !== "object" || Array.isArray(card._settings)) {
                    card._settings = {};
                }

                // Object এর ভিতরের empty array/null → clean করে দেবে
                const cleanedSettings = {};

                Object.keys(card._settings).forEach(key => {
                    const val = card._settings[key];

                    // যদি value null → skip
                    if (val === null) return;

                    // যদি empty array → skip
                    if (Array.isArray(val) && val.length === 0) return;

                    // normal valid data set
                    cleanedSettings[key] = val;
                });

                // যদি cleanedSettings empty → null save করো
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

        document.getElementById('btnPreview').onclick = () => {
            updateSectionsJSON();
            const json = document.getElementById('sections_json').value;

            const w = window.open('', '_blank');

            // Add close icon + styles + JSON preview
            w.document.write(`
                <style>
                    body{ font-family:Arial; margin:0; }
                    #closeBtn{
                        position:fixed;
                        top:10px;
                        right:10px;
                        background:#ff4d4d;
                        color:white;
                        border:none;
                        padding:6px 12px;
                        border-radius:4px;
                        cursor:pointer;
                        font-size:14px;
                        z-index:9999;
                    }
                    pre{ padding:20px; white-space:pre-wrap; }
                </style>

                <button id="closeBtn">✖ Close</button>
                <pre>${JSON.stringify(JSON.parse(json), null, 2)}</pre>

                <script>
                    document.getElementById('closeBtn').onclick = () => window.close();
                <\/script>
            `);
        };
    });
</script>

<script>
    const products = @json($products);
    const productSearch = document.getElementById('productSearch');
    const productResults = document.getElementById('productResults');
    const productIdInput = document.getElementById('product_id');

    productSearch.addEventListener('input', () => {
        const query = productSearch.value.toLowerCase().trim();
        productResults.innerHTML = '';

        if (!query) return;

        products
            .filter(p => p.name.toLowerCase().includes(query))
            .forEach(p => {
                const item = document.createElement('div');
                item.className = 'list-group-item list-group-item-action d-flex align-items-center gap-2';

                const imgSrc = p.image_url ? p.image_url : '/images/no-image.png';

                item.innerHTML = `
                    <img src="${imgSrc}" alt="${p.name}"
                         style="width:50px; height:50px; object-fit:cover; border-radius:4px;">
                    <div class="flex-grow-1">
                        <div class="fw-bold">${p.name}</div>
                        <div class="text-muted small">
                            Price: ${p.discount_price ? p.discount_price + ' ৳' : 'N/A'}
                        </div>
                    </div>
                `;
                item.dataset.id = p.id;

                item.addEventListener('click', () => {
                    productSearch.value = p.name;
                    productIdInput.value = p.id;
                    productResults.innerHTML = '';
                });

                productResults.appendChild(item);
            });
    });

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
@endsection
