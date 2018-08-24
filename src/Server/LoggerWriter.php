<?php declare(strict_types=1);

namespace Igni\Network\Server;

use Igni\Network\Server;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use DateTime;
use DateTimeZone;

class LoggerWriter implements OnClose, OnConnect, OnRequest, OnShutdown, OnStart
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onClose(Server $server, int $clientId): void
    {
        $this->logger->info(sprintf(
            '%s - client %d closed connection',
            new DateTime('now', new DateTimeZone('UTC')),
            $clientId
        ));
    }

    public function onConnect(Server $server, int $clientId): void
    {
        $this->logger->info(sprintf(
            '%s - client %d connected',
            new DateTime('now', new DateTimeZone('UTC')),
            $clientId
        ));
    }

    public function onRequest(ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->info(sprintf(
            '%s - client %d closed connection',
            new DateTime('now', new DateTimeZone('UTC')),
            $clientId
        ));
    }

    public function onShutdown(Server $server): void
    {
        // TODO: Implement onShutdown() method.
    }

    public function onStart(Server $server): void
    {
        // TODO: Implement onStart() method.
    }
}
