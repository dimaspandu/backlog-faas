<?php
/**
 * Sprint Detail View
 * $sprint array passed from controller
 */
$s = $sprint ?? [];
?>

<h1>Sprint Detail</h1>

<div style="margin-bottom: 20px;">
  <a href="/admin/sprints" class="btn btn--secondary">← Back to List</a>
  <button onclick="openSprintEditModal(<?= $s['id'] ?>)" class="btn btn--primary" style="margin-left: 8px;">Edit</button>
  <button onclick="deleteFromDetail(<?= $s['id'] ?>, this)" class="btn" style="margin-left: 8px; background:#dc3545; color:white;">Delete</button>
</div>

<div class="table-wrapper">
  <table class="sprint-table" style="max-width: 800px;">
    <tr>
      <th style="width: 180px;">Token</th>
      <td><code><?= htmlspecialchars($s['token'] ?? '') ?></code></td>
    </tr>
    <tr>
      <th>Name</th>
      <td><?= htmlspecialchars($s['name'] ?? '') ?></td>
    </tr>
    <tr>
      <th>Description</th>
      <td><?= nl2br(htmlspecialchars($s['description'] ?? '')) ?></td>
    </tr>
    <tr>
      <th>Status</th>
      <td><?= htmlspecialchars($s['status'] ?? '') ?></td>
    </tr>
    <tr>
      <th>Visible to Public</th>
      <td><?= !empty($s['is_visible']) ? 'Yes' : 'No' ?></td>
    </tr>
    <tr>
      <th>Open for Orders</th>
      <td><?= !empty($s['is_open']) ? 'Yes' : 'No' ?></td>
    </tr>
    <tr>
      <th>Start At</th>
      <td><?= $s['start_at'] ?? '-' ?></td>
    </tr>
    <tr>
      <th>End At</th>
      <td><?= $s['end_at'] ?? '-' ?></td>
    </tr>
    <tr>
      <th>Created At</th>
      <td><?= $s['created_at'] ?? '' ?></td>
    </tr>
    <tr>
      <th>Updated At</th>
      <td><?= $s['updated_at'] ?? '' ?></td>
    </tr>
  </table>
</div>

<script>
function editFromDetail(id) {
  // Reuse the edit modal from the list page if available, or redirect
  window.location.href = '/admin/sprints'; // For simplicity, redirect to list where modal works
  // You can enhance later to open modal with preloaded data
}

async function deleteFromDetail(id, button) {
  if (!confirm('Are you sure you want to delete this sprint?')) return;

  button.disabled = true;

  try {
    const res = await fetch(`/api/admin/sprints/${id}`, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' }
    });
    const json = await res.json();

    if (json.success) {
      window.location.href = '/admin/sprints';
    } else {
      alert(json.error || 'Failed to delete sprint');
      button.disabled = false;
    }
  } catch (e) {
    alert('Network error');
    button.disabled = false;
  }
}
</script>

<?php
// Include reusable edit modal
\App\Core\Renderer::view(dirname(__DIR__) . '/partials/sprint-edit-modal.php');
?>
