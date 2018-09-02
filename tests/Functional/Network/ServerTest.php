<?php declare(strict_types=1);

namespace Igni\Tests\Functional\Network;

use Igni\Network\Client\ClientManager;
use Igni\Network\Server;
use Mockery;
use PHPUnit\Framework\TestCase;

final class ServerTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        self::assertInstanceOf(Server::class, new Server());
        self::assertInstanceOf(Server::class, new Server(new Server\Configuration()));
    }

    public function testGetClientManager(): void
    {
        $server = new Server();
        self::assertInstanceOf(ClientManager::class, $server->getClientManager());
    }

    public function testStartAndStop(): void
    {
        /** @var Server|Mockery\MockInterface $server */
        $server = new Server(new Server\Configuration(0));
        $server->start();
        $server->stop();
    }
}
