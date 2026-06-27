<?php

declare(strict_types=1);

namespace App\Core\Http;

/**
 * File download / file serving HTTP response.
 *
 * Safely sends a file to the client if it exists.
 * Falls back to a 404 response if the file is missing.
 */
final class FileResponse extends Response
{
  /**
   * @param string $path Absolute or relative path to the file
   */
  public function __construct(private string $path) {}

  /**
   * Send the file contents to the client.
   *
   * This method does not attempt to guess caching behavior
   * or content disposition. Those concerns are left explicit.
   */
  public function send(): void
  {
    if (!is_file($this->path)) {
      $this->status(404);
      $this->sendHeaders();
      return;
    }

    $this->header(
      'Content-Type',
      mime_content_type($this->path) ?: 'application/octet-stream'
    );

    $this->sendHeaders();
    readfile($this->path);
  }
}
