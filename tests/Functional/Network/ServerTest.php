<?php declare(strict_types=1);

namespace Igni\Tests\Functional\Network;

use Igni\Network\Client;
use Igni\Network\Server;
use Igni\Network\Server\Configuration;
use Igni\Network\Server\HandlerFactory;
use Mockery;
use Closure;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class ServerTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        self::assertInstanceOf(Server::class, new Server());
        self::assertInstanceOf(Server::class, new Server(new Configuration()));
        self::assertInstanceOf(Server::class, new Server(new Configuration(), new NullLogger()));
        self::assertInstanceOf(Server::class, new Server(new Configuration(), new NullLogger(), Mockery::mock(HandlerFactory::class)));
    }

    public function testStart(): void
    {
        $configuration = new Configuration();
        $logger = Mockery::mock(LoggerInterface::class);
        $handlerFactory = $this->mockHandlerFactory($configuration, $listeners);
        /** @var Server|Mockery\MockInterface $server */
        $server = new Server($configuration, $logger, $handlerFactory);
        $server->start();
        self::assertSame(['Connect', 'Close', 'Shutdown', 'Start', 'Receive'], array_keys($listeners));
    }

    public function testClientCreation(): void
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $configuration = new Configuration();
        $handlerFactory = $this->mockHandlerFactory($configuration, $listeners);

        /** @var Server|Mockery\MockInterface $server */
        $server = new Server($configuration, $logger, $handlerFactory);
        $server->start();

        $listeners['Connect'](null, 1);
        $clients = self::readAttribute($server, 'clients');
        self::assertCount(1, $clients);
        self::assertInstanceOf(Client::class, $clients[1]);
        $listeners['Close'](null, 1);
        self::assertCount(0, self::readAttribute($server, 'clients'));
    }

    private function mockHandlerFactory(Configuration $configuration, &$listeners = []): HandlerFactory
    {
        $handler = Mockery::mock(\stdClass::class);
        $handler->shouldReceive('on')
            ->withArgs(function(string $type, Closure $listener) use(&$listeners) {
                $listeners[$type] = $listener;
                return true;
            });
        $handler->shouldReceive('start');

        $handlerFactory = Mockery::mock(HandlerFactory::class);
        $handlerFactory
            ->shouldReceive('createHandler')
            ->with($configuration)
            ->andReturn($handler);

        return $handlerFactory;
    }
}
