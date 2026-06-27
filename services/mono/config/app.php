<?php

declare(strict_types=1);

/**
 * Application configuration.
 *
 * This file defines global application behavior.
 */

return [
  /*
  |--------------------------------------------------------------------------
  | Application Environment
  |--------------------------------------------------------------------------
  | Supported values: development, production
  */
  'env' => env('APP_ENV', 'development'),

  /*
  |--------------------------------------------------------------------------
  | Debug Mode
  |--------------------------------------------------------------------------
  | When enabled, detailed errors may be logged internally.
  | Errors must never be displayed to end users in production.
  */
  'debug' => env('APP_DEBUG', false),
];
