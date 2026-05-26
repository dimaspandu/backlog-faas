<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Load Environment Variables (Minimal)
|--------------------------------------------------------------------------
| No external dependency.
| Only loads simple KEY=VALUE pairs.
*/
$envFile = __DIR__ . '/.env';

if (is_file($envFile)) {
  foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with(trim($line), '#')) {
      continue;
    }

    [$key, $value] = array_map('trim', explode('=', $line, 2));

    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
  }
}

/*
|--------------------------------------------------------------------------
| Environment Helper
|--------------------------------------------------------------------------
| Centralized env access with default values.
*/
function env(string $key, mixed $default = null): mixed
{
  $value = $_ENV[$key] ?? $default;

  if (is_string($value)) {
    $lower = strtolower($value);

    if ($lower === 'true') {
      return true;
    }

    if ($lower === 'false') {
      return false;
    }
  }

  return $value;
}

/*
|--------------------------------------------------------------------------
| Application Constants
|--------------------------------------------------------------------------
*/
define('APP_ENV', env('APP_ENV', 'production'));
define('APP_DEBUG', env('APP_DEBUG', 'false') === 'true');

define(
  'APP_BASE_PATH',
  rtrim(env('APP_BASE_PATH', '/'), '/')
);

define(
  'APP_BASE_URL',
  env('APP_BASE_URL', 'http://localhost' . APP_BASE_PATH)
);

/*
|--------------------------------------------------------------------------
| Autoloader (PSR-4 like, no Composer)
|--------------------------------------------------------------------------
*/
spl_autoload_register(function (string $class): void {
  $prefix = 'App\\';
  $baseDir = __DIR__ . '/app/';

  if (!str_starts_with($class, $prefix)) {
    return;
  }

  $relativeClass = substr($class, strlen($prefix));
  $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

  if (is_file($file)) {
    require $file;
  }
});

/*
|--------------------------------------------------------------------------
| Load Application Configuration
|--------------------------------------------------------------------------
*/
$appConfig = require __DIR__ . '/config/app.php';

/*
|--------------------------------------------------------------------------
| PHP Error Configuration
|--------------------------------------------------------------------------
| Errors are never displayed to users.
| All errors must be logged internally.
*/
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

if (
  ($appConfig['env'] ?? 'production') === 'development' &&
  ($appConfig['debug'] ?? false) === true
) {
  ini_set('display_errors', '1');
}

/*
|--------------------------------------------------------------------------
| Global Helper Functions
|--------------------------------------------------------------------------
*/

use App\Core\Widget;

/**
 * Render an HTML widget component.
 *
 * @param string $tag
 * @param array<string, mixed>|null $attrs
 * @param array|string|null $children
 */
function widget(
  string $tag,
  ?array $attrs = null,
  array|string|null $children = null
): string {
  return Widget::render($tag, $attrs, $children);
}
