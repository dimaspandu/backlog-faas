<?php
/**
 * Create Product Form - API driven
 */
?>

<h1>Create New Product</h1>

<form id="create-product-form" class="sprint-form">
  <div class="form-group">
    <label class="form-group__label">SKU *</label>
    <input class="form-group__input" name="sku" required>
  </div>

  <div class="form-group">
    <label class="form-group__label">Name *</label>
    <input class="form-group__input" name="name" required>
  </div>

  <div class="form-group">
    <label class="form-group__label">Description</label>
    <textarea class="form-group__textarea" name="description" rows="3"></textarea>
  </div>

  <div class="form-group">
    <label class="form-group__label">Images (JSON)</label>
    <textarea class="form-group__textarea" name="images" rows="4" placeholder='["https://example.com/product.jpg"]'></textarea>
  </div>

  <button type="submit" class="btn btn--primary">Create Product</button>
</form>

<div id="create-product-result" class="form-result"></div>
<p class="muted"><a href="/admin/products">&larr; Back to list</a></p>

<script>
document.getElementById('create-product-form').addEventListener('submit', async function(e) {
  e.preventDefault();

  const result = document.getElementById('create-product-result');
  const payload = Object.fromEntries(new FormData(this).entries());
  result.textContent = 'Saving...';

  try {
    const res = await fetch('/api/admin/products', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const json = await res.json();

    if (!json.success) {
      result.textContent = json.error || 'Failed to create product.';
      return;
    }

    window.location.href = '/admin/products';
  } catch (err) {
    result.textContent = 'Network error.';
  }
});
</script>
