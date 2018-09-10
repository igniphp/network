<?php declare(strict_types=1);

namespace Igni\Network\Server;

use Igni\Network\Server;
use Psr\Log\LoggerInterface;

class LogWriter implements OnCloseListener, OnConnectListener, OnShutdownListener, OnStartListener, LoggerInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new Logger();
    }

    public function onClose(Server $server, Client $client): void
    {
        $this->info(
            'Client {client} closed connection',
            ['client' => $client]
        );
    }

    public function onConnect(Server $server, Client $client): void
    {
        $this->info(
            'Client {client} connected',
            ['client' => $client]
        );
    }

    public function onShutdown(Server $server): void
    {
        $this->alert('Server shutdown');
    }

    public function onStart(Server $server): void
    {
        $this->info(
            'Server is listening {address}:{port}',
            ['port' => $server->getConfiguration()->getPort(), 'address' => $server->getConfiguration()->getAddress()]
        );
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function emergency($message, array $context = [])
    {
        $this->logger->emergency($message, $context);
    }

    public function alert($message, array $context = [])
    {
        $this->logger->alert($message, $context);
    }

    public function critical($message, array $context = [])
    {
        $this->logger->critical($message, $context);
    }

    public function error($message, array $context = [])
    {
        $this->logger->error($message, $context);
    }

    public function warning($message, array $context = [])
    {
        $this->logger->warning($message, $context);
    }

    public function notice($message, array $context = [])
    {
        $this->logger->notice($message, $context);
    }

    public function info($message, array $context = [])
    {
        $this->logger->info($message, $context);
    }

    public function debug($message, array $context = [])
    {
        $this->logger->debug($message, $context);
    }

    public function log($level, $message, array $context = [])
    {
        $this->logger->log($level, $message, $context);
    }
}
