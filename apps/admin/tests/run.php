<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

/*
|------------------------------------------------------------------
| Parse CLI arguments
|------------------------------------------------------------------
*/
$skip = [];

foreach ($argv as $arg) {
  if (str_starts_with($arg, '--skip')) {
    [, $value] = array_pad(explode('=', $arg, 2), 2, '');
    $skip = array_filter(
      array_map('strtolower', explode(',', $value))
    );
  }
}

/*
|------------------------------------------------------------------
| Run tests
|------------------------------------------------------------------
*/
$files = glob(__DIR__ . '/*Test.php');

foreach ($files as $file) {
  $basename = strtolower(basename($file));

  foreach ($skip as $pattern) {
    if ($pattern !== '' && str_contains($basename, $pattern)) {
      echo "⏭  Skipped {$basename}\n";
      continue 2;
    }
  }

  require $file;
}

echo "\n✅ All tests passed\n";
