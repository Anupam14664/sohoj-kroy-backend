<script>
    // page-validation.js
    class PageValidation {
        constructor() {
            this.saveBtn = document.getElementById('savePageBtn');
            this.form = document.getElementById('pageForm');
            this.init();
        }

        init() {
            this.bindEvents();
        }

        bindEvents() {
            this.saveBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.validateAndSubmit();
            });
        }

        validateAndSubmit() {
            const validation = this.validateForm();

            if (!validation.isValid) {
                this.showWarningModal(validation.missingFields);
                return;
            }

            this.showConfirmModal();
        }

        validateForm() {
            const pageName = document.getElementById('pageName').value.trim();
            const slug = document.getElementById('slug').value.trim();
            const productId = document.getElementById('product_id').value.trim();
            const sections = document.getElementById('sections_json').value;

            const missingFields = [];

            if (!pageName) missingFields.push('Page Name');
            if (!slug) missingFields.push('Slug');
            if (!productId) missingFields.push('Product');

            // Check if canvas has sections
            const canvas = document.getElementById('canvas');
            const sectionCards = canvas.querySelectorAll('.section-card');
            if (sectionCards.length === 0) {
                missingFields.push('At least one section');
            }

            return {
                isValid: missingFields.length === 0,
                missingFields: missingFields
            };
        }

        showWarningModal(missingFields) {
            const warningText = document.getElementById('warningText');
            warningText.textContent = `Please fill the following fields first: ${missingFields.join(', ')}`;

            const warningModal = new bootstrap.Modal(document.getElementById('warningModal'));
            warningModal.show();
        }

        showConfirmModal() {
            const pageName = document.getElementById('pageName').value.trim();
            const productName = document.getElementById('productSearch').value.trim();

            // Update confirm modal content
            const confirmContent = document.querySelector('#saveConfirmModal .modal-body');
            confirmContent.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Please review your page before saving:
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Page Name:</strong><br>
                        ${pageName}
                    </div>
                    <div class="col-md-6">
                        <strong>Product:</strong><br>
                        ${productName}
                    </div>
                </div>
                <div class="mt-3">
                    <strong>Sections Added:</strong>
                    <div id="sectionSummary" class="mt-2"></div>
                </div>
            `;

            this.updateSectionSummary();

            const modal = new bootstrap.Modal(document.getElementById('saveConfirmModal'));
            modal.show();

            // Bind confirm button
            document.querySelector('#saveConfirmModal .btn-success').onclick = () => {
                this.form.submit();
            };
        }

        updateSectionSummary() {
            const canvas = document.getElementById('canvas');
            const sectionCards = canvas.querySelectorAll('.section-card');
            const summaryContainer = document.getElementById('sectionSummary');

            if (sectionCards.length === 0) {
                summaryContainer.innerHTML = '<span class="text-muted">No sections added</span>';
                return;
            }

            let summaryHTML = '<ul class="list-group list-group-flush">';

            sectionCards.forEach((card, index) => {
                const type = card.dataset.type;
                const formattedName = type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

                summaryHTML += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas ${this.getSectionIcon(type)} me-2 text-primary"></i>
                            ${formattedName}
                        </span>
                        <span class="badge bg-primary rounded-pill">${index + 1}</span>
                    </li>
                `;
            });

            summaryHTML += '</ul>';
            summaryContainer.innerHTML = summaryHTML;
        }

        getSectionIcon(type) {
            const icons = {
                'hero_product_banner': 'fa-star',
                'product_video_slide': 'fa-video',
                'product_feature': 'fa-image',
                'premium_product_promotion': 'fa-crown',
                'why_choose_us': 'fa-trophy',
                'featured_product': 'fa-fire',
                'product_highlight': 'fa-bullseye',
                'certification': 'fa-certificate',
                'customer_review': 'fa-comment',
                'exclusive_product': 'fa-gem',
                'faq': 'fa-question-circle'
            };
            return icons[type] || 'fa-cube';
        }
    }

    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('savePageBtn')) {
            window.pageValidation = new PageValidation();
        }
    });
</script>
