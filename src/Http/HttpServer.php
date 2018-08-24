<?php declare(strict_types=1);

namespace Igni\Network\Http;

use Igni\Network\Server;
use Igni\Network\Server\Listener;

class HttpServer extends Server
{
    protected function attachListener(Listener $listener): void
    {
        if ($listener instanceof Server\OnRequest) {
            $this->attachOnRequestListener($listener);
        }

        parent::attachListener($listener);
    }

    protected function attachOnRequestListener(Server\OnRequest $listener): void
    {
        $this->handler->on('Connect', function($handler, $clientId) use ($listener) {
            $listener->onConnect($this, $clientId);
        });
    }
}
