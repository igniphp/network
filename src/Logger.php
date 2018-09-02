<?php declare(strict_types=1);

namespace Igni\Network;

use DateTime;
use DateTimeZone;
use Igni\Network\Server\Listener\OnClose;
use Igni\Network\Server\Listener\OnConnect;
use Igni\Network\Server\Listener\OnShutdown;
use Igni\Network\Server\Listener\OnStart;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Logger implements OnClose, OnConnect, OnShutdown, OnStart, LoggerInterface
{
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
        $this->alert('Server shutdown.');
    }

    public function onStart(Server $server): void
    {
        $this->alert(
            'Server is listening {address}:{port}',
            ['port' => $server->getConfiguration()->getPort(), 'address' => $server->getConfiguration()->getAddress()]
        );
    }

    public function emergency($message, array $context = [])
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = [])
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = [])
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = [])
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = [])
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = [])
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = [])
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = [])
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function log($level, $message, array $context = [])
    {
        foreach ($context as $key => $value) {
            $search = sprintf('{%s}', $key);
            $message = str_replace($search, $value, $message);
        }

        printf('[%s] - %s%s', (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s'), $message, PHP_EOL);
    }
}
