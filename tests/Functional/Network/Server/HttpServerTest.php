<?php declare(strict_types=1);

namespace Igni\Tests\Functional\Network\Server;

use Igni\Network\Server\Configuration;
use Igni\Network\Server\HandlerFactory;
use Igni\Network\Server\HttpServer;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class HttpServerTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        self::assertInstanceOf(HttpServer::class, new HttpServer());
        self::assertInstanceOf(HttpServer::class, new HttpServer(new Configuration()));
        self::assertInstanceOf(HttpServer::class, new HttpServer(new Configuration(), new NullLogger()));
        self::assertInstanceOf(HttpServer::class, new HttpServer(new Configuration(), new NullLogger(), Mockery::mock(HandlerFactory::class)));
    }
}
