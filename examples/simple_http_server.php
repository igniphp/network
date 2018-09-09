<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Igni\Network\Server\Configuration;
use Igni\Network\Server\HttpServer;
use Igni\Network\Server\Listener\OnRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Igni\Network\Client;
use Igni\Network\Http\Stream;

$configuration = new Configuration(8080);
$server = new HttpServer($configuration);
$server->addListener(new class implements OnRequest {
    public function onRequest(Client $client, ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response->withBody(Stream::fromString("Hello from http server"));
    }
});
$server->start();
