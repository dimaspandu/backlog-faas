<?php
/**
 * Reusable Admin Navigation
 *
 * Usage:
 *   Renderer::view(__DIR__ . '/admin/partials/nav.php');
 */
?>
<nav class="admin-nav">
  <div class="admin-nav__links">
    <a href="/admin" class="admin-nav__link<?= (($_SERVER['REQUEST_URI'] ?? '') === '/admin' ? ' admin-nav__link--active' : '') ?>">Dashboard</a>
    <a href="/admin/sprints" class="admin-nav__link<?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/sprints') ? ' admin-nav__link--active' : '' ?>">Sprints</a>
    <a href="/admin/products" class="admin-nav__link">Products</a>
  </div>
  <div class="admin-nav__actions">
    <button type="button" onclick="logoutAdmin()">Logout</button>
  </div>

<script>
function logoutAdmin() {
  fetch('/api/admin/logout', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' }
  })
  .then(res => res.json())
  .then(data => {
    if (data.success && data.redirect) {
      window.location.href = data.redirect;
    } else {
      window.location.href = '/admin/login';
    }
  })
  .catch(() => {
    window.location.href = '/admin/login';
  });
}
</script>
</nav>
