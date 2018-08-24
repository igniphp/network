<?php declare(strict_types=1);

namespace Igni\Network\Server;

use Igni\Network\Server;

/**
 * The event happens when the server starts.
 */
interface OnStart extends Listener
{
    /**
     * Handles server's start event.
     *
     * @param Server $server
     */
    public function onStart(Server $server): void;
}
