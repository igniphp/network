<?php declare(strict_types=1);

namespace Igni\Tests\Functional\Network\Server;

use Closure;
use Igni\Network\Http\Response;
use Igni\Network\Http\Stream;
use Igni\Network\Server\Client;
use Igni\Network\Server\Configuration;
use Igni\Network\Server\HandlerFactory;
use Igni\Network\Server\HttpServer;
use Igni\Network\Server\OnRequestListener;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\NullLogger;
use stdClass;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\Http\Response as SwooleHttpResponse;

final class HttpServerTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        self::assertInstanceOf(HttpServer::class, new HttpServer());
        self::assertInstanceOf(HttpServer::class, new HttpServer(new Configuration()));
        self::assertInstanceOf(HttpServer::class, new HttpServer(new Configuration(), new NullLogger()));
        self::assertInstanceOf(HttpServer::class, new HttpServer(new Configuration(), new NullLogger(), Mockery::mock(HandlerFactory::class)));
    }

    public function testOnRequestListener(): void
    {
        $server = $this->mockServer($listeners);
        $onRequest = Mockery::mock(OnRequestListener::class);
        $onRequest
            ->shouldReceive('onRequest')
            ->withArgs(function(Client $client, ServerRequestInterface $request, ResponseInterface $response) {
                self::assertSame(1, $client->getId());
                self::assertSame(200, $response->getStatusCode());
                return true;
            })
            ->andReturn(Response::asText('Test 1'));
        $server->addListener($onRequest);
        $server->start();

        $swooleRequest = Mockery::mock(SwooleHttpRequest::class);
        $swooleRequest->fd = 1;
        $swooleResponse = Mockery::mock(SwooleHttpResponse::class);
        $swooleResponse->shouldReceive('header');
        $swooleResponse->shouldReceive('status')
            ->withArgs([200]);
        $swooleResponse->shouldReceive('end')
            ->withArgs(['Test 1']);

        $listeners['Connect']($server, 1);
        $listeners['Request']($swooleRequest, $swooleResponse);
        $listeners['Close']($server, 1);
    }

    public function testGzipSupport(): void
    {
        $server = $this->mockServer($listeners);

        $content = Mockery::mock(Stream::class);
        $content
            ->shouldReceive('getContents')
            ->andReturn('test gzip');

        $psrResponse = Mockery::mock(ResponseInterface::class);
        $psrResponse
            ->shouldReceive('getHeaders')
            ->andReturn([]);
        $psrResponse
            ->shouldReceive('getBody')
            ->andReturn($content);
        $psrResponse
            ->shouldReceive('getStatusCode')
            ->andReturn(200);

        $onRequestMock = Mockery::mock(OnRequestListener::class);
        $onRequestMock
            ->shouldReceive('onRequest')
            ->andReturn($psrResponse);

        $server->addListener($onRequestMock);
        $server->start();

        $swooleRequestMock = Mockery::mock(SwooleHttpRequest::class);
        $swooleRequestMock->fd = 1;
        $swooleRequestMock->header = ['accept-encoding' => 'gzip, deflate'];

        $swooleResponseMock = Mockery::mock(SwooleHttpResponse::class);
        $swooleResponseMock
            ->shouldReceive('status')
            ->withArgs([200]);
        $swooleResponseMock
            ->shouldReceive('header');
        $swooleResponseMock
            ->shouldReceive('end')
            ->withArgs(function(string $result) {
                self::assertSame('test gzip', $result);
                return true;
            });
        $swooleResponseMock
            ->shouldReceive('gzip')
            ->withArgs([1]);

        $listeners['Connect']($server, 1);
        $listeners['Request']($swooleRequestMock, $swooleResponseMock);
        $listeners['Close']($server, 1);
    }

    private function mockHandlerFactory(Configuration $configuration, &$listeners = []): HandlerFactory
    {
        $handler = Mockery::mock(stdClass::class);
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

    private function mockServer(&$listeners = []): HttpServer
    {
        $configuration = new Configuration();
        $handlerFactory = $this->mockHandlerFactory($configuration, $listeners);

        return new HttpServer($configuration, new NullLogger(), $handlerFactory);
    }
}
