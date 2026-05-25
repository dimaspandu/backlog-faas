<?php
/**
 * Login View
 * Pure presentation layer. Receives $error from controller.
 */
?>

<form id="login-form" class="login-form">
  <h1 class="login-form__title">Backlog Admin</h1>
  <p>Sign in to manage sprints and products.</p>

  <div id="login-error" class="error" style="display:none;"></div>

  <div class="login-form__group">
    <label class="form-group__label">Username</label>
    <input class="login-form__input" type="text" name="username" required autofocus>
  </div>

  <div class="login-form__group">
    <label class="form-group__label">Password</label>
    <input class="login-form__input" type="password" name="password" required>
  </div>

  <button type="submit" class="btn btn--primary">Login</button>

  <div class="login-form__hint">
    First time? Insert a user manually:<br>
    <code>INSERT INTO admin_users (username, password_hash) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');</code><br>
    Password: <strong>password</strong>
  </div>
</form>

<script>
document.getElementById('login-form').addEventListener('submit', async function(e) {
  e.preventDefault();

  const errorBox = document.getElementById('login-error');
  errorBox.style.display = 'none';
  errorBox.textContent = '';

  const formData = new FormData(this);
  const payload = {
    username: formData.get('username'),
    password: formData.get('password')
  };

  try {
    const res = await fetch('/api/admin/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const data = await res.json();

    if (data.success) {
      window.location.href = data.redirect || '/admin';
    } else {
      errorBox.textContent = data.error || 'Login failed';
      errorBox.style.display = 'block';
    }
  } catch (err) {
    errorBox.textContent = 'Network error. Please try again.';
    errorBox.style.display = 'block';
  }
});
</script>
