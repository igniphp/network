<?php declare(strict_types=1);

namespace Igni\Network\Server;

use Igni\Network\Server;

/**
 * The event happens when the TCP connection between the client and the server is closed.
 */
interface OnCloseListener extends Listener
{
    /**
     * Handles server close event.
     *
     * @param Server $server
     * @param Client $client
     */
    public function onClose(Server $server, Client $client): void;
}
