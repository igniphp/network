<?php declare(strict_types=1);

namespace Igni\Http;

/**
 * Responsible for aggregating routes and forwarding request between framework and application layer.
 *
 * @package Igni\Network\Http
 */
interface Router
{
    public function add(Route $route): void;
    public function find(string $method, string $path): Route;
}
