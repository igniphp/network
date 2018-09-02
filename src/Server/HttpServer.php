<?php declare(strict_types=1);

namespace Igni\Network\Http;

use Swoole\Http\Server as SwooleHttpServer;
use Igni\Network\Server;

class HttpServer extends Server
{
    /**
     * @return SwooleHttpServer
     */
    protected function createHandler()
    {
        $flags = SWOOLE_TCP;
        if ($this->configuration->isSslEnabled()) {
            $flags |= SWOOLE_SSL;
        }
        $settings = $this->configuration->toArray();
        $handler = new SwooleHttpServer($settings['address'], $settings['port'], SWOOLE_PROCESS, $flags);
        $handler->set($settings);

        return $handler;
    }
}
