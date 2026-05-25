<?php
/**
 * Create Sprint Form - API driven
 */
?>

<h1>Create New Sprint</h1>

<form id="create-sprint-form" class="sprint-form">

  <div class="form-group">
    <label class="form-group__label">Name *</label>
    <input class="form-group__input" name="name" required>
  </div>

  <div class="form-group">
    <label class="form-group__label">Description</label>
    <textarea class="form-group__textarea" name="description" rows="3"></textarea>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label class="form-group__label">Start At</label>
      <input class="form-group__input" type="datetime-local" name="start_at">
    </div>
    <div class="form-group">
      <label class="form-group__label">End At</label>
      <input class="form-group__input" type="datetime-local" name="end_at">
    </div>
  </div>

  <div class="form-group">
    <label class="form-group__label">Status</label>
    <select class="form-group__select" name="status">
      <option value="DRAFT">DRAFT</option>
      <option value="ACTIVE">ACTIVE</option>
      <option value="PAUSED">PAUSED</option>
      <option value="CLOSED">CLOSED</option>
    </select>
  </div>

  <div class="form-group form-group--inline">
    <input type="checkbox" name="is_visible" value="1" checked>
    <span>Visible to public</span>
  </div>

  <div class="form-group form-group--inline">
    <input type="checkbox" name="is_open" value="1">
    <span>Open for orders</span>
  </div>

  <button type="submit" class="btn btn--primary">Create Sprint</button>
</form>

<div id="create-result" style="margin-top:12px;"></div>

<p class="muted mt-2"><a href="/admin/sprints">← Back to list</a></p>

<script>
document.getElementById('create-sprint-form').addEventListener('submit', async function(e) {
  e.preventDefault();

  const form = this;
  const resultBox = document.getElementById('create-result');
  resultBox.innerHTML = '';

  const formData = new FormData(form);
  const payload = {};

  for (let [key, value] of formData.entries()) {
    if (key === 'is_visible' || key === 'is_open') {
      payload[key] = 1;
    } else {
      payload[key] = value;
    }
  }

  try {
    const res = await fetch('/api/admin/sprints', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const json = await res.json();

    if (json.success) {
      resultBox.innerHTML = `<p style="color:green;">${json.message || 'Sprint created!'}</p>`;
      setTimeout(() => {
        window.location.href = '/admin/sprints';
      }, 800);
    } else {
      resultBox.innerHTML = `<p style="color:red;">${json.error || 'Failed to create sprint.'}</p>`;
    }
  } catch (err) {
    resultBox.innerHTML = `<p style="color:red;">Network error. Please try again.</p>`;
  }
});
</script>
