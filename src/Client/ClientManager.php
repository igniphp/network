<?php declare(strict_types=1);

namespace Igni\Network\Client;

use ArrayIterator;
use Igni\Network\Client;
use Igni\Network\Exception\ClientManagerException;
use IteratorAggregate;
use Swoole\Server as SwooleServer;
use Traversable;
use Countable;

class ClientManager implements IteratorAggregate, Countable
{
    /**
     * @var Client[]
     */
    private $clients = [];

    private $length = 0;

    public function createClient(SwooleServer $handler, int $id): void
    {
        $this->length++;
        $this->clients[$id] = new Client($handler, $id);
    }

    public function getClient(int $id): Client
    {
        if (!$this->hasClient($id)) {
            throw ClientManagerException::forNonExistingClient($id);
        }

        return $this->clients[$id];
    }

    public function hasClient(int $id): bool
    {
        return isset($this->clients[$id]);
    }

    public function removeClient(int $id): void
    {
        $this->getClient($id);
        unset($this->clients[$id]);
        $this->length--;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->clients);
    }

    public function count(): int
    {
        return $this->length;
    }
}
