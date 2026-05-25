<?php
/**
 * Product Detail View
 */
$p = $product ?? [];
?>

<h1>Product Detail</h1>

<div style="margin-bottom:20px;">
  <a href="/admin/products" class="btn btn--secondary">&larr; Back to List</a>
  <button onclick="openProductEditModal(<?= (int) ($p['id'] ?? 0) ?>)" class="btn btn--primary" style="margin-left:8px;">Edit</button>
  <button onclick="deleteProductFromDetail(<?= (int) ($p['id'] ?? 0) ?>, this)" class="btn" style="margin-left:8px;background:#dc3545;color:white;">Delete</button>
</div>

<div class="table-wrapper">
  <table class="sprint-table" style="max-width:800px;">
    <tr>
      <th style="width:180px;">SKU</th>
      <td><code><?= htmlspecialchars($p['sku'] ?? '') ?></code></td>
    </tr>
    <tr>
      <th>Name</th>
      <td><?= htmlspecialchars($p['name'] ?? '') ?></td>
    </tr>
    <tr>
      <th>Description</th>
      <td><?= nl2br(htmlspecialchars($p['description'] ?? '')) ?></td>
    </tr>
    <tr>
      <th>Status</th>
      <td><?= htmlspecialchars($p['status'] ?? '') ?></td>
    </tr>
    <tr>
      <th>Images (JSON)</th>
      <td><pre style="margin:0;white-space:pre-wrap;"><?= htmlspecialchars($p['images'] ?? '-') ?></pre></td>
    </tr>
    <tr>
      <th>Created At</th>
      <td><?= htmlspecialchars($p['created_at'] ?? '') ?></td>
    </tr>
    <tr>
      <th>Updated At</th>
      <td><?= htmlspecialchars($p['updated_at'] ?? '') ?></td>
    </tr>
  </table>
</div>

<section style="margin-top:32px;">
  <div style="display:flex;gap:12px;align-items:center;justify-content:space-between;">
    <div>
      <h2 style="margin-bottom:4px;">Product Variants</h2>
      <p class="muted" style="margin-top:0;">Master variants used when this product is offered in a sprint.</p>
    </div>
    <button onclick="openProductVariantModal()" class="btn btn--primary">+ Add Variant</button>
  </div>

  <div id="product-variants-loading">Loading variants...</div>
  <div class="table-wrapper" id="product-variants-table-wrapper" style="display:none;">
    <table class="sprint-table">
      <thead>
        <tr>
          <th>SKU</th>
          <th>Name</th>
          <th>Attributes</th>
          <th>Status</th>
          <th style="width:150px;">Actions</th>
        </tr>
      </thead>
      <tbody id="product-variants-tbody"></tbody>
    </table>
  </div>
  <div id="product-variants-empty" class="muted" style="display:none;">No variants yet.</div>
</section>

<script>
const productId = <?= (int) ($p['id'] ?? 0) ?>;

document.addEventListener('DOMContentLoaded', () => {
  fetchProductVariants();
});

async function deleteProductFromDetail(id, button) {
  if (!confirm('Are you sure you want to delete this product?')) {
    return;
  }

  button.disabled = true;
  try {
    const res = await fetch(`/api/admin/products/${id}`, { method: 'DELETE' });
    const json = await res.json();

    if (json.success) {
      window.location.href = '/admin/products';
      return;
    }

    alert(json.error || 'Failed to delete product.');
    button.disabled = false;
  } catch (err) {
    alert('Network error.');
    button.disabled = false;
  }
}

async function fetchProductVariants() {
  const loading = document.getElementById('product-variants-loading');
  const tableWrapper = document.getElementById('product-variants-table-wrapper');
  const empty = document.getElementById('product-variants-empty');
  const tbody = document.getElementById('product-variants-tbody');

  loading.style.display = 'block';
  loading.textContent = 'Loading variants...';
  tableWrapper.style.display = 'none';
  empty.style.display = 'none';
  tbody.innerHTML = '';

  try {
    const res = await fetch(`/api/admin/products/${productId}/variants`);
    const json = await res.json();
    loading.style.display = 'none';

    if (!json.success) {
      loading.style.display = 'block';
      loading.textContent = json.error || 'Failed to load variants.';
      return;
    }

    if (!json.data || json.data.length === 0) {
      empty.style.display = 'block';
      return;
    }

    tableWrapper.style.display = 'block';
    json.data.forEach(variant => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td><code>${escapeVariantHtml(variant.sku || '-')}</code></td>
        <td>${escapeVariantHtml(variant.name)}</td>
        <td><code>${escapeVariantHtml(variant.attributes || '-')}</code></td>
        <td>${escapeVariantHtml(variant.status)}</td>
        <td>
          <button onclick="openProductVariantModal(${variant.id})" class="btn btn--secondary" style="padding:4px 8px;font-size:0.8rem;">Edit</button>
          <button onclick="deleteProductVariant(${variant.id}, this)" class="btn" style="padding:4px 8px;font-size:0.8rem;background:#dc3545;color:white;">Delete</button>
        </td>
      `;
      tbody.appendChild(row);
    });
  } catch (err) {
    loading.textContent = 'Failed to load variants.';
  }
}

function escapeVariantHtml(text) {
  const div = document.createElement('div');
  div.textContent = text ?? '';
  return div.innerHTML;
}

async function openProductVariantModal(id = null) {
  const modal = document.getElementById('product-variant-modal');
  const form = document.getElementById('product-variant-form');
  const result = document.getElementById('product-variant-result');

  form.reset();
  document.getElementById('product-variant-id').value = '';
  document.getElementById('product-variant-modal-title').textContent = id ? 'Edit Variant' : 'Add Variant';
  result.textContent = '';
  modal.style.display = 'flex';

  if (!id) {
    return;
  }

  result.textContent = 'Loading...';
  try {
    const res = await fetch(`/api/admin/products/${productId}/variants/${id}`);
    const json = await res.json();
    if (!json.success || !json.data) {
      result.textContent = json.error || 'Failed to load variant.';
      return;
    }

    const variant = json.data;
    document.getElementById('product-variant-id').value = variant.id;
    document.getElementById('product-variant-sku').value = variant.sku || '';
    document.getElementById('product-variant-name').value = variant.name || '';
    document.getElementById('product-variant-attributes').value = variant.attributes || '';
    result.textContent = '';
  } catch (err) {
    result.textContent = 'Network error.';
  }
}

function closeProductVariantModal() {
  document.getElementById('product-variant-modal').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('product-variant-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const id = document.getElementById('product-variant-id').value;
    const result = document.getElementById('product-variant-result');
    const payload = {
      sku: this.sku.value,
      name: this.name.value,
      attributes: this.attributes.value
    };
    const url = id
      ? `/api/admin/products/${productId}/variants/${id}`
      : `/api/admin/products/${productId}/variants`;
    const method = id ? 'PUT' : 'POST';

    result.textContent = 'Saving...';
    try {
      const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();

      if (!json.success) {
        result.textContent = json.error || 'Failed to save variant.';
        return;
      }

      closeProductVariantModal();
      fetchProductVariants();
    } catch (err) {
      result.textContent = 'Network error.';
    }
  });
});

async function deleteProductVariant(id, button) {
  if (!confirm('Are you sure you want to delete this variant?')) {
    return;
  }

  button.disabled = true;
  try {
    const res = await fetch(`/api/admin/products/${productId}/variants/${id}`, { method: 'DELETE' });
    const json = await res.json();
    if (json.success) {
      fetchProductVariants();
      return;
    }

    alert(json.error || 'Failed to delete variant.');
    button.disabled = false;
  } catch (err) {
    alert('Network error.');
    button.disabled = false;
  }
}
</script>

<?php
\App\Core\Renderer::view(dirname(__DIR__) . '/partials/product-edit-modal.php');
\App\Core\Renderer::view(dirname(__DIR__) . '/partials/product-variant-modal.php');
?>
