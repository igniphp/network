<?php declare(strict_types=1);

namespace Igni\Network\Server\Listener;

use Igni\Network\Server;
use Igni\Network\Server\Listener;
use Igni\Network\Client;

/**
 * This event happens when the new connection comes in.
 */
interface OnConnect extends Listener
{
    /**
     * Handles connect server event.
     *
     * @param Server $server
     * @param Client $client
     */
    public function onConnect(Server $server, Client $client): void;
}
