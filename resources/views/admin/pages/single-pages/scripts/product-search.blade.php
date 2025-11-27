<script>
    // product-search.js
    class ProductSearch {
        constructor() {
            this.products = window.products || [];
            this.searchInput = document.getElementById('productSearch');
            this.resultsContainer = document.getElementById('productResults');
            this.productIdInput = document.getElementById('product_id');
            this.init();
        }

        init() {
            this.bindEvents();
        }

        bindEvents() {
            this.searchInput.addEventListener('input', () => this.handleSearch());

            document.addEventListener('click', (e) => {
                if (!this.resultsContainer.contains(e.target) && e.target !== this.searchInput) {
                    this.hideResults();
                }
            });
        }

        handleSearch() {
            const query = this.searchInput.value.toLowerCase().trim();
            this.resultsContainer.innerHTML = '';

            if (!query) {
                this.hideResults();
                return;
            }

            const filteredProducts = this.products.filter(p =>
                p.name.toLowerCase().includes(query)
            );

            if (filteredProducts.length === 0) {
                this.showNoResults();
                return;
            }

            this.displayResults(filteredProducts);
        }

        displayResults(products) {
            this.resultsContainer.innerHTML = '';

            products.forEach(product => {
                const item = this.createProductItem(product);
                this.resultsContainer.appendChild(item);
            });

            this.showResults();
        }

        createProductItem(product) {
            const item = document.createElement('div');
            item.className = 'list-group-item list-group-item-action product-item';
            item.style.cursor = 'pointer';

            const imgSrc = product.image_url || '/images/no-image.png';
            const price = product.discount_price ?
                `${product.discount_price} ৳` :
                (product.price ? `${product.price} ৳` : 'N/A');

            item.innerHTML = `
                <div class="d-flex align-items-center gap-3">
                    <img src="${imgSrc}" alt="${product.name}"
                        style="width:50px; height:50px; object-fit:cover; border-radius:4px;">
                    <div class="flex-grow-1">
                        <div class="fw-bold">${product.name}</div>
                        <div class="text-muted small">
                            <span class="me-2">Price: ${price}</span>
                            ${product.category ? `<span>Category: ${product.category.name}</span>` : ''}
                        </div>
                    </div>
                </div>`;

            item.addEventListener('click', () => this.selectProduct(product));

            return item;
        }

        selectProduct(product) {
            this.searchInput.value = product.name;
            this.productIdInput.value = product.id;
            this.hideResults();

            // Show success feedback
            this.showSelectionFeedback(product.name);
        }

        showSelectionFeedback(productName) {
            const feedback = document.createElement('div');
            feedback.className = 'alert alert-success alert-dismissible fade show mt-2';
            feedback.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>
                <strong>Selected:</strong> ${productName}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            this.searchInput.parentNode.appendChild(feedback);

            // Auto remove after 3 seconds
            setTimeout(() => {
                feedback.remove();
            }, 3000);
        }

        showNoResults() {
            this.resultsContainer.innerHTML = `
                <div class="list-group-item text-center text-muted">
                    <i class="fas fa-search me-2"></i>
                    No products found
                </div>`;
            this.showResults();
        }

        showResults() {
            this.resultsContainer.style.display = 'block';
        }

        hideResults() {
            this.resultsContainer.style.display = 'none';
        }
    }

    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('productSearch')) {
            window.productSearch = new ProductSearch();
        }
    });
</script>
