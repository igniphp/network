<?php declare(strict_types=1);

namespace Igni\Network\Http;

/**
 * Proxy class for symfony's route.
 *
 * @package Igni\Network\Http
 */
class Route
{
    /** @var string */
    private $name;

    /** @var array */
    private $attributes = [];

    /** @var array */
    private $methods = [];

    /** @var string */
    private $path;

    /** @var mixed */
    private $controller;

    /**
     * Route constructor.
     *
     * @param string $path
     * @param string $name
     * @param array $methods
     */
    public function __construct(string $path, array $methods = ['GET'], string $name = '')
    {
        if (empty($name)) {
            $name = self::generateNameFromPath($path, $methods);
        }
        $this->name = $name;
        $this->methods = $methods;
        $this->path = $path;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function withController($controller): self
    {
        $instance = clone $this;
        $instance->controller = $controller;

        return $instance;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function withMethods(array $methods): self
    {
        $instance = clone $this;
        $instance->methods = $methods;

        return $instance;
    }

    /**
     * Returns route pattern.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Returns request methods.
     *
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Factories new instance of the current route with
     * attributes retrieved from client's request.
     *
     * @param array $attributes
     * @return Route
     */
    public function withAttributes(array $attributes): self
    {
        $instance = clone $this;
        $instance->attributes = $attributes;

        return $instance;
    }

    /**
     * Returns attributes extracted from the uri.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Factories new instance of the route
     * that will be matched against get request.
     *
     * @param string $path
     * @param string $name
     * @return Route
     */
    public static function get(string $path, string $name = ''): self
    {
        return new self($path, [Request::METHOD_GET], $name);
    }

    /**
     * Factories new instance of the route
     * that will be matched against post request.
     *
     * @param string $path
     * @param string $name
     * @return Route
     */
    public static function post(string $path, string $name = ''): self
    {
        return new self($path, [Request::METHOD_POST], $name);
    }

    /**
     * Factories new instance of the route
     * that will be matched against put request.
     *
     * @param string $path
     * @param string $name
     * @return Route
     */
    public static function put(string $path, string $name = ''): self
    {
        return new self($path, [Request::METHOD_PUT], $name);
    }

    /**
     * Factories new instance of the route
     * that will be matched against delete request.
     *
     * @param string $path
     * @param string $name
     * @return Route
     */
    public static function delete(string $path, string $name = ''): self
    {
        return new self($path, [Request::METHOD_DELETE], $name);
    }

    /**
     * Factories new instance of the route
     * that will be matched against patch request.
     *
     * @param string $path
     * @param string $name
     * @return Route
     */
    public static function patch(string $path, string $name = ''): self
    {
        return new self($path, [Request::METHOD_PATCH], $name);
    }

    /**
     * Factories new instance of the route
     * that will be matched against head request.
     *
     * @param string $path
     * @param string $name
     * @return Route
     */
    public static function head(string $path, string $name = ''): self
    {
        return new self($path, [Request::METHOD_HEAD, Request::METHOD_GET], $name);
    }

    /**
     * Factories new instance of the route
     * that will be matched against options request.
     *
     * @param string $path
     * @param string $name
     * @return Route
     */
    public static function options(string $path, string $name = ''): self
    {
        return new self($path, [Request::METHOD_OPTIONS], $name);
    }

    /**
     * Generates default name from given path expression,
     * GET /some/{resource} becomes get_some_resource
     *
     * @param string $path
     * @param array $methods
     * @return string
     */
    public static function generateNameFromPath(string $path, array $methods): string
    {
        $path = preg_replace('/<[^>]+>/', '', $path);
        $uri = str_replace(['{', '}', '?', '.', '/'], ['', '', '', '_', '_'], trim($path, '/'));

        return strtolower(array_shift($methods)) . '_' . $uri;
    }
}
