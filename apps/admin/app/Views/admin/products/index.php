<?php
/**
 * Products List View - API driven
 */
?>

<h1>Products</h1>

<div style="margin-bottom:16px;display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
  <input
    type="text"
    id="product-search"
    placeholder="Search by SKU or name..."
    style="padding:8px;width:280px;border:1px solid #ccc;border-radius:4px;"
    onkeyup="if (event.key === 'Enter') fetchProducts(1)"
  >
  <button onclick="fetchProducts(1)" class="btn btn--secondary">Search</button>
  <button onclick="clearProductSearch()" class="btn btn--secondary">Clear</button>

  <a href="/admin/products/new" class="btn btn--primary" style="margin-left:auto;">+ New Product</a>
</div>

<div id="products-loading">Loading products...</div>

<div class="table-wrapper" id="products-table-wrapper" style="display:none;">
  <table class="sprint-table">
    <thead>
      <tr>
        <th>SKU</th>
        <th>Name</th>
        <th>Description</th>
        <th>Status</th>
        <th style="width:220px;">Actions</th>
      </tr>
    </thead>
    <tbody id="products-tbody"></tbody>
  </table>
</div>

<div id="products-pagination"></div>

<div id="products-empty" style="display:none;">
  <p>No products found.</p>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  fetchProducts();
});

let currentProductPage = 1;

async function fetchProducts(page = 1) {
  const loading = document.getElementById('products-loading');
  const tableWrapper = document.getElementById('products-table-wrapper');
  const emptyMsg = document.getElementById('products-empty');
  const tbody = document.getElementById('products-tbody');
  const search = document.getElementById('product-search').value.trim();

  currentProductPage = page;
  tbody.innerHTML = '';
  loading.style.display = 'block';
  loading.textContent = 'Loading products...';
  tableWrapper.style.display = 'none';
  emptyMsg.style.display = 'none';

  let url = `/api/admin/products?page=${page}&per_page=25`;
  if (search) {
    url += `&search=${encodeURIComponent(search)}`;
  }

  try {
    const res = await fetch(url);
    const json = await res.json();
    loading.style.display = 'none';

    if (!json.success) {
      loading.style.display = 'block';
      loading.textContent = json.error || 'Failed to load products.';
      return;
    }

    if (!json.data || json.data.length === 0) {
      emptyMsg.style.display = 'block';
      renderProductPagination(json.pagination || {});
      return;
    }

    tableWrapper.style.display = 'block';
    json.data.forEach(product => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td><code>${escapeProductHtml(product.sku)}</code></td>
        <td>${escapeProductHtml(product.name)}</td>
        <td>${escapeProductHtml(product.description || '-')}</td>
        <td>${escapeProductHtml(product.status)}</td>
        <td>
          <a href="/admin/products/${product.id}" class="btn btn--secondary" style="padding:4px 8px;font-size:0.8rem;">View Detail</a>
          <button onclick="openProductEditModal(${product.id})" class="btn btn--secondary" style="padding:4px 8px;font-size:0.8rem;">Edit</button>
          <button onclick="deleteProduct(${product.id}, this)" class="btn" style="padding:4px 8px;font-size:0.8rem;background:#dc3545;color:white;">Delete</button>
        </td>
      `;
      tbody.appendChild(row);
    });

    renderProductPagination(json.pagination || {});
  } catch (err) {
    loading.style.display = 'block';
    loading.textContent = 'Failed to load products.';
  }
}

function renderProductPagination(pagination) {
  const container = document.getElementById('products-pagination');
  container.innerHTML = '';

  if (!pagination || pagination.total_pages <= 1) {
    return;
  }

  let html = '<div style="margin-top:16px;display:flex;gap:8px;align-items:center;justify-content:center;">';
  if (pagination.current_page > 1) {
    html += `<button onclick="fetchProducts(${pagination.current_page - 1})" class="btn btn--secondary">Previous</button>`;
  }
  html += `<span style="padding:0 12px;">Page ${pagination.current_page} of ${pagination.total_pages}</span>`;
  if (pagination.current_page < pagination.total_pages) {
    html += `<button onclick="fetchProducts(${pagination.current_page + 1})" class="btn btn--secondary">Next</button>`;
  }
  container.innerHTML = html + '</div>';
}

function clearProductSearch() {
  document.getElementById('product-search').value = '';
  fetchProducts(1);
}

function escapeProductHtml(text) {
  const div = document.createElement('div');
  div.textContent = text ?? '';
  return div.innerHTML;
}

async function deleteProduct(id, button) {
  if (!confirm('Are you sure you want to delete this product?')) {
    return;
  }

  button.disabled = true;
  try {
    const res = await fetch(`/api/admin/products/${id}`, { method: 'DELETE' });
    const json = await res.json();

    if (json.success) {
      fetchProducts(currentProductPage);
      return;
    }

    alert(json.error || 'Failed to delete product.');
    button.disabled = false;
  } catch (err) {
    alert('Network error.');
    button.disabled = false;
  }
}
</script>

<?php
\App\Core\Renderer::view(dirname(__DIR__) . '/partials/product-edit-modal.php');
?>
