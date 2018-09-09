<?php declare(strict_types=1);

namespace Igni\Network\Server\Listener;

use Igni\Network\Client;
use Igni\Network\Server\Listener;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface OnRequest extends Listener
{
    /**
     * @param Client $client
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function onRequest(Client $client, ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;
}
