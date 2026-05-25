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

<script>
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
</script>

<?php
\App\Core\Renderer::view(dirname(__DIR__) . '/partials/product-edit-modal.php');
?>
