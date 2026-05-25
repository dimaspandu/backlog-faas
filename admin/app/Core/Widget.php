<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Class Widget
 *
 * Simple HTML component renderer.
 */
class Widget
{
  /**
   * Render an HTML element.
   *
   * @param string $tag
   * @param array<string, mixed>|null $attributes
   * @param mixed $children
   */
  public static function render(
    string $tag,
    ?array $attributes = null,
    mixed $children = null
  ): string {
    // Detect self-closing tag (e.g. br/, hr/)
    $isSelfClosing = str_ends_with($tag, '/');
    $tagName = $isSelfClosing ? rtrim($tag, '/') : $tag;

    $attrString = self::buildAttributes($attributes);

    if ($isSelfClosing) {
      return "<{$tagName}{$attrString} />";
    }

    $content = self::buildChildren($children);

    return "<{$tagName}{$attrString}>{$content}</{$tagName}>";
  }

  /**
   * Build HTML attributes string.
   *
   * @param array<string, mixed>|null $attributes
   */
  private static function buildAttributes(?array $attributes): string
  {
    if (empty($attributes)) {
      return '';
    }

    $parts = [];

    foreach ($attributes as $key => $value) {
      $escapedKey = htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8');

      if ($value === true) {
        $parts[] = $escapedKey;
        continue;
      }

      if ($value === false || $value === null) {
        continue;
      }

      $escapedValue = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
      $parts[] = "{$escapedKey}=\"{$escapedValue}\"";
    }

    return ' ' . implode(' ', $parts);
  }

  /**
   * Build HTML children content.
   *
   * Rules:
   * - string → escaped
   * - array → recursive
   * - widget output (string with "<") → trusted
   *
   * @param mixed $children
   */
  private static function buildChildren(mixed $children): string
  {
    if ($children === null) {
      return '';
    }

    // Plain string
    if (is_string($children)) {
      // If contains HTML tags, assume it's widget output (trusted)
      if (str_contains($children, '<')) {
        return $children;
      }

      return htmlspecialchars($children, ENT_QUOTES, 'UTF-8');
    }

    // Array of children
    if (is_array($children)) {
      $output = '';

      foreach ($children as $child) {
        $output .= self::buildChildren($child);
      }

      return $output;
    }

    // Fallback (should not happen)
    return '';
  }
}
