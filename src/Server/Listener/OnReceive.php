<?php declare(strict_types=1);

namespace Igni\Network\Server\Listener;

use Igni\Network\Client;
use Igni\Network\Server;
use Igni\Network\Server\Listener;

/**
 * This event happens when the new connection comes in.
 */
interface OnReceive extends Listener
{
    /**
     * Handles receive server event.
     *
     * @param Server $server
     * @param Client $client
     * @param string $data
     */
    public function onReceive(Server $server, Client $client, string $data): void;
}
