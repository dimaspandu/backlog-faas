<?php
/**
 * Reusable Product Edit Modal
 */
?>

<div id="product-edit-modal" class="modal-overlay" style="display:none;">
  <div class="modal-content">
    <h2>Edit Product</h2>

    <form id="product-edit-form">
      <input type="hidden" name="id" id="product-edit-id">

      <div class="form-group">
        <label class="form-group__label">SKU *</label>
        <input class="form-group__input" name="sku" id="product-edit-sku" required>
      </div>

      <div class="form-group">
        <label class="form-group__label">Name *</label>
        <input class="form-group__input" name="name" id="product-edit-name" required>
      </div>

      <div class="form-group">
        <label class="form-group__label">Description</label>
        <textarea class="form-group__textarea" name="description" id="product-edit-description" rows="3"></textarea>
      </div>

      <div class="form-group">
        <label class="form-group__label">Images</label>
        <div id="product-edit-images-container">
          <div style="display:flex;gap:8px;margin-bottom:8px;">
            <input class="form-group__input image-url-input" name="image_urls[]" id="product-edit-image-0" placeholder="https://example.com/image.jpg">
            <button type="button" onclick="addProductEditImageInput()" class="btn">+</button>
          </div>
        </div>
        <small class="muted">Add one or more image URLs. They will be stored as a JSON array.</small>
      </div>

      <div class="modal-actions">
        <button type="submit" class="btn btn--primary">Save Changes</button>
        <button type="button" onclick="closeProductEditModal()" class="btn btn--secondary">Cancel</button>
      </div>
    </form>

    <div id="product-edit-result" class="form-result"></div>
  </div>
</div>

<style>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 24px;
  border-radius: 8px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-actions {
  display: flex;
  gap: 12px;
  margin-top: 16px;
}

.form-result {
  margin-top: 12px;
}

</style>

<script>
function addProductEditImageInput(value = '') {
  const container = document.getElementById('product-edit-images-container');
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
  if (document.querySelectorAll('#product-edit-images-container .image-url-input').length === 0) {
    addProductEditImageInput();
  }

  const form = document.getElementById('product-edit-form');
  form.addEventListener('submit', async function(e) {
    e.preventDefault();

    const result = document.getElementById('product-edit-result');
    result.textContent = 'Saving...';

    const formData = new FormData(this);
    const payload = {};
    for (const [k, v] of formData.entries()) {
      if (k === 'image_urls[]') continue;
      payload[k] = v;
    }

    const images = Array.from(document.querySelectorAll('.image-url-input'))
      .map(i => i.value.trim())
      .filter(v => v !== '');
    payload.images = images;

    try {
      const id = document.getElementById('product-edit-id').value;
      const res = await fetch(`/api/admin/products/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();

      if (!json.success) {
        result.textContent = json.error || 'Failed to save product.';
        return;
      }

      closeProductEditModal();
      window.location.reload();
    } catch (err) {
      result.textContent = 'Network error.';
    }
  });
});
</script>
