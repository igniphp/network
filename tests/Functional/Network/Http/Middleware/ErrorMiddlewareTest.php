<?php declare(strict_types=1);

namespace Igni\Tests\Functional\Network\Http\Middleware;

use Igni\Exception\RuntimeException;
use Igni\Tests\Fixtures\CustomHttpException;
use Igni\Network\Http\Middleware\ErrorMiddleware;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ErrorMiddlewareTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $middleware = new ErrorMiddleware(function() {});
        self::assertInstanceOf(ErrorMiddleware::class, $middleware);
    }

    public function testInvokeWithException(): void
    {
        $middleware = new ErrorMiddleware(function() {});
        $requestHandler = Mockery::mock(RequestHandlerInterface::class);
        $requestHandler
            ->shouldReceive('handle')
            ->andThrow(RuntimeException::class);

        $response = $middleware->process(Mockery::mock(ServerRequestInterface::class), $requestHandler);

        self::assertSame(500, $response->getStatusCode());
    }

    public function testInvokeWithError(): void
    {
        $middleware = new ErrorMiddleware(function() {});
        $requestHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $a = $call['undefined'];
            }
        };

        $response = $middleware->process(Mockery::mock(ServerRequestInterface::class), $requestHandler);

        self::assertSame(500, $response->getStatusCode());
    }

    public function testWithCustomException(): void
    {
        $error = new CustomHttpException('Nothing to see here', 400);
        $middleware = new ErrorMiddleware(function() {});
        $requestHandler = new class($error) implements RequestHandlerInterface {
            private $error;

            public function __construct(CustomHttpException $error)
            {
                $this->error = $error;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw $this->error;
            }
        };

        $response = $middleware->process(Mockery::mock(ServerRequestInterface::class), $requestHandler);

        self::assertSame($error->asResponse()->getStatusCode(), $response->getStatusCode());
        self::assertSame((string) $error->asResponse()->getBody(), (string) $response->getBody());
    }
}
