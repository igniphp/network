<?php declare(strict_types=1);

namespace Igni\Network\Exception;

use Igni\Exception\RuntimeException;
use Psr\Http\Message\ResponseInterface;

class MiddlewareException extends RuntimeException
{
    public static function forEmptyMiddlewarePipeline(): self
    {
        return new self('Middleware pipeline is empty.');
    }

    public static function forInvalidMiddlewareResponse($response): self
    {
        $dumped = var_export($response, true);
        return new self(sprintf(
            "Middleware failed to produce valid response object, expected instance of `%s` got `%s`".
            ResponseInterface::class,
            $dumped
        ));
    }
}
