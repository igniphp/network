<?php declare(strict_types=1);

namespace Igni\Tests\Functional\Network\Http\Middleware;

use Igni\Exception\RuntimeException;
use Igni\Network\Http\Middleware\CallableMiddleware;
use Igni\Network\Http\ServerRequest;
use Igni\Tests\Fixtures\CustomHttpException;
use Igni\Network\Http\Middleware\ErrorMiddleware;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CallableMiddlewareTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $middleware = new CallableMiddleware(function() {});
        self::assertInstanceOf(CallableMiddleware::class, $middleware);
    }

    /**
     * @expectedException \Igni\Network\Exception\MiddlewareException
     */
    public function testNegativeUsageCase(): void
    {
        $middleware = new CallableMiddleware(function() {});
        $middleware->process(
            Mockery::mock(ServerRequestInterface::class),
            Mockery::mock(RequestHandlerInterface::class)
        );
    }

    public function testPositiveUsageCase(): void
    {
        $middleware = new CallableMiddleware(function(ServerRequestInterface $request, RequestHandlerInterface $next) {
            return $next->handle($request);
        });

        $requestHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return Mockery::mock(ResponseInterface::class);
            }
        };

        $results = $middleware->process(Mockery::mock(ServerRequestInterface::class), $requestHandler);

        $this->assertInstanceOf(ResponseInterface::class, $results);
    }
}
