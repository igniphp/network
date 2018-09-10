<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Igni\Network\Server;
use Igni\Network\Server\Client;
use Igni\Network\Server\Configuration;

$configuration = new Configuration(8080);
$server = new Server($configuration);
$server->addListener(new class implements Server\OnConnectListener, Server\OnReceiveListener, Server\OnCloseListener {
    public function onConnect(Server $server, Client $client): void
    {
        echo "\n Server has {$server->getClientManager()->count()} open connections. \n";
    }

    public function onReceive(Server $server, Client $client, string $data): void
    {
        echo "\n Server has {$server->getClientManager()->count()} open connections. \n";
        $client->send($data);
        $client->close();
    }

    public function onClose(Server $server, Client $client): void
    {
        echo "$client closed the connection";
    }
});
$server->start();
