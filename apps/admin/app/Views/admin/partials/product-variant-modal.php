<?php
/**
 * Product Variant Create/Edit Modal
 */
?>

<div id="product-variant-modal" class="modal-overlay" style="display:none;">
  <div class="modal-content">
    <h2 id="product-variant-modal-title">Add Variant</h2>

    <form id="product-variant-form">
      <input type="hidden" id="product-variant-id">

      <div class="form-group">
        <label class="form-group__label">SKU *</label>
        <input class="form-group__input" name="sku" id="product-variant-sku" required>
      </div>

      <div class="form-group">
        <label class="form-group__label">Name *</label>
        <input class="form-group__input" name="name" id="product-variant-name" required placeholder="Bottle / Large">
      </div>

      <div class="form-group">
        <label class="form-group__label">Attributes</label>
        <div id="product-variant-attributes-container">
          <div style="display:flex;gap:8px;margin-bottom:8px;">
            <input class="form-group__input variant-attr-key" placeholder="key (e.g. size)">
            <input class="form-group__input variant-attr-value" placeholder="value (e.g. large)">
            <button type="button" onclick="addVariantAttributeInput()" class="btn">+</button>
          </div>
        </div>
        <small class="muted">Add key/value pairs for variant attributes. They will be stored as JSON.</small>
      </div>

      <div class="modal-actions">
        <button type="submit" class="btn btn--primary">Save Variant</button>
        <button type="button" onclick="closeProductVariantModal()" class="btn btn--secondary">Cancel</button>
      </div>
    </form>

    <div id="product-variant-result" class="form-result"></div>
  </div>
</div>

<script>
function addVariantAttributeInput(key = '', value = '') {
  const container = document.getElementById('product-variant-attributes-container');
  const wrapper = document.createElement('div');
  wrapper.style.display = 'flex';
  wrapper.style.gap = '8px';
  wrapper.style.marginBottom = '8px';
  wrapper.innerHTML = `
    <input class="form-group__input variant-attr-key" value="${key}" placeholder="key (e.g. size)">
    <input class="form-group__input variant-attr-value" value="${value}" placeholder="value (e.g. large)">
    <button type="button" class="btn" onclick="this.parentNode.remove()">-</button>
  `;
  container.appendChild(wrapper);
}

document.addEventListener('DOMContentLoaded', () => {
  // ensure at least one attribute input exists
  if (document.querySelectorAll('#product-variant-attributes-container .variant-attr-key').length === 0) {
    addVariantAttributeInput();
  }

  // hook into existing form submit to transform attributes into JSON
  const form = document.getElementById('product-variant-form');
  form.addEventListener('submit', function(e) {
    const attrs = Array.from(document.querySelectorAll('#product-variant-attributes-container'));
  });
});
</script>
