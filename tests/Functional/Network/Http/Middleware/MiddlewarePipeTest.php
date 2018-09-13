<?php declare(strict_types=1);

namespace Igni\Tests\Functional\Network\Http\Middleware;

use Igni\Network\Exception\MiddlewareException;
use Igni\Network\Http\Middleware\MiddlewarePipe;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SplQueue;

final class MiddlewarePipeTest extends TestCase
{
    public function testInstantiation(): void
    {
        self::assertInstanceOf(MiddlewarePipe::class, new MiddlewarePipe());
        self::assertInstanceOf(MiddlewarePipe::class, new MiddlewarePipe(new SplQueue()));
    }

    public function testInvokeOnEmptyPipe(): void
    {
        $this->expectException(MiddlewareException::class);
        $request = Mockery::mock(ServerRequestInterface::class);
        $pipe = new MiddlewarePipe();
        $pipe($request);
    }

    public function testInvoke(): void
    {
        $request = Mockery::mock(ServerRequestInterface::class);
        $response = Mockery::mock(ResponseInterface::class);

        $middleware = Mockery::mock(MiddlewareInterface::class);
        $middleware->shouldReceive('process')
            ->withArgs([$request, Mockery::any()])
            ->andReturn($response);

        $pipe = new MiddlewarePipe();
        $pipe->add($middleware);
        $middlewareResponse = $pipe($request);

        self::assertSame($response, $middlewareResponse);
    }

    public function testProcess(): void
    {
        $requestMock = Mockery::mock(ServerRequestInterface::class);
        $requestHandlerMock = Mockery::mock(RequestHandlerInterface::class);
        $requestHandlerMock->shouldReceive('handle');
        $pipe = new MiddlewarePipe();
        $response = $pipe->process($requestMock, $requestHandlerMock);

        self::assertInstanceOf(ResponseInterface::class, $response);
    }
}
