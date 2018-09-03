<?php declare(strict_types=1);

namespace Igni\Tests\Functional\Network;

use Igni\Network\Client;
use Igni\Network\Exception\ServerException;
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

    public function testStartAndStop(): void
    {
        $configuration = new Configuration();
        $logger = Mockery::mock(LoggerInterface::class);
        $handlerFactory = $this->mockHandlerFactory($configuration, $listeners);

        /** @var Server|Mockery\MockInterface $server */
        $server = new Server($configuration, $logger, $handlerFactory);
        $server->start();
        self::assertSame(['Connect', 'Close', 'Shutdown', 'Start', 'Receive'], array_keys($listeners));
        self::assertTrue(self::readAttribute($server, 'started'));
        $server->stop();
        self::assertFalse(self::readAttribute($server, 'started'));
    }

    public function testGetConfig(): void
    {
        $server = new Server();
        self::assertInstanceOf(Configuration::class, $server->getConfiguration());

        $configuration = new Configuration();
        $server = new Server($configuration);
        self::assertSame($configuration, $server->getConfiguration());
    }

    public function testFailureOnGetStats(): void
    {
        $this->expectException(ServerException::class);
        $server = new Server();
        $server->getServerStats();
    }

    public function testClientCreation(): void
    {
        $server = $this->mockServer($listeners);
        $server->start();

        $listeners['Connect'](null, 1);
        $clients = self::readAttribute($server, 'clients');
        self::assertCount(1, $clients);
        self::assertInstanceOf(Client::class, $clients[1]);
        $listeners['Close'](null, 1);
        self::assertCount(0, self::readAttribute($server, 'clients'));
    }

    public function testOnStartListener(): void
    {
        $server = $this->mockServer($listeners);
        $onStart = Mockery::mock(Server\Listener\OnStart::class);
        $onStart
            ->shouldReceive('onStart')
            ->withArgs(function(Server $passed) use ($server) {
                self::assertSame($server, $passed);
                return true;
            });
        $server->addListener($onStart);
        $server->start();

        $listeners['Start']();
    }

    public function testOnReceiveListener(): void
    {
        $onReceive = Mockery::mock(Server\Listener\OnReceive::class);
        $onReceive
            ->shouldReceive('onReceive')
            ->withArgs(function(Server $server, Client $client, string $data) {
                self::assertSame('Test', $data);
                self::assertSame(1, $client->getId());

                return true;
            });

        /** @var Server|Mockery\MockInterface $server */
        $server = $this->mockServer($listeners);
        $server->addListener($onReceive);
        $server->start();

        $listeners['Connect'](null, 1);
        $listeners['Receive'](null, 1, 0, 'Test');
    }

    public function testOnConnectListener(): void
    {
        $server = $this->mockServer($listeners);
        $onConnect = Mockery::mock(Server\Listener\OnConnect::class);
        $onConnect
            ->shouldReceive('onConnect')
            ->withArgs(function(Server $passed, Client $client) use ($server) {
                self::assertSame(1, $client->getId());
                self::assertSame($server, $passed);
                return true;
            });
        $server->addListener($onConnect);
        $server->start();

        $listeners['Connect'](null, 1);
    }

    public function testOnShutdownListener(): void
    {
        $server = $this->mockServer($listeners);
        $onShutdown = Mockery::mock(Server\Listener\OnShutdown::class);
        $onShutdown
            ->shouldReceive('onShutdown')
            ->withArgs(function(Server $passed) use ($server) {
                self::assertSame($server, $passed);
                return true;
            });
        $server->addListener($onShutdown);
        $server->start();

        $listeners['Shutdown']();
    }

    public function testOnCloseListener(): void
    {
        $server = $this->mockServer($listeners);
        $onClose = Mockery::mock(Server\Listener\OnClose::class);
        $onClose
            ->shouldReceive('onClose')
            ->withArgs(function(Server $passed, Client $client) use ($server) {
                self::assertSame($server, $passed);
                self::assertSame(1, $client->getId());
                return true;
            });
        $server->addListener($onClose);
        $server->start();

        $listeners['Connect'](null, 1);
        $listeners['Close'](null, 1);
    }

    public function testAddingListeners(): void
    {
        $onStart = Mockery::mock(Server\Listener\OnStart::class);
        $server = $this->mockServer($listeners);
        $server->addListener($onStart);
        $listeners = self::readAttribute($server, 'listeners');
        self::assertSame([Server\Listener\OnStart::class], array_keys($listeners));
        self::assertCount(1, $listeners[Server\Listener\OnStart::class]);
        self::assertTrue($server->hasListener($onStart));

        $onShutdown = Mockery::mock(Server\Listener\OnShutdown::class);
        $server = $this->mockServer($listeners);
        $server->addListener($onShutdown);
        $listeners = self::readAttribute($server, 'listeners');
        self::assertSame([Server\Listener\OnShutdown::class], array_keys($listeners));
        self::assertCount(1, $listeners[Server\Listener\OnShutdown::class]);
        self::assertTrue($server->hasListener($onShutdown));
        self::assertFalse($server->hasListener($onStart));

        $onClose = Mockery::mock(Server\Listener\OnClose::class);
        $server = $this->mockServer($listeners);
        $server->addListener($onClose);
        $listeners = self::readAttribute($server, 'listeners');
        self::assertSame([Server\Listener\OnClose::class], array_keys($listeners));
        self::assertCount(1, $listeners[Server\Listener\OnClose::class]);
        self::assertTrue($server->hasListener($onClose));

        $onConnect = Mockery::mock(Server\Listener\OnConnect::class);
        $server = $this->mockServer($listeners);
        $server->addListener($onConnect);
        $listeners = self::readAttribute($server, 'listeners');
        self::assertSame([Server\Listener\OnConnect::class], array_keys($listeners));
        self::assertCount(1, $listeners[Server\Listener\OnConnect::class]);
        self::assertTrue($server->hasListener($onConnect));

        $onReceive = Mockery::mock(Server\Listener\OnReceive::class);
        $server = $this->mockServer($listeners);
        $server->addListener($onReceive);
        $listeners = self::readAttribute($server, 'listeners');
        self::assertSame([Server\Listener\OnReceive::class], array_keys($listeners));
        self::assertCount(1, $listeners[Server\Listener\OnReceive::class]);
        self::assertTrue($server->hasListener($onReceive));
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
        $handler->shouldReceive('shutdown');

        $handlerFactory = Mockery::mock(HandlerFactory::class);
        $handlerFactory
            ->shouldReceive('createHandler')
            ->with($configuration)
            ->andReturn($handler);

        return $handlerFactory;
    }

    private function mockServer(&$listeners = []): Server
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $configuration = new Configuration();
        $handlerFactory = $this->mockHandlerFactory($configuration, $listeners);

        return new Server($configuration, $logger, $handlerFactory);
    }
}
