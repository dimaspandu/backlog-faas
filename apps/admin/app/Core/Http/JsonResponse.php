<?php

declare(strict_types=1);

namespace App\Core\Http;

/**
 * JSON HTTP response.
 *
 * Used primarily for API endpoints.
 * Automatically sets the Content-Type header and serializes data to JSON.
 */
final class JsonResponse extends Response
{
  /**
   * @param mixed $data Any data structure that can be JSON-encoded
   */
  public function __construct(private mixed $data) {}

  /**
   * Send the JSON response to the client.
   *
   * Encoding errors are not swallowed and will bubble up,
   * allowing the ErrorBoundary to handle them properly.
   */
  public function send(): void
  {
    $this->header('Content-Type', 'application/json');
    $this->sendHeaders();

    echo json_encode(
      $this->data,
      JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
    );
  }
}
