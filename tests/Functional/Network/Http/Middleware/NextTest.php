<?php declare(strict_types=1);

namespace Igni\Tests\Functional\Network\Http\Middleware;

use Igni\Network\Http\Middleware\Next;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SplQueue;
use Mockery;

final class NextTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        self::assertInstanceOf(Next::class, new Next(new SplQueue(), Mockery::mock(RequestHandlerInterface::class)));
    }

    public function testInvoke(): void
    {
        $requestHandlerMock = Mockery::mock(RequestHandlerInterface::class);
        $requestHandlerMock->shouldReceive('handle');
        $requestMock = Mockery::mock(ServerRequestInterface::class);
        $next = new Next(new SplQueue(), $requestHandlerMock);
        $response = $next($requestMock);

        self::assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testHandleWithEmptyQueue(): void
    {
        $requestHandlerMock = Mockery::mock(RequestHandlerInterface::class);
        $requestHandlerMock->shouldReceive('handle');
        $requestMock = Mockery::mock(ServerRequestInterface::class);
        $next = new Next(new SplQueue(), $requestHandlerMock);
        $response = $next->handle($requestMock);

        self::assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testHandle(): void
    {
        $requestHandlerMock = Mockery::mock(RequestHandlerInterface::class);
        $requestHandlerMock->shouldReceive('handle');
        $requestMock = Mockery::mock(ServerRequestInterface::class);
        $middlewareMock = Mockery::mock(MiddlewareInterface::class);
        $middlewareMock->shouldReceive('process');

        $queue = new SplQueue();
        $queue->push($middlewareMock);

        $next = new Next($queue, $requestHandlerMock);
        $response = $next->handle($requestMock);

        self::assertInstanceOf(ResponseInterface::class, $response);
    }
}
