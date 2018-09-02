<?php declare(strict_types=1);

namespace Igni\Network;

use Igni\Exception\RuntimeException;
use Igni\Network\Client\ClientManager;
use Igni\Network\Exception\ServerException;
use Igni\Network\Server\Configuration;
use Igni\Network\Server\Listener;
use Igni\Network\Server\Listener\OnClose;
use Igni\Network\Server\Listener\OnConnect;
use Igni\Network\Server\Listener\OnReceive;
use Igni\Network\Server\Listener\OnShutdown;
use Igni\Network\Server\Listener\OnStart;
use Igni\Network\Server\ServerStats;
use Psr\Log\LoggerInterface;
use SplQueue;
use Swoole\Server as SwooleServer;

/**
 * Http server implementation based on swoole extension.
 *
 * @package Igni\Http
 */
class Server
{
    private const SWOOLE_EXT_NAME = 'swoole';

    /**
     * @var SwooleServer|null
     */
    protected $handler;

    /**
     * @var Configuration
     */
    protected $settings;

    /**
     * @var SplQueue[]
     */
    protected $listeners = [];

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ClientManager
     */
    private $clientManager;

    /**
     * @var bool
     */
    private $started = false;

    public function __construct(Configuration $settings = null)
    {
        if (!extension_loaded(self::SWOOLE_EXT_NAME)) {
            throw new RuntimeException('Swoole extenstion is missing, please install it and try again.');
        }

        $this->settings = $settings ?? new Configuration();
        $this->logger = $this->settings->getLogger();
        $this->clientManager = new ClientManager();
    }

    /**
     * Return server configuration.
     *
     * @return Configuration
     */
    public function getSettings(): Configuration
    {
        return $this->settings;
    }

    public function getClientManager(): ClientManager
    {
        return $this->clientManager;
    }

    /**
     * Adds listener that is attached to server once it is run.
     *
     * @param Listener $listener
     */
    public function addListener(Listener $listener): void
    {
        $this->addListenerByType($listener, OnStart::class);
        $this->addListenerByType($listener, OnClose::class);
        $this->addListenerByType($listener, OnConnect::class);
        $this->addListenerByType($listener, OnShutdown::class);
        $this->addListenerByType($listener, OnReceive::class);
    }

    protected function addListenerByType(Listener $listener, string $type): void
    {
        if ($listener instanceof $type) {
            if (!isset($this->listeners[$type])) {
                $this->listeners[$type] = new SplQueue();
            }
            $this->listeners[$type]->push($listener);
        }
    }

    /**
     * Checks if listener exists.
     *
     * @param Listener $listener
     * @return bool
     */
    public function hasListener(Listener $listener): bool
    {
        return in_array($listener, $this->listeners);
    }

    /**
     * Returns information about server.
     *
     * @return ServerStats
     */
    public function getServerStats(): ServerStats
    {
        if (!$this->started) {
            throw ServerException::forMethodCallOnIdleServer(__METHOD__);
        }
        return new ServerStats($this->handler->stats());
    }

    /**
     * @return SwooleServer
     */
    protected function createHandler()
    {
        $flags = SWOOLE_TCP;
        if ($this->settings->isSslEnabled()) {
            $flags |= SWOOLE_SSL;
        }
        $settings = $this->settings->toArray();
        $handler = new SwooleServer($settings['address'], $settings['port'], SWOOLE_PROCESS, $flags);
        $handler->set($settings);

        return $handler;
    }

    public function start(): void
    {
        $this->handler = $this->createHandler();
        $this->createListeners();
        $this->handler->start();
        $this->started = true;
    }

    public function stop(): void
    {
        if ($this->handler !== null) {
            $this->handler->shutdown();
            $this->handler = null;
        }
        $this->started = false;
    }

    protected function createListeners(): void
    {
        $this->createOnConnectListener();
        $this->createOnCloseListener();
        $this->createOnShutdownListener();
        $this->createOnStartListener();
        $this->createOnReceiveListener();
    }

    protected function createOnConnectListener(): void
    {
        $this->handler->on('Connect', function($handler, int $clientId) {
            $this->clientManager->createClient($handler, $clientId);

            if (!isset($this->listeners[OnClose::class])) {
                return;
            }

            $queue = clone $this->listeners[OnConnect::class];
            /** @var OnConnect $listener */
            while (!$queue->isEmpty() && $listener = $queue->pop()) {
                $listener->onConnect($this, $this->clientManager->getClient($clientId));
            }
        });
    }

    protected function createOnCloseListener(): void
    {
        $this->handler->on('Close', function($handler, int $clientId) {
            if (isset($this->listeners[OnClose::class])) {

                $queue = clone $this->listeners[OnClose::class];
                /** @var OnClose $listener */
                while (!$queue->isEmpty() && $listener = $queue->pop()) {
                    $listener->onClose($this, $this->clientManager->getClient($clientId));
                }
            }

            $this->clientManager->removeClient($clientId);
        });
    }

    protected function createOnShutdownListener(): void
    {
        $this->handler->on('Shutdown', function() {
            if (!isset($this->listeners[OnShutdown::class])) {
                return;
            }

            $queue = clone $this->listeners[OnShutdown::class];

            /** @var OnShutdown $listener */
            while (!$queue->isEmpty() && $listener = $queue->pop()) {
                $listener->onShutdown($this);
            }
        });
    }

    protected function createOnStartListener(): void
    {
        $this->handler->on('Start', function() {
            if (!isset($this->listeners[OnStart::class])) {
                return;
            }

            $queue = clone $this->listeners[OnStart::class];
            /** @var OnStart $listener */
            while (!$queue->isEmpty() && $listener = $queue->pop()) {
                $listener->onStart($this);
            }
        });
    }

    protected function createOnReceiveListener(): void
    {
        $this->handler->on('Receive', function ($handler, int $clientId, int $fromId, string $data) {
            if (!isset($this->listeners[OnReceive::class])) {
                return;
            }

            $queue = clone $this->listeners[OnReceive::class];

            /** @var OnReceive $listener */
            while (!$queue->isEmpty() && $listener = $queue->pop()) {
                $listener->onReceive($this, $this->clientManager->getClient($clientId), $data);
            }
        });
    }
}
