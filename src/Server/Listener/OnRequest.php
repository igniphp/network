<?php declare(strict_types=1);

namespace Igni\Network\Server\Listener;

use Igni\Network\Server\Listener;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface OnRequest extends Listener
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface|null
     */
    public function onRequest(ServerRequestInterface $request): ?ResponseInterface;
}
