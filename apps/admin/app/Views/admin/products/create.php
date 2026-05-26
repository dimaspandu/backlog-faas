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
    <label class="form-group__label">Images</label>
    <div id="create-images-container">
      <div style="display:flex;gap:8px;margin-bottom:8px;">
        <input class="form-group__input image-url-input" name="image_urls[]" placeholder="https://example.com/image.jpg">
        <button type="button" onclick="addCreateImageInput()" class="btn">+</button>
      </div>
    </div>
    <small class="muted">Add one or more image URLs. They will be stored as a JSON array.</small>
  </div>

  <button type="submit" class="btn btn--primary">Create Product</button>
</form>

<div id="create-product-result" class="form-result"></div>
<p class="muted"><a href="/admin/products">&larr; Back to list</a></p>

<script>
function addCreateImageInput(value = '') {
  const container = document.getElementById('create-images-container');
  const wrapper = document.createElement('div');
  wrapper.style.display = 'flex';
  wrapper.style.gap = '8px';
  wrapper.style.marginBottom = '8px';
  wrapper.innerHTML = `
    <input class="form-group__input image-url-input" name="image_urls[]" value="${value}" placeholder="https://example.com/image.jpg">
    <button type="button" class="btn" onclick="this.parentNode.remove()">-</button>
  `;
  container.appendChild(wrapper);
}

document.addEventListener('DOMContentLoaded', () => {
  // ensure at least one input exists
  if (document.querySelectorAll('#create-images-container .image-url-input').length === 0) {
    addCreateImageInput();
  }

  document.getElementById('create-product-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const result = document.getElementById('create-product-result');
    result.textContent = 'Saving...';

    // collect form fields except images
    const formData = new FormData(this);
    const payload = {};
    for (const [k, v] of formData.entries()) {
      if (k === 'image_urls[]') continue;
      payload[k] = v;
    }

    // collect images from inputs
    const images = Array.from(document.querySelectorAll('.image-url-input'))
      .map(i => i.value.trim())
      .filter(v => v !== '');

    payload.images = images; // send as JSON array in request body

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
});
</script>
