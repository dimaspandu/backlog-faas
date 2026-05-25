<?php
/**
 * Dashboard View - Presentation only
 * Receives: $username
 */
?>

<h1>Backlog Admin</h1>
<p>Welcome, <strong><?= htmlspecialchars($username ?? 'admin') ?></strong>.</p>

<div class="dashboard-cards">
  <a href="/admin/sprints" class="dashboard-cards__card">
    <strong>📋 Sprints</strong><br>
    <small>Manage sprint periods, visibility and status</small>
  </a>

  <a href="/admin/products" class="dashboard-cards__card">
    <strong>📦 Products</strong><br>
    <small>Manage the product catalog</small>
  </a>

  <a href="/admin/sprint-products" class="dashboard-cards__card dashboard-cards__card--disabled">
    <strong>🔗 Sprint Products</strong><br>
    <small>Link products to sprints with pricing (coming soon)</small>
  </a>
</div>

<p class="muted mt-2">Internal tool for backlog-faas. Session-based auth.</p>
