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
        <label class="form-group__label">SKU</label>
        <input class="form-group__input" name="sku" id="product-variant-sku">
      </div>

      <div class="form-group">
        <label class="form-group__label">Name *</label>
        <input class="form-group__input" name="name" id="product-variant-name" required placeholder="Bottle / Large">
      </div>

      <div class="form-group">
        <label class="form-group__label">Attributes (JSON)</label>
        <textarea class="form-group__textarea" name="attributes" id="product-variant-attributes" rows="7" placeholder='[{"name":"container","value":"bottle"},{"name":"size","value":"large"}]'></textarea>
      </div>

      <div class="modal-actions">
        <button type="submit" class="btn btn--primary">Save Variant</button>
        <button type="button" onclick="closeProductVariantModal()" class="btn btn--secondary">Cancel</button>
      </div>
    </form>

    <div id="product-variant-result" class="form-result"></div>
  </div>
</div>
