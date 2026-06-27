<?php

declare(strict_types=1);

namespace App\Core\Http;

/**
 * Abstract HTTP Response base class.
 *
 * Acts as a minimal contract for all HTTP responses in the system.
 * Concrete responses (JSON, Stream, File, etc.) must implement send().
 *
 * This class is intentionally small and explicit:
 * - No global state
 * - No output buffering
 * - No implicit side effects
 */
abstract class Response
{
  /**
   * HTTP status code to be sent with the response.
   */
  protected int $status = 200;

  /**
   * Collection of HTTP headers to be sent.
   *
   * @var array<string, string>
   */
  protected array $headers = [];

  /**
   * Set the HTTP status code.
   *
   * Fluent API allows chaining:
   * return (new JsonResponse($data))->status(201);
   */
  public function status(int $code): static
  {
    $this->status = $code;
    return $this;
  }

  /**
   * Add or override an HTTP header.
   *
   * Headers are stored internally and only sent when sendHeaders() is called.
   */
  public function header(string $key, string $value): static
  {
    $this->headers[$key] = $value;
    return $this;
  }

  /**
   * Send HTTP status code and headers to the client.
   *
   * This method is protected to ensure that headers are sent
   * in a controlled manner by the Response itself.
   *
   * If headers were already sent (e.g. due to early output),
   * this method becomes a no-op to avoid PHP warnings.
   */
  protected function sendHeaders(): void
  {
    if (headers_sent()) {
      return;
    }

    http_response_code($this->status);

    foreach ($this->headers as $key => $value) {
      header("{$key}: {$value}", true);
    }
  }

  /**
   * Send the response body.
   *
   * Concrete implementations are responsible for:
   * - Sending headers (via sendHeaders())
   * - Emitting the response body
   */
  abstract public function send(): void;
}
