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
        <label class="form-group__label">Images (JSON)</label>
        <textarea class="form-group__textarea" name="images" id="product-edit-images" rows="4"></textarea>
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
