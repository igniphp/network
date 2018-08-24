<?php declare(strict_types=1);

namespace Igni\Network;

use Igni\Exception\RuntimeException;
use Igni\Network\Server\ClientStats;
use Igni\Network\Server\OnConnect;
use Igni\Network\Server\OnShutdown;
use Igni\Network\Server\OnStart;
use Igni\Network\Server\ServerStats;
use Igni\Network\Server\Configuration;
use Igni\Network\Server\Listener;
use Igni\Network\Server\OnClose;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Swoole\Server as SwooleServer;

/**
 * Http server implementation based on swoole extension.
 *
 * @package Igni\Http
 */
class Server
{
    private const SWOOLE_EXT_NAME = 'swoole';

    private $testMode = false;

    /**
     * @var SwooleServer|null
     */
    protected $handler;

    /**
     * @var Configuration
     */
    protected $settings;

    /**
     * @var Listener[]
     */
    protected $listeners = [];

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(Configuration $settings = null)
    {
        if (!extension_loaded(self::SWOOLE_EXT_NAME)) {
            throw new RuntimeException('Swoole extenstion is missing, please install it and try again.');
        }

        $this->logger = $logger ?? new NullLogger();
        $this->settings = $settings ?? new Configuration();
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

    /**
     * Adds listener that is attached to server once it is run.
     *
     * @param Listener $listener
     */
    public function addListener(Listener $listener): void
    {
        $this->listeners[] = $listener;
        if ($this->handler !== null) {
            $this->attachListener($listener);
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
     * Returns information about client.
     *
     * @param int $clientId
     * @return ClientStats
     */
    public function getClientStats(int $clientId): ClientStats
    {
        return new ClientStats($this->handler->getClientInfo($clientId));
    }

    /**
     * Returns information about server.
     *
     * @return ServerStats
     */
    public function getServerStats(): ServerStats
    {
        return new ServerStats($this->handler->stats());
    }

    public function enableTestMode(): void
    {
        $this->testMode = true;
    }

    /**
     * Starts the server.
     */
    public function start(): void
    {
        $flags = SWOOLE_SOCK_TCP;

        if ($this->settings->isSslEnabled()) {
            $flags |= SWOOLE_SSL;
        }

        $settings = $this->settings->getSettings();

        if (!$this->testMode) {
            $this->handler = new SwooleServer($settings['address'], $settings['port'], SWOOLE_PROCESS, $flags);
        }

        $this->handler->set($settings);

        // Start the server.
        foreach ($this->listeners as $listener) {
            $this->attachListener($listener);
        }
        $this->handler->start();
    }

    /**
     * Stops the server.
     */
    public function stop(): void
    {
        if ($this->handler !== null) {
            $this->handler->shutdown();
            $this->handler = null;
        }
    }

    protected function attachListener(Listener $listener): void
    {
        if ($listener instanceof OnConnect) {
            $this->attachOnConnectListener($listener);
        }

        if ($listener instanceof OnClose) {
            $this->attachOnCloseListener($listener);
        }

        if ($listener instanceof OnShutdown) {
            $this->attachOnShutdownListener($listener);
        }

        if ($listener instanceof OnStart) {
            $this->attachOnStartListener($listener);
        }
    }

    protected function attachOnConnectListener(OnConnect $listener): void
    {
        $this->handler->on('Connect', function($handler, $clientId) use ($listener) {
            $listener->onConnect($this, $clientId);
        });
    }

    protected function attachOnCloseListener(OnClose $listener): void
    {
        $this->handler->on('Close', function($handler, $clientId) use ($listener) {
            $listener->onClose($this, $clientId);
        });
    }

    protected function attachOnShutdownListener(OnShutdown $listener): void
    {
        $this->handler->on('Shutdown', function() use ($listener) {
            $listener->onShutdown($this);
        });
    }

    protected function attachOnStartListener(OnStart $listener): void
    {
        $this->handler->on('Start', function() use ($listener) {
            $listener->onStart($this);
        });
    }
}
