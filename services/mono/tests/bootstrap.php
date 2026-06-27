<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

if (!function_exists('assertTrue')) {
  function assertTrue(bool $condition, string $message): void
  {
    if (!$condition) {
      throw new Exception("Assertion failed: {$message}");
    }
  }
}

if (!function_exists('assertEquals')) {
  function assertEquals($expected, $actual, string $message): void
  {
    if ($expected !== $actual) {
      throw new Exception(
        "Assertion failed: {$message}\nExpected: {$expected}\nActual: {$actual}"
      );
    }
  }
}
