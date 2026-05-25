<?php
/**
 * Sprint Products List View - API driven
 */
?>

<h1>Sprint Products</h1>
<p class="muted">Products and variants available to order within each sprint.</p>

<div style="margin-bottom:16px;display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
  <select id="sprint-product-sprint-filter" class="form-group__select" style="width:220px;" onchange="fetchSprintProducts(1)">
    <option value="">All sprints</option>
  </select>
  <input
    id="sprint-product-search"
    type="text"
    placeholder="Search sprint, product, variant or SKU..."
    style="padding:10px;width:310px;border:1px solid #ccc;border-radius:4px;"
    onkeyup="if (event.key === 'Enter') fetchSprintProducts(1)"
  >
  <button onclick="fetchSprintProducts(1)" class="btn btn--secondary">Search</button>
  <button onclick="clearSprintProductFilter()" class="btn btn--secondary">Clear</button>
  <button onclick="openSprintProductModal()" class="btn btn--primary" style="margin-left:auto;">+ Add Offering</button>
</div>

<div id="sprint-products-loading">Loading sprint products...</div>
<div class="table-wrapper" id="sprint-products-table-wrapper" style="display:none;">
  <table class="sprint-table">
    <thead>
      <tr>
        <th>Sprint</th>
        <th>Product / Variant</th>
        <th>SKU</th>
        <th>Price</th>
        <th>Discount</th>
        <th>Stock</th>
        <th>Status</th>
        <th style="width:210px;">Actions</th>
      </tr>
    </thead>
    <tbody id="sprint-products-tbody"></tbody>
  </table>
</div>
<div id="sprint-products-pagination"></div>
<div id="sprint-products-empty" class="muted" style="display:none;">No sprint products found.</div>

<script>
let currentSprintProductPage = 1;

document.addEventListener('DOMContentLoaded', async () => {
  await loadSprintProductFilter();
  fetchSprintProducts();
});

async function loadSprintProductFilter() {
  const filter = document.getElementById('sprint-product-sprint-filter');
  try {
    const res = await fetch('/api/admin/sprints?per_page=100');
    const json = await res.json();
    if (!json.success) return;
    json.data.forEach(sprint => {
      const option = document.createElement('option');
      option.value = sprint.id;
      option.textContent = sprint.name;
      filter.appendChild(option);
    });
  } catch (err) {
    return;
  }
}

async function fetchSprintProducts(page = 1) {
  const loading = document.getElementById('sprint-products-loading');
  const wrapper = document.getElementById('sprint-products-table-wrapper');
  const tbody = document.getElementById('sprint-products-tbody');
  const empty = document.getElementById('sprint-products-empty');
  const search = document.getElementById('sprint-product-search').value.trim();
  const sprintId = document.getElementById('sprint-product-sprint-filter').value;

  currentSprintProductPage = page;
  loading.style.display = 'block';
  loading.textContent = 'Loading sprint products...';
  wrapper.style.display = 'none';
  empty.style.display = 'none';
  tbody.innerHTML = '';

  let url = `/api/admin/sprint-products?page=${page}&per_page=25`;
  if (search) url += `&search=${encodeURIComponent(search)}`;
  if (sprintId) url += `&sprint_id=${encodeURIComponent(sprintId)}`;

  try {
    const res = await fetch(url);
    const json = await res.json();
    loading.style.display = 'none';

    if (!json.success) {
      loading.style.display = 'block';
      loading.textContent = json.error || 'Failed to load sprint products.';
      return;
    }

    if (!json.data.length) {
      empty.style.display = 'block';
      renderSprintProductPagination(json.pagination);
      return;
    }

    wrapper.style.display = 'block';
    json.data.forEach(item => {
      const variantName = item.variant_name ? ` / ${escapeSprintProductHtml(item.variant_name)}` : '';
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${escapeSprintProductHtml(item.sprint_name)}</td>
        <td>${escapeSprintProductHtml(item.product_name)}${variantName}</td>
        <td><code>${escapeSprintProductHtml(item.sku)}</code></td>
        <td>${escapeSprintProductHtml(item.price_cents)}</td>
        <td>${escapeSprintProductHtml(item.discount_cents)}</td>
        <td>${escapeSprintProductHtml(item.stock)}</td>
        <td>${escapeSprintProductHtml(item.status)}</td>
        <td>
          <a href="/admin/sprint-products/${item.id}" class="btn btn--secondary" style="padding:4px 8px;font-size:0.8rem;">View Detail</a>
          <button onclick="openSprintProductModal(${item.id})" class="btn btn--secondary" style="padding:4px 8px;font-size:0.8rem;">Edit</button>
          <button onclick="deleteSprintProduct(${item.id}, this)" class="btn" style="padding:4px 8px;font-size:0.8rem;background:#dc3545;color:white;">Delete</button>
        </td>
      `;
      tbody.appendChild(row);
    });
    renderSprintProductPagination(json.pagination);
  } catch (err) {
    loading.textContent = 'Failed to load sprint products.';
  }
}

function renderSprintProductPagination(pagination) {
  const container = document.getElementById('sprint-products-pagination');
  container.innerHTML = '';
  if (!pagination || pagination.total_pages <= 1) return;

  let html = '<div style="margin-top:16px;display:flex;gap:8px;align-items:center;justify-content:center;">';
  if (pagination.current_page > 1) {
    html += `<button onclick="fetchSprintProducts(${pagination.current_page - 1})" class="btn btn--secondary">Previous</button>`;
  }
  html += `<span>Page ${pagination.current_page} of ${pagination.total_pages}</span>`;
  if (pagination.current_page < pagination.total_pages) {
    html += `<button onclick="fetchSprintProducts(${pagination.current_page + 1})" class="btn btn--secondary">Next</button>`;
  }
  container.innerHTML = html + '</div>';
}

function clearSprintProductFilter() {
  document.getElementById('sprint-product-search').value = '';
  document.getElementById('sprint-product-sprint-filter').value = '';
  fetchSprintProducts(1);
}

function escapeSprintProductHtml(text) {
  const div = document.createElement('div');
  div.textContent = text ?? '';
  return div.innerHTML;
}

async function deleteSprintProduct(id, button) {
  if (!confirm('Are you sure you want to remove this product from the sprint?')) return;
  button.disabled = true;
  try {
    const res = await fetch(`/api/admin/sprint-products/${id}`, { method: 'DELETE' });
    const json = await res.json();
    if (json.success) {
      fetchSprintProducts(currentSprintProductPage);
      return;
    }
    alert(json.error || 'Failed to delete sprint product.');
    button.disabled = false;
  } catch (err) {
    alert('Network error.');
    button.disabled = false;
  }
}

function refreshSprintProductPage() {
  fetchSprintProducts(currentSprintProductPage);
}
</script>

<?php
\App\Core\Renderer::view(dirname(__DIR__) . '/partials/sprint-product-modal.php');
?>
