<?php
/**
 * Sprints List View - API driven
 */
?>

<h1>Sprints</h1>

<div style="margin-bottom: 16px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
  <input 
    type="text" 
    id="sprint-search" 
    placeholder="Search by name or token..." 
    style="padding: 8px; width: 280px; border: 1px solid #ccc; border-radius: 4px;"
    onkeyup="if (event.key === 'Enter') fetchSprints(1)"
  >
  <button onclick="fetchSprints(1)" class="btn btn--secondary">Search</button>
  <button onclick="clearSprintSearch()" class="btn btn--secondary">Clear</button>

  <a href="/admin/sprints/new" class="btn btn--primary" style="margin-left: auto;">+ New Sprint</a>
</div>

<div id="sprints-loading">Loading sprints...</div>

<div class="table-wrapper" id="sprints-table-wrapper" style="display:none;">
  <table class="sprint-table">
    <thead>
      <tr>
        <th>Token</th>
        <th>Name</th>
        <th>Status</th>
        <th>Visible</th>
        <th>Open</th>
        <th style="width: 180px;">Actions</th>
      </tr>
    </thead>
    <tbody id="sprints-tbody">
      <!-- Populated via JavaScript from /api/admin/sprints -->
    </tbody>
  </table>
</div>

<div id="sprints-pagination"></div>

<div id="sprints-empty" style="display:none;">
  <p>No sprints yet. Create the first one.</p>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  fetchSprints();
});

let currentPage = 1;
let currentSearch = '';

async function fetchSprints(page = 1) {
  const loading = document.getElementById('sprints-loading');
  const tableWrapper = document.getElementById('sprints-table-wrapper');
  const emptyMsg = document.getElementById('sprints-empty');
  const tbody = document.getElementById('sprints-tbody');
  const searchInput = document.getElementById('sprint-search');

  currentPage = page;
  currentSearch = searchInput ? searchInput.value.trim() : '';

  tbody.innerHTML = '';
  loading.style.display = 'block';
  tableWrapper.style.display = 'none';
  emptyMsg.style.display = 'none';

  let url = `/api/admin/sprints?page=${currentPage}&per_page=25`;
  if (currentSearch) {
    url += `&search=${encodeURIComponent(currentSearch)}`;
  }

  try {
    const res = await fetch(url);
    const json = await res.json();

    loading.style.display = 'none';

    if (!json.success || !json.data || json.data.length === 0) {
      emptyMsg.style.display = 'block';
      renderPagination(json.pagination || {});
      return;
    }

    tableWrapper.style.display = 'block';

    json.data.forEach(s => {
      const visible = Number(s.is_visible) === 1 ? 'Yes' : 'No';
      const open = Number(s.is_open) === 1 ? 'Yes' : 'No';

      const row = document.createElement('tr');
      row.innerHTML = `
        <td><code>${s.token || ''}</code></td>
        <td>${escapeHtml(s.name)}</td>
        <td>${escapeHtml(s.status)}</td>
        <td>${visible}</td>
        <td>${open}</td>
        <td>
          <a href="/admin/sprints/${s.id}" class="btn btn--secondary" style="padding:4px 8px;font-size:0.8rem;text-decoration:none;">View Detail</a>
          <button onclick="openSprintEditModal(${s.id}); event.stopImmediatePropagation();" class="btn btn--secondary" style="padding:4px 8px;font-size:0.8rem;">Edit</button>
          <button onclick="deleteSprint(${s.id}, this); event.stopImmediatePropagation();" class="btn" style="padding:4px 8px;font-size:0.8rem;background:#dc3545;color:white;">Delete</button>
        </td>
      `;
      tbody.appendChild(row);
    });

    renderPagination(json.pagination || {});

  } catch (err) {
    loading.innerHTML = '<p style="color:red;">Failed to load sprints.</p>';
  }
}

function renderPagination(pagination) {
  const container = document.getElementById('sprints-pagination');
  if (!container) return;

  container.innerHTML = '';

  if (!pagination || pagination.total_pages <= 1) return;

  const { current_page, total_pages } = pagination;

  let html = `<div style="margin-top:16px; display:flex; gap:8px; align-items:center; justify-content:center;">`;

  if (current_page > 1) {
    html += `<button onclick="fetchSprints(${current_page - 1})" class="btn btn--secondary">Previous</button>`;
  }

  html += `<span style="padding: 0 12px;">Page ${current_page} of ${total_pages}</span>`;

  if (current_page < total_pages) {
    html += `<button onclick="fetchSprints(${current_page + 1})" class="btn btn--secondary">Next</button>`;
  }

  html += `</div>`;
  container.innerHTML = html;
}

function clearSprintSearch() {
  const input = document.getElementById('sprint-search');
  if (input) input.value = '';
  fetchSprints(1);
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text ?? '';
  return div.innerHTML;
}

// === DELETE (Soft Delete) ===
async function deleteSprint(id, button) {
  if (!confirm('Are you sure you want to delete this sprint?')) return;

  button.disabled = true;
  button.textContent = '...';

  try {
    const res = await fetch(`/api/admin/sprints/${id}`, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' }
    });

    const json = await res.json();

    if (json.success) {
      fetchSprints();
    } else {
      alert(json.error || 'Gagal menghapus');
      button.disabled = false;
      button.textContent = 'Delete';
    }
  } catch (err) {
    alert('Network error');
    button.disabled = false;
    button.textContent = 'Delete';
  }
}
</script>

<?php
// Include reusable sprint edit modal
\App\Core\Renderer::view(dirname(__DIR__) . '/partials/sprint-edit-modal.php');
?>
