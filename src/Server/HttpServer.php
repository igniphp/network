<?php declare(strict_types=1);

namespace Igni\Network\Server;

use Igni\Network\Http\ServerRequest;
use Igni\Network\Server;
use Igni\Network\Server\Listener\OnRequest;
use Psr\Log\LoggerInterface;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\Http\Response as SwooleHttpResponse;
use Swoole\Http\Server as SwooleHttpServer;

class HttpServer extends Server implements HandlerFactory
{
    public function __construct(Configuration $settings = null, LoggerInterface $logger = null, HandlerFactory $handlerFactory = null)
    {
        parent::__construct($settings, $logger, $handlerFactory ?? $this);
    }

    public function createHandler(Configuration $configuration)
    {
        $flags = SWOOLE_TCP;
        if ($configuration->isSslEnabled()) {
            $flags |= SWOOLE_SSL;
        }
        $settings = $configuration->toArray();
        $handler = new SwooleHttpServer($settings['address'], $settings['port'], SWOOLE_PROCESS, $flags);
        $handler->set($settings);

        return $handler;
    }

    public function addListener(Listener $listener): void
    {
        $this->addListenerByType($listener, OnRequest::class);
        parent::addListener($listener);
    }

    protected function createListeners(): void
    {
        $this->createOnRequestListener();
        parent::createListeners();
    }

    protected function createOnRequestListener(): void
    {
        $this->handler->on('Request', function($handler, int $clientId) {

        });
    }

    private function normalizeRequest(
        SwooleHttpRequest $request,
        SwooleHttpResponse $response,
        OnRequest $listener
    ): void {
        $psrRequest = ServerRequest::fromSwoole($request);
        $psrResponse = $listener->onRequest($psrRequest);

        // Set headers
        foreach ($psrResponse->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $response->header($name, $value);
            }
        }

        // Response body.
        $body = $psrResponse->getBody()->getContents();

        // Status code
        $response->status($psrResponse->getStatusCode());

        // Protect server software header.
        $response->header('software-server', '');
        $response->header('server', '');

        // Support gzip/deflate encoding.
        if ($psrRequest->hasHeader('accept-encoding')) {
            $encoding = explode(',', strtolower(implode(',', $psrRequest->getHeader('accept-encoding'))));

            if (in_array('gzip', $encoding, true)) {
                $response->gzip(1);
            } elseif (in_array('deflate', $encoding, true)) {
                $response->header('content-encoding', 'deflate');
                $body = gzdeflate($body);
            }
        }

        $response->end($body);
    }
}
