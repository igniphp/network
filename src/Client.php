<?php declare(strict_types=1);

namespace Igni\Network;

use Igni\Exception\RuntimeException;
use Igni\Network\Client\ClientInfo;
use Igni\Network\Exception\ClientException;
use Swoole\Server as SwooleServer;

class Client
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var SwooleServer
     */
    private $handler;

    /**
     * Client constructor.
     * @param SwooleServer $handler
     * @param int $clientId
     */
    public function __construct($handler, int $clientId)
    {
        $this->handler = $handler;
        $this->id = $clientId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getInfo(): ClientInfo
    {
        return new ClientInfo($this->handler->getClientInfo($this->id));
    }

    public function isActive(): bool
    {
        return $this->handler->exist($this->id);
    }

    public function pause(): void
    {
        $this->handler->pause($this->id);
    }

    public function resume(): void
    {
        $this->handler->resume($this->id);
    }

    /**
     * @param string $data
     * @throws RuntimeException
     */
    public function send(string $data = null): void
    {
        if (!$this->handler->send($this->id, $data)) {
            throw ClientException::forSendFailure($this, $data);
        }
    }

    public function protect(): void
    {
        $this->handler->protect($this->id);
    }

    /**
     * @param string|null $data
     */
    public function wait(string $data = null): void
    {
        if (!$this->handler->sendwait($this->id, $data)) {
            throw ClientException::forWaitFailure($this);
        }
    }

    public function confirm(): void
    {
        $this->handler->confirm($this->id);
    }

    public function close(): void
    {
        $this->handler->close($this->id);
    }

    public function __toString(): string
    {
        return self::class . "[{$this->id}]";
    }
}
