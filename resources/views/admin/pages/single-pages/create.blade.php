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
        <div class="mb-3">
            <label class="form-label">Select Product</label>
            <input type="text" id="productSearch" class="form-control" placeholder="Type to search products...">
            <div id="productResults" class="list-group mt-1" style="max-height:200px; overflow-y:auto;"></div>
            <input type="hidden" name="product_id" id="product_id" value="{{ old('product_id', $page->product_id ?? '') }}">
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
                    <button type="submit" class="btn btn-primary">Save Page</button>
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
            el.querySelector('.btn-remove').onclick = () => { el.remove(); updateSectionsJSON(); };
            el.querySelector('.btn-edit').onclick = () => openSettings(el);
            canvas.appendChild(el);
            updateSectionsJSON();
        }

        // ---------- SETTINGS PANEL ----------
        function openSettings(card){
            const type = card.dataset.type;
            let html = `<h6 class="mb-2">Edit: ${type.replace(/_/g,' ')}</h6>`;

            // const uploadInput = (id='fileInput') => `<input type="file" id="${id}" class="form-control">`;
            // Helper upload input function — add optional multiple param
    const uploadInput = (id='fileInput', multiple=false) => `<input type="file" id="${id}" class="form-control" ${multiple ? 'multiple' : ''}>`;

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
                                value="${card._settings.countdownDate ? card._settings.countdownDate.split('T')[1].slice(0,5) : ''}">
                        </div>
                        <label>Image</label>
                        ${uploadInput('heroImg')}
                        ${uploadPreview(card._settings.image)}
                        <div id="uploadStatus" class="text-muted small mt-1"></div>
                        <div class="mt-2"><label>CTA Text</label><input type="text" id="cta" class="form-control" value="${card._settings.cta||''}"></div>`;
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
                    </div>
                    `;
                    break;

                case 'product_feature':
                case 'certification':
                case 'customer_review':
                    html += `
                        ${uploadInput('multiImage', true)}
                        <div id="imagePreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                        <div id="uploadStatus" class="text-muted small mt-1"></div>
                        <div class="mt-2"><label>CTA Text</label><input type="text" id="cta" class="form-control" value="${card._settings.cta||''}"></div>`;
                    break;

                case 'premium_product_promotion':
                case 'featured_product':
                    html += `
                        <div class="mb-2"><label>Heading</label>
                            <input type="text" id="heading" class="form-control" value="${card._settings.heading||''}">
                        </div>

                        <div class="mb-2"><label>Description</label>
                            <textarea id="description" class="form-control">${card._settings.description||''}</textarea>
                        </div>

                        <div class="mb-2"><label>Single Image</label>
                            <input type="file" id="featuredImage" class="form-control">
                            ${card._settings.image ? `<img src="${card._settings.image}" class="mt-2" style="max-height:100px;">` : ''}
                        </div>

                        <div class="mb-2"><label>CTA Text</label>
                            <input type="text" id="cta" class="form-control" value="${card._settings.cta||''}">
                        </div>
                    `;
                    break;

                case 'product_highlight':
                    html += `
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
                        ${uploadInput('whyImage')}
                        <input type="text" id="whyDesc" class="form-control mt-1" placeholder="Description">
                        <button id="addWhy" type="button" class="btn btn-sm btn-outline-primary mt-1">Add Item</button>`;
                    break;

                case 'exclusive_product':
                    html += `

                        <div class="mb-2">
                            <label>Product Image</label>
                            <input type="file" id="exclusiveImage" class="form-control">
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
                        <input type="text" id="faqA" class="form-control mt-1" placeholder="Answer">
                        <button id="addFaq" type="button" class="btn btn-sm btn-outline-primary mt-1">Add FAQ</button>`;
                    break;
            }

            html += `<div class="mt-3 d-flex gap-2"><button id="saveSettings" class="btn btn-primary btn-sm">Save</button>
                    <button id="cancelSettings" class="btn btn-secondary btn-sm">Cancel</button></div>`;
            settingsPane.innerHTML = html;

            // ---------- Upload logic ----------
            if(type === 'hero_product_banner'){
                const input = document.getElementById('heroImg');
                const status = document.getElementById('uploadStatus');
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

            if(['product_feature','certification','customer_review'].includes(type)){
                const input=document.getElementById('multiImage');
                const preview=document.getElementById('imagePreview');
                const status=document.getElementById('uploadStatus');
                card._settings.images=card._settings.images||[];
                card._settings.images.forEach(u=>{
                    const img=document.createElement('img'); img.src=u; img.style.height='50px'; preview.appendChild(img);
                });
                input.onchange=async e=>{
                    for(const f of e.target.files){
                        const fd=new FormData(); fd.append('file',f);
                        status.textContent='Uploading...';
                        const res=await fetch(uploadUrl,{method:'POST',headers:{'X-CSRF-TOKEN':csrf},body:fd});
                        const d=await res.json();
                        if(d.success){ card._settings.images.push(d.url); const img=document.createElement('img'); img.src=d.url; img.style.height='50px'; preview.appendChild(img); }
                        status.textContent='Uploaded';
                    }
                }
            }

            if(type==='product_video_slide'){
                const list=document.getElementById('videoList');
                card._settings.videos=card._settings.videos||[];
                card._settings.videos.forEach(v=>{ const div=document.createElement('div'); div.textContent=v; list.appendChild(div); });
                document.getElementById('addVideo').onclick=()=>{
                    const val=document.getElementById('newVideo').value.trim();
                    if(val){ card._settings.videos.push(val); const div=document.createElement('div'); div.textContent=val; list.appendChild(div); document.getElementById('newVideo').value=''; }
                };
            }

            if(type==='why_choose_us'){
                const list=document.getElementById('whyList');
                card._settings.items=card._settings.items||[];
                card._settings.items.forEach(i=>{
                    const div=document.createElement('div'); div.innerHTML=`<img src="${i.image}" style="height:40px;"> ${i.desc}`; list.appendChild(div);
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
                        const div=document.createElement('div'); div.innerHTML=`<img src="${j.url}" style="height:40px;"> ${d}`; list.appendChild(div);
                        document.getElementById('whyDesc').value='';
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
                card._settings.points.forEach(p=>{ const div=document.createElement('div'); div.textContent=p; list.appendChild(div); });
                document.getElementById('addPoint').onclick=()=>{
                    const val=document.getElementById('newPoint').value;
                    if(val){ card._settings.points.push(val); const div=document.createElement('div'); div.textContent=val; list.appendChild(div); document.getElementById('newPoint').value=''; }
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
                        // openSettings(card);
                    }
                }
            }


            if(type==='faq'){
                const list=document.getElementById('faqList');
                card._settings.faqs=card._settings.faqs||[];
                card._settings.faqs.forEach(f=>{ const div=document.createElement('div'); div.innerHTML=`<strong>Q:</strong>${f.q}<br><strong>A:</strong>${f.a}<hr>`; list.appendChild(div); });
                document.getElementById('addFaq').onclick=()=>{
                    const q=document.getElementById('faqQ').value, a=document.getElementById('faqA').value;
                    if(q&&a){ card._settings.faqs.push({q,a}); const div=document.createElement('div'); div.innerHTML=`<strong>Q:</strong>${q}<br><strong>A:</strong>${a}<hr>`; list.appendChild(div); document.getElementById('faqQ').value=''; document.getElementById('faqA').value=''; }
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
                        card._settings.countdownDate = `${date}${time}:00`;
                    } else if (date) {
                        card._settings.countdownDate = date;
                    } else {
                        card._settings.countdownDate = null;
                    }
                }



                settingsPane.innerHTML = '<p class="text-success">Settings Saved</p>';
                updateSectionsJSON();
            };

            document.getElementById('cancelSettings').onclick=()=>settingsPane.innerHTML='<p class="text-muted">Cancelled</p>';
        }

        function updateSectionsJSON(){
            const arr=[];
            canvas.querySelectorAll('.section-card').forEach((c,i)=>{
                arr.push({ id:c.dataset.id, type:c.dataset.type, position:i, settings:c._settings });
            });
            document.getElementById('sections_json').value=JSON.stringify(arr);
        }

        document.getElementById('btnPreview').onclick=()=>{
            updateSectionsJSON();
            const json=document.getElementById('sections_json').value;
            const w=window.open('','_blank');
            w.document.write('<pre>'+JSON.stringify(JSON.parse(json),null,2)+'</pre>');
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

                // fallback image if missing
                const imgSrc = p.image ? p.image : '/images/no-image.png';

                item.innerHTML = `
                    <img src="${imgSrc}" alt="${p.name}" style="width:50px; height:50px; object-fit:cover; border-radius:4px;">
                    <div class="flex-grow-1">
                        <div class="fw-bold">${p.name}</div>
                        <div class="text-muted small">Price: ${p.discount_price ? p.discount_price + ' ৳' : 'N/A'}</div>
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
</script>


@endsection
