<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Class TrieNode
 *
 * Represents a single node in the routing trie.
 * Each node may contain:
 * - child nodes (static or dynamic segments)
 * - HTTP method handlers
 * - a parameter name for dynamic segments
 */
final class TrieNode
{
  /**
   * Child nodes mapped by segment key.
   *
   * Static segment: "users"
   * Dynamic segment: "*"
   *
   * @var array<string, TrieNode>
   */
  public array $children = [];

  /**
   * HTTP method handlers assigned to this node.
   *
   * @var array<string, callable|array>
   */
  public array $handlers = [];

  /**
   * Parameter name for dynamic segments (e.g. ":id").
   */
  public ?string $paramName = null;
}
