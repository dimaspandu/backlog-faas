<?php
/**
 * Reusable <head> component for all admin pages.
 *
 * Usage:
 *   Renderer::view(__DIR__ . '/admin/partials/head.php', ['title' => 'Sprints']);
 *
 * Always includes:
 * - charset
 * - viewport meta (critical for mobile/responsive)
 * - CSS
 * - JS
 */
$title = $title ?? 'Backlog Admin';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title) ?> | Backlog Admin</title>
<link rel="stylesheet" href="/assets/css/admin.css">
<script src="/assets/js/admin.js" defer></script>
