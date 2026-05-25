<?php
/**
 * Reusable Sprint Edit Modal
 * Can be included in any page that needs to edit sprints.
 *
 * Requires:
 * - The main page must include this partial
 * - JavaScript functions: openSprintEditModal(id) and the modal must be initialized
 */
?>

<!-- Reusable Sprint Edit Modal -->
<div id="sprint-edit-modal" class="modal-overlay" style="display:none;">
  <div class="modal-content">
    <h2>Edit Sprint</h2>
    
    <form id="sprint-edit-form">
      <input type="hidden" name="id" id="sprint-edit-id">

      <div class="form-group">
        <label class="form-group__label">Name *</label>
        <input class="form-group__input" name="name" id="sprint-edit-name" required>
      </div>

      <div class="form-group">
        <label class="form-group__label">Description</label>
        <textarea class="form-group__textarea" name="description" id="sprint-edit-description" rows="3"></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-group__label">Start At</label>
          <input class="form-group__input" type="datetime-local" name="start_at" id="sprint-edit-start_at">
        </div>
        <div class="form-group">
          <label class="form-group__label">End At</label>
          <input class="form-group__input" type="datetime-local" name="end_at" id="sprint-edit-end_at">
        </div>
      </div>

      <div class="form-group">
        <label class="form-group__label">Status</label>
        <select class="form-group__select" name="status" id="sprint-edit-status">
          <option value="DRAFT">DRAFT</option>
          <option value="ACTIVE">ACTIVE</option>
          <option value="PAUSED">PAUSED</option>
          <option value="CLOSED">CLOSED</option>
        </select>
      </div>

      <div class="form-group form-group--inline">
        <input type="checkbox" name="is_visible" id="sprint-edit-is_visible" value="1">
        <span>Visible to public</span>
      </div>

      <div class="form-group form-group--inline">
        <input type="checkbox" name="is_open" id="sprint-edit-is_open" value="1">
        <span>Open for orders</span>
      </div>

      <div class="modal-actions">
        <button type="submit" class="btn btn--primary">Save Changes</button>
        <button type="button" onclick="closeSprintEditModal()" class="btn btn--secondary">Cancel</button>
      </div>
    </form>

    <div id="sprint-edit-result" class="form-result"></div>
  </div>
</div>

<style>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
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
  font-size: 0.9rem;
}
</style>
