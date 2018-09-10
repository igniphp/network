<?php declare(strict_types=1);

namespace Igni\Network;

use Igni\Exception\RuntimeException;
use Igni\Network\Exception\ServerException;
use Igni\Network\Server\Client;
use Igni\Network\Server\Configuration;
use Igni\Network\Server\HandlerFactory;
use Igni\Network\Server\Listener;
use Igni\Network\Server\LogWriter;
use Igni\Network\Server\OnCloseListener;
use Igni\Network\Server\OnConnectListener;
use Igni\Network\Server\OnReceiveListener;
use Igni\Network\Server\OnShutdownListener;
use Igni\Network\Server\OnStartListener;
use Igni\Network\Server\ServerStats;
use Psr\Log\LoggerInterface;
use SplQueue;
use Swoole\Server as SwooleServer;

use function extension_loaded;

/**
 * Http server implementation based on swoole extension.
 *
 * @package Igni\Http
 */
class Server implements HandlerFactory
{
    private const SWOOLE_EXT_NAME = 'swoole';

    /**
     * @var SwooleServer|null
     */
    protected $handler;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var SplQueue[]
     */
    protected $listeners = [];

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var HandlerFactory
     */
    protected $handlerFactory;

    /**
     * @var Client[]
     */
    private $clients = [];

    /**
     * @var bool
     */
    private $started = false;

    public function __construct(
        Configuration $settings = null,
        LoggerInterface $logger = null,
        HandlerFactory $handlerFactory = null
    ) {
        if (!extension_loaded(self::SWOOLE_EXT_NAME)) {
            throw new RuntimeException('Swoole extenstion is missing, please install it and try again.');
        }

        $this->handlerFactory = $handlerFactory ?? $this;
        $this->configuration = $settings ?? new Configuration();
        $this->logger = new LogWriter($logger);
    }

    /**
     * Return server configuration.
     *
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getClient(int $id): Client
    {
        return $this->clients[$id];
    }

    /**
     * Adds listener that is attached to server once it is run.
     *
     * @param Listener $listener
     */
    public function addListener(Listener $listener): void
    {
        $this->addListenerByType($listener, OnStartListener::class);
        $this->addListenerByType($listener, OnCloseListener::class);
        $this->addListenerByType($listener, OnConnectListener::class);
        $this->addListenerByType($listener, OnShutdownListener::class);
        $this->addListenerByType($listener, OnReceiveListener::class);
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
        /** @var SplQueue $listenerCollection */
        foreach ($this->listeners as $listenerCollection) {
            foreach ($listenerCollection as $current) {
                if ($current === $listener) {
                    return true;
                }
            }
        }

        return false;
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

    public function createHandler(Configuration $configuration)
    {
        $flags = SWOOLE_TCP;
        if ($configuration->isSslEnabled()) {
            $flags |= SWOOLE_SSL;
        }
        $settings = $configuration->toArray();
        $handler = new SwooleServer($settings['address'], $settings['port'], SWOOLE_PROCESS, $flags);
        $handler->set($settings);

        return $handler;
    }

    public function start(): void
    {
        $this->addListener($this->logger);
        $this->handler = $this->handlerFactory->createHandler($this->configuration);
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

    private function createClient($handler, int $clientId): Client
    {
        return $this->clients[$clientId] = new Client($handler, $clientId);
    }

    private function destroyClient(int $clientId): void
    {
        unset($this->clients[$clientId]);
    }

    protected function createOnConnectListener(): void
    {
        $this->handler->on('Connect', function($handler, int $clientId) {
            $this->createClient($handler, $clientId);

            if (!isset($this->listeners[OnConnectListener::class])) {
                return;
            }

            $queue = clone $this->listeners[OnConnectListener::class];
            /** @var OnConnectListener $listener */
            while (!$queue->isEmpty() && $listener = $queue->pop()) {
                $listener->onConnect($this, $this->getClient($clientId));
            }
        });
    }

    protected function createOnCloseListener(): void
    {
        $this->handler->on('Close', function($handler, int $clientId) {
            if (isset($this->listeners[OnCloseListener::class])) {

                $queue = clone $this->listeners[OnCloseListener::class];
                /** @var OnCloseListener $listener */
                while (!$queue->isEmpty() && $listener = $queue->pop()) {
                    $listener->onClose($this, $this->getClient($clientId));
                }
            }

            $this->destroyClient($clientId);
        });
    }

    protected function createOnShutdownListener(): void
    {
        $this->handler->on('Shutdown', function() {
            if (!isset($this->listeners[OnShutdownListener::class])) {
                return;
            }

            $queue = clone $this->listeners[OnShutdownListener::class];

            /** @var OnShutdownListener $listener */
            while (!$queue->isEmpty() && $listener = $queue->pop()) {
                $listener->onShutdown($this);
            }
        });
    }

    protected function createOnStartListener(): void
    {
        $this->handler->on('Start', function() {
            if (!isset($this->listeners[OnStartListener::class])) {
                return;
            }

            $queue = clone $this->listeners[OnStartListener::class];
            /** @var OnStartListener $listener */
            while (!$queue->isEmpty() && $listener = $queue->pop()) {
                $listener->onStart($this);
            }
        });
    }

    protected function createOnReceiveListener(): void
    {
        $this->handler->on('Receive', function ($handler, int $clientId, int $fromId, string $data) {
            if (!isset($this->listeners[OnReceiveListener::class])) {
                return;
            }

            $queue = clone $this->listeners[OnReceiveListener::class];

            /** @var OnReceiveListener $listener */
            while (!$queue->isEmpty() && $listener = $queue->pop()) {
                $listener->onReceive($this, $this->getClient($clientId), $data);
            }
        });
    }
}
