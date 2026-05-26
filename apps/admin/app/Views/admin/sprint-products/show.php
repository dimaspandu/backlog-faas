<?php
$sp = $sprintProduct ?? [];
?>

<h1>Sprint Product Detail</h1>

<div style="margin-bottom:20px;">
  <a href="/admin/sprint-products" class="btn btn--secondary">&larr; Back to List</a>
  <button onclick="openSprintProductModal(<?= (int) ($sp['id'] ?? 0) ?>)" class="btn btn--primary" style="margin-left:8px;">Edit</button>
  <button onclick="deleteSprintProductFromDetail(<?= (int) ($sp['id'] ?? 0) ?>, this)" class="btn" style="margin-left:8px;background:#dc3545;color:white;">Delete</button>
</div>

<div class="table-wrapper">
  <table class="sprint-table" style="max-width:850px;">
    <tr><th style="width:200px;">Sprint</th><td><?= htmlspecialchars($sp['sprint_name'] ?? '') ?></td></tr>
    <tr><th>Product</th><td><?= htmlspecialchars($sp['product_name'] ?? '') ?></td></tr>
    <tr><th>Variant</th><td><?= htmlspecialchars($sp['variant_name'] ?? '-') ?></td></tr>
    <tr><th>SKU</th><td><code><?= htmlspecialchars($sp['sku'] ?? '') ?></code></td></tr>
    <tr><th>Price (cents)</th><td><?= htmlspecialchars((string) ($sp['price_cents'] ?? '0')) ?></td></tr>
    <tr><th>List Price (cents)</th><td><?= htmlspecialchars((string) ($sp['list_price_cents'] ?? '0')) ?></td></tr>
    <tr><th>Discount (cents)</th><td><?= htmlspecialchars((string) ($sp['discount_cents'] ?? '0')) ?></td></tr>
    <tr><th>Stock</th><td><?= htmlspecialchars((string) ($sp['stock'] ?? '0')) ?></td></tr>
    <tr><th>Reserved</th><td><?= htmlspecialchars((string) ($sp['reserved_quantity'] ?? '0')) ?></td></tr>
    <tr><th>Sold</th><td><?= htmlspecialchars((string) ($sp['stock_sold'] ?? '0')) ?></td></tr>
    <tr><th>Variant Snapshot</th><td><pre style="margin:0;white-space:pre-wrap;"><?= htmlspecialchars($sp['variant'] ?? '-') ?></pre></td></tr>
    <tr><th>Status</th><td><?= htmlspecialchars($sp['status'] ?? '') ?></td></tr>
  </table>
</div>

<script>
async function deleteSprintProductFromDetail(id, button) {
  if (!confirm('Are you sure you want to remove this product from the sprint?')) return;
  button.disabled = true;
  try {
    const res = await fetch(`/api/admin/sprint-products/${id}`, { method: 'DELETE' });
    const json = await res.json();
    if (json.success) {
      window.location.href = '/admin/sprint-products';
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
  window.location.reload();
}
</script>

<?php
\App\Core\Renderer::view(dirname(__DIR__) . '/partials/sprint-product-modal.php');
?>
