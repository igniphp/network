<?php declare(strict_types=1);

namespace Igni\Network\Server;

use Swoole\Server as SwooleServer;

class TcpHandlerFactory implements HandlerFactory
{
    public function createHandler(Configuration $configuration)
    {
        $flags = SWOOLE_TCP;
        if ($configuration->isSslEnabled()) {
            $flags |= SWOOLE_SSL;
        }
        $settings = $configuration->toArray();
        $handler = new SwooleServer($settings['address'], $settings['port'], SWOOLE_PROCESS, $flags);
        $handler->set($settings);

        return $handler;
    }
}
