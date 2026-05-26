<?php

declare(strict_types=1);

namespace App\Core\Http;

/**
 * Streaming HTTP response.
 *
 * Used when output should be generated progressively,
 * such as large files, live data, or chunked rendering.
 */
final class StreamResponse extends Response
{
  /**
   * Stream callback responsible for producing output.
   *
   * @var callable
   */
  private $stream;

  public function __construct(callable $stream)
  {
    $this->stream = $stream;
  }

  public function send(): void
  {
    $this->sendHeaders();
    ($this->stream)();
  }
}

