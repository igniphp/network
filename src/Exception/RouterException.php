<?php declare(strict_types=1);

namespace Igni\Network\Exception;

use Igni\Exception\RuntimeException;
use Igni\Network\Http\Response;
use Igni\Network\Http\Route;
use Igni\Network\Http\Router;
use Psr\Http\Message\ResponseInterface;

class RouterException extends RuntimeException implements HttpException
{
    private $httpStatus;

    public static function noRouteMatchesRequestedUri(string $uri, string $method): self
    {
        $exception = new self("No route matches requested uri: $method `$uri`.");
        $exception->httpStatus = 404;
        return $exception;
    }

    public static function methodNotAllowed(string $uri, array $allowedMethods): self
    {
        $allowedMethods = implode(', ', $allowedMethods);
        $exception = new self("This uri `$uri` allows only $allowedMethods http methods.");
        $exception->httpStatus = 405;
        return $exception;
    }

    public static function invalidRoute($given): self
    {

        $exception = new self(sprintf(
            '%s::addRoute() - passed value must be instance of %s, %s given.',
            Router::class,
            Route::class,
            get_class($given)
        ));
        $exception->httpStatus = 500;
        return $exception;
    }

    public function asResponse(): ResponseInterface
    {
        return Response::asText($this->getMessage(), $this->httpStatus);
    }
}
