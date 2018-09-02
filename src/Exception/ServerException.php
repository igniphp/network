<?php declare(strict_types=1);

namespace Igni\Network\Exception;

use Igni\Exception\RuntimeException;

class ServerException extends RuntimeException implements NetworkException
{
    public static function forMethodCallOnIdleServer(string $method): self
    {
        return new self("Cannot call method {$method} - server hasn't started yet");
    }
}

