<?php declare(strict_types=1);

namespace Igni\Network\Exception;

class ClientManagerException extends ServerException
{
    public static function forNonExistingClient(int $clientId): self
    {
        return new self("Client with id {$clientId} does not exist");
    }
}

