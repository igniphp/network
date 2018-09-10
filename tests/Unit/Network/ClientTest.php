<?php declare(strict_types=1);

namespace Igni\Tests\Functional\Network\Server;

use Igni\Network\Exception\ClientException;
use Igni\Network\Server\Client;
use Igni\Network\Server\ClientInfo;
use Mockery;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ClientTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        self::assertInstanceOf(Client::class, new Client(null, 1));
    }

    public function testGetInfo(): void
    {
        $handlerMock = Mockery::mock(stdClass::class);
        $handlerMock->shouldReceive('getClientInfo')
            ->andReturn([
                'remote_port' => 80,
                'remote_ip' => 'mockedip',
                'connect_time' => time(),
            ]);
        $client = new Client($handlerMock, 1);

        self::assertInstanceOf(ClientInfo::class, $client->getInfo());
        self::assertSame(80, $client->getInfo()->getPort());
    }

    public function testIsActive(): void
    {
        $handlerMock = Mockery::mock(stdClass::class);
        $handlerMock->shouldReceive('exist')
            ->withArgs([1])
            ->andReturn(true);
        $client = new Client($handlerMock, 1);
        
        self::assertSame(true, $client->isActive());
    }

    public function testPause(): void
    {
        $handlerMock = Mockery::mock(stdClass::class);
        $handlerMock->shouldReceive('pause')
            ->withArgs([1]);
        $client = new Client($handlerMock, 1);

        self::assertNull($client->pause());
    }

    public function testResume(): void
    {
        $handlerMock = Mockery::mock(stdClass::class);
        $handlerMock->shouldReceive('resume')
            ->withArgs([1]);
        $client = new Client($handlerMock, 1);

        self::assertNull($client->resume());
    }

    public function testProtect(): void
    {
        $handlerMock = Mockery::mock(stdClass::class);
        $handlerMock->shouldReceive('protect')
            ->withArgs([1]);
        $client = new Client($handlerMock, 1);

        self::assertNull($client->protect());
    }

    public function testConfirm(): void
    {
        $handlerMock = Mockery::mock(stdClass::class);
        $handlerMock->shouldReceive('confirm')
            ->withArgs([1]);
        $client = new Client($handlerMock, 1);

        self::assertNull($client->confirm());
    }

    public function testClose(): void
    {
        $handlerMock = Mockery::mock(stdClass::class);
        $handlerMock->shouldReceive('close')
            ->withArgs([1]);
        $client = new Client($handlerMock, 1);

        self::assertNull($client->close());
    }

    public function testSendSuccess(): void
    {
        $handlerMock = Mockery::mock(stdClass::class);
        $handlerMock->shouldReceive('send')
            ->withArgs([1, 'test'])
            ->andReturn(true);
        $client = new Client($handlerMock, 1);

        self::assertNull($client->send('test'));
    }

    public function testSendFailure(): void
    {
        $handlerMock = Mockery::mock(stdClass::class);
        $handlerMock->shouldReceive('send')
            ->withArgs([1, 'test'])
            ->andReturn(false);
        $client = new Client($handlerMock, 1);

        $this->expectException(ClientException::class);
        self::assertNull($client->send('test'));
    }
}
