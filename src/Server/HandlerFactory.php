<?php declare(strict_types=1);

namespace Igni\Network\Server;

interface HandlerFactory
{
    public function createHandler(Configuration $configuration);
}
