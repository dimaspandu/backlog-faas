<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\ErrorHandler;
use App\Core\Http\Response;
use App\Core\Middleware\ErrorBoundary;
use Throwable;

/**
 * Class Router
 *
 * A lightweight HTTP router based on a Trie (prefix tree) data structure.
 *
 * Computer Science perspective:
 * - Uses a Trie to achieve efficient path matching (O(n) by path depth)
 * - Avoids regex-based routing for predictability and performance
 * - Separates routing, execution, and error handling responsibilities
 *
 * Architectural goals:
 * - Explicit control flow
 * - No hidden magic or dependency injection
 * - Suitable for streaming, micro-framework usage, and scalability
 */
class Router
{
  /**
   * Root node of the routing trie.
   *
   * Each node represents one path segment.
   * Children are keyed by literal segment or "*" for dynamic parameters.
   */
  private TrieNode $root;

  /**
   * Custom 404 (Not Found) handler.
   *
   * Stored as callable or [ControllerClass, method].
   * Executed when no route matches.
   */
  private $notFoundHandler = null;

  /**
   * Custom 500 (Internal Server Error) handler.
   *
   * Receives the thrown exception as parameter.
   * Allows user-defined error pages instead of hardcoded output.
   */
  private $errorHandler = null;

  public function __construct()
  {
    // Initialize the trie root node
    $this->root = new TrieNode();
  }

  /* -------------------------------------------------
   | Route registration (public API)
   |-------------------------------------------------
   | These methods expose HTTP verbs explicitly.
   | No method overloading or magic registration.
   */
  public function get(string $path, callable|array $handler): void
  {
    $this->addRoute('GET', $path, $handler);
  }

  public function post(string $path, callable|array $handler): void
  {
    $this->addRoute('POST', $path, $handler);
  }

  public function put(string $path, callable|array $handler): void
  {
    $this->addRoute('PUT', $path, $handler);
  }

  public function patch(string $path, callable|array $handler): void
  {
    $this->addRoute('PATCH', $path, $handler);
  }

  public function delete(string $path, callable|array $handler): void
  {
    $this->addRoute('DELETE', $path, $handler);
  }

  /* -------------------------------------------------
   | Error handler registration
   |-------------------------------------------------
   | Allows applications to define their own
   | 404 and 500 pages via controllers.
   */
  public function setNotFoundHandler(callable|array $handler): void
  {
    $this->notFoundHandler = $handler;
  }

  public function setErrorHandler(callable|array $handler): void
  {
    $this->errorHandler = $handler;
  }

  /* -------------------------------------------------
   | Internal route registration (Trie insertion)
   |-------------------------------------------------
   | Converts a path string into trie nodes.
   |
   | Example:
   |   /users/:id/profile
   |
   | Trie structure:
   |   users -> * -> profile
   |             ^ paramName = "id"
   */
  private function addRoute(
    string $httpMethod,
    string $path,
    callable|array $handler
  ): void {
    $segments = explode('/', trim($path, '/'));
    $currentNode = $this->root;

    foreach ($segments as $segment) {
      // Dynamic segments (":id") are normalized to "*"
      // This allows multiple dynamic routes to share the same node
      $key = str_starts_with($segment, ':') ? '*' : $segment;

      // Create node if it does not exist
      $currentNode->children[$key] ??= new TrieNode();

      // Store parameter name for later extraction
      if ($key === '*') {
        $currentNode->children[$key]->paramName = substr($segment, 1);
      }

      $currentNode = $currentNode->children[$key];
    }

    // Bind handler to HTTP method at the leaf node
    $currentNode->handlers[$httpMethod] = $handler;
  }

  /* -------------------------------------------------
   | Request dispatching
   |-------------------------------------------------
   | Core runtime path resolution logic.
   |
   | Steps:
   | 1. Normalize request path
   | 2. Traverse trie
   | 3. Extract dynamic parameters
   | 4. Execute handler within error boundary
   */
  public function dispatch(): void
  {
    $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $requestPath   = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

    /*
     |-------------------------------------------------
     | Base path normalization
     |-------------------------------------------------
     | Supports non-root deployments:
     | Example: /backlog/index.php
     */
    $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';

    if ($basePath !== '' && str_starts_with($requestPath, $basePath)) {
      $requestPath = substr($requestPath, strlen($basePath));
    }

    $requestPath = '/' . trim($requestPath, '/');

    $segments = explode('/', trim($requestPath, '/'));
    $params   = [];

    $currentNode = $this->root;

    /*
     |-------------------------------------------------
     | Trie traversal
     |-------------------------------------------------
     | Each segment moves one level down the trie.
     | Preference:
     | 1. Exact match
     | 2. Dynamic "*" match
     */
    foreach ($segments as $segment) {
      if (isset($currentNode->children[$segment])) {
        $currentNode = $currentNode->children[$segment];
        continue;
      }

      if (isset($currentNode->children['*'])) {
        $currentNode = $currentNode->children['*'];
        $params[$currentNode->paramName] = $segment;
        continue;
      }

      // No matching route found
      $this->handleNotFound();
      return;
    }

    // Route exists but HTTP method is not allowed
    if (!isset($currentNode->handlers[$requestMethod])) {
      $this->handleNotFound();
      return;
    }

    $handler  = $currentNode->handlers[$requestMethod];
    $boundary = new ErrorBoundary();

    /*
     |-------------------------------------------------
     | Execution with error isolation
     |-------------------------------------------------
     | ErrorBoundary ensures:
     | - Exceptions do not break global state
     | - Errors are centrally handled
     */
    try {
      $boundary->handle(fn() => $this->executeHandler($handler, $params));
    } catch (Throwable $e) {
      $this->handleError($e);
    }
  }

  /**
   * Execute the resolved handler.
   *
   * If the handler returns a Response object,
   * it is sent automatically.
   */
  private function executeHandler(callable|array $handler, array $params): void
  {
    $result = is_array($handler)
      ? (new $handler[0])->{$handler[1]}($params)
      : call_user_func($handler, $params);

    if ($result instanceof Response) {
      $result->send();
    }
  }

  /**
   * Handle 404 Not Found.
   *
   * Priority:
   * 1. Custom notFoundHandler
   * 2. Default ErrorHandler
   */
  private function handleNotFound(): void
  {
    http_response_code(404);

    if ($this->notFoundHandler) {
      $this->executeHandler($this->notFoundHandler, []);
      return;
    }

    ErrorHandler::renderNotFound();
  }

  /**
   * Handle uncaught exceptions (500 errors).
   *
   * Allows application-level error pages
   * while preserving a safe fallback.
   */
  private function handleError(Throwable $e): void
  {
    if ($this->errorHandler) {
      http_response_code(500);
      $this->executeHandler($this->errorHandler, ['exception' => $e]);
      return;
    }

    ErrorHandler::handle($e);
  }
}
