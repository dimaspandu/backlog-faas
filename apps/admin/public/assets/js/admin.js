/* ========================================
   Backlog Admin - Client-side JavaScript
   ======================================== */

/*
 * This file is loaded on all admin pages.
 * Currently empty — add interactive features here when needed:
 *
 * Examples:
 * - Confirm before delete
 * - Auto-save drafts
 * - Dynamic form fields for sprint products
 * - Date range validation
 */

document.addEventListener('DOMContentLoaded', () => {
  // Future: add admin JS here
  // console.log('Admin JS initialized');
});

/* ========================================
   Reusable Sprint Edit Modal
   ======================================== */

window.openSprintEditModal = async function(id) {
  const modal = document.getElementById('sprint-edit-modal');
  if (!modal) {
    console.error('Sprint edit modal not found. Make sure the partial is included.');
    return;
  }

  // Show modal
  modal.style.display = 'flex';
  document.getElementById('sprint-edit-result').innerHTML = 'Loading...';

  try {
    const res = await fetch(`/api/admin/sprints/${id}`);
    const json = await res.json();

    if (!json.success || !json.data) {
      document.getElementById('sprint-edit-result').innerHTML = '<p style="color:red;">Failed to load sprint data.</p>';
      return;
    }

    const s = json.data;

    // Fill form
    document.getElementById('sprint-edit-id').value = s.id;
    document.getElementById('sprint-edit-name').value = s.name || '';
    document.getElementById('sprint-edit-description').value = s.description || '';
    document.getElementById('sprint-edit-start_at').value = s.start_at ? s.start_at.slice(0, 16) : '';
    document.getElementById('sprint-edit-end_at').value = s.end_at ? s.end_at.slice(0, 16) : '';
    document.getElementById('sprint-edit-status').value = s.status || 'DRAFT';
    document.getElementById('sprint-edit-is_visible').checked = Number(s.is_visible) === 1;
    document.getElementById('sprint-edit-is_open').checked = Number(s.is_open) === 1;

    document.getElementById('sprint-edit-result').innerHTML = '';
  } catch (err) {
    document.getElementById('sprint-edit-result').innerHTML = '<p style="color:red;">Error loading data.</p>';
  }
};

window.closeSprintEditModal = function() {
  const modal = document.getElementById('sprint-edit-modal');
  if (modal) modal.style.display = 'none';
};

// Handle form submission for the reusable modal
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('sprint-edit-form');
  if (!form) return;

  form.addEventListener('submit', async function(e) {
    e.preventDefault();

    const id = document.getElementById('sprint-edit-id').value;
    const resultBox = document.getElementById('sprint-edit-result');

    const payload = {
      name: this.name.value,
      description: this.description.value,
      start_at: this.start_at.value || null,
      end_at: this.end_at.value || null,
      status: this.status.value,
      is_visible: document.getElementById('sprint-edit-is_visible').checked ? 1 : 0,
      is_open: document.getElementById('sprint-edit-is_open').checked ? 1 : 0
    };

    resultBox.innerHTML = 'Saving...';

    try {
      const res = await fetch(`/api/admin/sprints/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const json = await res.json();

      if (json.success) {
        resultBox.innerHTML = `<p style="color:green;">${json.message || 'Updated successfully!'}</p>`;
        setTimeout(() => {
          window.closeSprintEditModal();
          // Refresh list if on list page
          if (typeof fetchSprints === 'function') {
            fetchSprints();
          } else {
            // On detail page, reload the page
            window.location.reload();
          }
        }, 600);
      } else {
        resultBox.innerHTML = `<p style="color:red;">${json.error || 'Update failed'}</p>`;
      }
    } catch (err) {
      resultBox.innerHTML = '<p style="color:red;">Network error</p>';
    }
  });
});

/* ========================================
   Reusable Product Edit Modal
   ======================================== */

window.openProductEditModal = async function(id) {
  const modal = document.getElementById('product-edit-modal');
  const result = document.getElementById('product-edit-result');
  if (!modal || !result) {
    return;
  }

  modal.style.display = 'flex';
  result.textContent = 'Loading...';

  try {
    const res = await fetch(`/api/admin/products/${id}`);
    const json = await res.json();
    if (!json.success || !json.data) {
      result.textContent = json.error || 'Failed to load product.';
      return;
    }

    const product = json.data;
    document.getElementById('product-edit-id').value = product.id;
    document.getElementById('product-edit-sku').value = product.sku || '';
    document.getElementById('product-edit-name').value = product.name || '';
    document.getElementById('product-edit-description').value = product.description || '';
    // populate images: support old textarea (product-edit-images) and new inputs (product-edit-images-container)
    if (document.getElementById('product-edit-images')) {
      document.getElementById('product-edit-images').value = product.images || '';
    } else if (document.getElementById('product-edit-images-container')) {
      const container = document.getElementById('product-edit-images-container');
      container.innerHTML = '';
      let imgs = [];
      try {
        imgs = product.images ? (Array.isArray(product.images) ? product.images : JSON.parse(product.images)) : [];
      } catch (e) {
        imgs = [];
      }
      // helper: append global add button
      const appendAddButton = () => {
        const ctrl = document.createElement('div');
        ctrl.style.display = 'flex';
        ctrl.style.gap = '8px';
        ctrl.style.marginTop = '6px';
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn';
        btn.textContent = '+';
        btn.onclick = () => addProductEditImageInput();
        ctrl.appendChild(btn);
        container.appendChild(ctrl);
      };

      if (imgs.length === 0) {
        if (typeof addProductEditImageInput === 'function') {
          addProductEditImageInput();
        } else {
          const wrapper = document.createElement('div');
          wrapper.style.display = 'flex';
          wrapper.style.gap = '8px';
          wrapper.style.marginBottom = '8px';
          wrapper.innerHTML = '<input class="form-group__input image-url-input" name="image_urls[]" placeholder="https://example.com/image.jpg"><button type="button" class="btn" onclick="this.parentNode.remove()">-</button>';
          container.appendChild(wrapper);
        }
        appendAddButton();
      } else {
        imgs.forEach(url => {
          if (typeof addProductEditImageInput === 'function') {
            addProductEditImageInput(url);
          } else {
            const wrapper = document.createElement('div');
            wrapper.style.display = 'flex';
            wrapper.style.gap = '8px';
            wrapper.style.marginBottom = '8px';
            wrapper.innerHTML = `<input class="form-group__input image-url-input" name="image_urls[]" value="${url}" placeholder="https://example.com/image.jpg"><button type="button" class="btn" onclick="this.parentNode.remove()">-</button>`;
            container.appendChild(wrapper);
          }
        });
        appendAddButton();
      }
    }
    result.textContent = '';
  } catch (err) {
    result.textContent = 'Error loading product.';
  }
};

window.closeProductEditModal = function() {
  const modal = document.getElementById('product-edit-modal');
  if (modal) {
    modal.style.display = 'none';
  }
};

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('product-edit-form');
  if (!form) {
    return;
  }

  form.addEventListener('submit', async function(e) {
    e.preventDefault();

    const id = document.getElementById('product-edit-id').value;
    const result = document.getElementById('product-edit-result');
    const payload = {
      sku: this.sku.value,
      name: this.name.value,
      description: this.description.value,
      images: this.images.value
    };

    result.textContent = 'Saving...';
    try {
      const res = await fetch(`/api/admin/products/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();

      if (!json.success) {
        result.textContent = json.error || 'Update failed.';
        return;
      }

      result.textContent = json.message || 'Product updated.';
      setTimeout(() => {
        window.closeProductEditModal();
        if (typeof fetchProducts === 'function') {
          fetchProducts();
        } else {
          window.location.reload();
        }
      }, 600);
    } catch (err) {
      result.textContent = 'Network error.';
    }
  });
});
