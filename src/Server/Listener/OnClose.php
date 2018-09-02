<?php declare(strict_types=1);

namespace Igni\Network\Server\Listener;

use Igni\Network\Client;
use Igni\Network\Server;
use Igni\Network\Server\Listener;

/**
 * The event happens when the TCP connection between the client and the server is closed.
 */
interface OnClose extends Listener
{
    /**
     * Handles server close event.
     *
     * @param Server $server
     * @param Client $client
     */
    public function onClose(Server $server, Client $client): void;
}
