<?php declare(strict_types=1);

namespace Igni\Network\Exception;

use Psr\Http\Message\ResponseInterface;

interface HttpException extends NetworkException
{
    public function asResponse(): ResponseInterface;
}
