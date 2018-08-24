<?php declare(strict_types=1);

namespace Igni\Network\Exception;

use Igni\Exception\InvalidArgumentException;

class ConfigurationException extends InvalidArgumentException implements NetworkException
{
    public static function forInvalidDirectory(string $dir): self
    {
        return new self("Directory {$dir} does not exist.");
    }

    public static function forInvalidSslCertFile(string $certFile): self
    {
        return new self("Ssl cert file {$certFile} does not exist or could not be read.");
    }

    public static function forInvalidSslKeyFile(string $keyFile): self
    {
        return new self("Ssl key file {$keyFile} does not exist or could not be read.");
    }
}

