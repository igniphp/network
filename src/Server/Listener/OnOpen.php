<?php declare(strict_types=1);

namespace Igni\Network\Server\Listener;

use Igni\Network\Client;
use Igni\Network\Server;
use Igni\Network\Server\Listener;


interface OnOpen extends Listener
{

    public function onOpen(Server $server, Client $client): void;
}
