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
    return;
  }

  modal.style.display = 'flex';
  document.getElementById('sprint-edit-result').textContent = 'Loading...';

  try {
    const res = await fetch(`/api/admin/sprints/${id}`);
    const json = await res.json();

    if (!json.success || !json.data) {
      document.getElementById('sprint-edit-result').textContent = 'Failed to load sprint data.';
      return;
    }

    const sprint = json.data;
    document.getElementById('sprint-edit-id').value = sprint.id;
    document.getElementById('sprint-edit-name').value = sprint.name || '';
    document.getElementById('sprint-edit-description').value = sprint.description || '';
    document.getElementById('sprint-edit-start_at').value = sprint.start_at ? sprint.start_at.slice(0, 16) : '';
    document.getElementById('sprint-edit-end_at').value = sprint.end_at ? sprint.end_at.slice(0, 16) : '';
    document.getElementById('sprint-edit-status').value = sprint.status || 'DRAFT';
    document.getElementById('sprint-edit-is_visible').checked = Number(sprint.is_visible) === 1;
    document.getElementById('sprint-edit-is_open').checked = Number(sprint.is_open) === 1;
    document.getElementById('sprint-edit-result').textContent = '';
  } catch (err) {
    document.getElementById('sprint-edit-result').textContent = 'Error loading data.';
  }
};

window.closeSprintEditModal = function() {
  const modal = document.getElementById('sprint-edit-modal');
  if (modal) {
    modal.style.display = 'none';
  }
};

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('sprint-edit-form');
  if (!form) {
    return;
  }

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

    resultBox.textContent = 'Saving...';

    try {
      const res = await fetch(`/api/admin/sprints/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();

      if (!json.success) {
        resultBox.textContent = json.error || 'Update failed';
        return;
      }

      resultBox.textContent = json.message || 'Updated successfully!';
      setTimeout(() => {
        window.closeSprintEditModal();
        if (typeof fetchSprints === 'function') {
          fetchSprints();
        } else {
          window.location.reload();
        }
      }, 600);
    } catch (err) {
      resultBox.textContent = 'Network error';
    }
  });
});
