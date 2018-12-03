<?php declare(strict_types=1);

namespace Igni\Tests\Functional\Network\Http;

use Igni\Network\Http\Route;
use PHPUnit\Framework\TestCase;

final class RouteTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        self::assertInstanceOf(Route::class, new Route('test'));
    }

    public function testRouteNaming(): void
    {
        $route = Route::get('/test/{var}');
        self::assertSame('get_test_var', $route->getName());
        $route = Route::get('/test/{var1}<\d+>');
        self::assertSame('get_test_var1', $route->getName());
        $route = Route::get('/test/{blabla}', 'test_route');
        self::assertSame('test_route', $route->getName());
    }

    public function testWithController(): void
    {
        $route = Route::delete('/test');
        $routeWithController = $route->withController('test');

        self::assertNull($route->getController());
        self::assertNotSame($route, $routeWithController);
        self::assertSame('test', $routeWithController->getController());
    }

    public function testWithMethods(): void
    {
        $route = Route::put('/test');

        self::assertSame(['PUT'], $route->getMethods());
        $routeWithMethods = $route->withMethods(['POST', 'GET']);

        self::assertSame(['PUT'], $route->getMethods());
        self::assertNotSame($route, $routeWithMethods);
        self::assertSame(['POST', 'GET'], $routeWithMethods->getMethods());
    }

    public function testWithAttributes(): void
    {
        $route = Route::post('/test');
        self::assertEmpty($route->getAttributes());

        $routeWithAttributes = $route->withAttributes(['a' => 'test']);

        self::assertEmpty($route->getAttributes());
        self::assertNotSame($route, $routeWithAttributes);
        self::assertSame(['a' => 'test'], $routeWithAttributes->getAttributes());
    }
}
