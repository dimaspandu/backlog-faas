<div id="sprint-product-modal" class="modal-overlay" style="display:none;">
  <div class="modal-content">
    <h2 id="sprint-product-modal-title">Add Sprint Product</h2>

    <form id="sprint-product-form">
      <input type="hidden" id="sprint-product-id">

      <div class="form-group">
        <label class="form-group__label">Sprint *</label>
        <select class="form-group__select" name="sprint_id" id="sprint-product-sprint-id" required></select>
      </div>

      <div class="form-group">
        <label class="form-group__label">Product *</label>
        <select class="form-group__select" name="product_id" id="sprint-product-product-id" required onchange="loadSprintProductVariants()"></select>
      </div>

      <div class="form-group">
        <label class="form-group__label">Variant</label>
        <select class="form-group__select" name="product_variant_id" id="sprint-product-variant-id">
          <option value="">No variant</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-group__label">SKU Override</label>
        <input class="form-group__input" name="sku" id="sprint-product-sku" placeholder="Leave blank to use product/variant SKU">
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-group__label">Price (cents) *</label>
          <input class="form-group__input" type="number" min="0" name="price_cents" id="sprint-product-price" required value="0">
        </div>
        <div class="form-group">
          <label class="form-group__label">List Price (cents)</label>
          <input class="form-group__input" type="number" min="0" name="list_price_cents" id="sprint-product-list-price" value="0">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-group__label">Discount (cents)</label>
          <input class="form-group__input" type="number" min="0" name="discount_cents" id="sprint-product-discount" value="0">
        </div>
        <div class="form-group">
          <label class="form-group__label">Stock</label>
          <input class="form-group__input" type="number" min="0" name="stock" id="sprint-product-stock" value="0">
        </div>
      </div>

      <div class="form-group">
        <label class="form-group__label">Status</label>
        <select class="form-group__select" name="status" id="sprint-product-status">
          <option value="ACTIVE">ACTIVE</option>
          <option value="INACTIVE">INACTIVE</option>
        </select>
      </div>

      <div class="modal-actions">
        <button type="submit" class="btn btn--primary">Save Offering</button>
        <button type="button" class="btn btn--secondary" onclick="closeSprintProductModal()">Cancel</button>
      </div>
    </form>
    <div id="sprint-product-result" class="form-result"></div>
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
  max-width: 650px;
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
async function loadSprintProductOptions() {
  const sprintSelect = document.getElementById('sprint-product-sprint-id');
  const productSelect = document.getElementById('sprint-product-product-id');
  const [sprintResponse, productResponse] = await Promise.all([
    fetch('/api/admin/sprints?per_page=100'),
    fetch('/api/admin/products?per_page=100')
  ]);
  const [sprints, products] = await Promise.all([sprintResponse.json(), productResponse.json()]);

  sprintSelect.innerHTML = '<option value="">Select sprint</option>';
  productSelect.innerHTML = '<option value="">Select product</option>';
  if (sprints.success) {
    sprints.data.forEach(item => sprintSelect.add(new Option(item.name, item.id)));
  }
  if (products.success) {
    products.data.forEach(item => productSelect.add(new Option(item.name, item.id)));
  }
}

async function loadSprintProductVariants(selectedId = '') {
  const productId = document.getElementById('sprint-product-product-id').value;
  const select = document.getElementById('sprint-product-variant-id');
  select.innerHTML = '<option value="">No variant</option>';
  if (!productId) return;

  const res = await fetch(`/api/admin/products/${productId}/variants`);
  const json = await res.json();
  if (!json.success) return;
  json.data.forEach(item => select.add(new Option(item.name, item.id)));
  select.value = selectedId || '';
}

async function openSprintProductModal(id = null) {
  const modal = document.getElementById('sprint-product-modal');
  const form = document.getElementById('sprint-product-form');
  const result = document.getElementById('sprint-product-result');
  form.reset();
  document.getElementById('sprint-product-id').value = '';
  document.getElementById('sprint-product-modal-title').textContent = id ? 'Edit Sprint Product' : 'Add Sprint Product';
  result.textContent = 'Loading options...';
  modal.style.display = 'flex';

  try {
    await loadSprintProductOptions();
    if (!id) {
      result.textContent = '';
      return;
    }

    const res = await fetch(`/api/admin/sprint-products/${id}`);
    const json = await res.json();
    if (!json.success || !json.data) {
      result.textContent = json.error || 'Failed to load sprint product.';
      return;
    }

    const item = json.data;
    document.getElementById('sprint-product-id').value = item.id;
    document.getElementById('sprint-product-sprint-id').value = item.sprint_id;
    document.getElementById('sprint-product-product-id').value = item.product_id;
    await loadSprintProductVariants(item.product_variant_id || '');
    document.getElementById('sprint-product-sku').value = item.sku || '';
    document.getElementById('sprint-product-price').value = item.price_cents || 0;
    document.getElementById('sprint-product-list-price').value = item.list_price_cents || 0;
    document.getElementById('sprint-product-discount').value = item.discount_cents || 0;
    document.getElementById('sprint-product-stock').value = item.stock || 0;
    document.getElementById('sprint-product-status').value = item.status || 'ACTIVE';
    result.textContent = '';
  } catch (err) {
    result.textContent = 'Failed to load form options.';
  }
}

function closeSprintProductModal() {
  document.getElementById('sprint-product-modal').style.display = 'none';
}

document.getElementById('sprint-product-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  const id = document.getElementById('sprint-product-id').value;
  const result = document.getElementById('sprint-product-result');
  const payload = Object.fromEntries(new FormData(this).entries());
  const url = id ? `/api/admin/sprint-products/${id}` : '/api/admin/sprint-products';
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
      result.textContent = json.error || 'Failed to save sprint product.';
      return;
    }

    closeSprintProductModal();
    refreshSprintProductPage();
  } catch (err) {
    result.textContent = 'Network error.';
  }
});
</script>
