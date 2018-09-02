<?php declare(strict_types=1);

namespace Igni\Tests\Functional\Network\Server;

use Igni\Network\Client\ClientInfo;
use PHPUnit\Framework\TestCase;

final class ClientStatsTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        self::assertInstanceOf(ClientInfo::class, new ClientInfo([]));
    }

    public function testGetPort(): void
    {
        $stats = new ClientInfo([
            'remote_port' => 80
        ]);

        self::assertSame(80, $stats->getPort());
    }

    public function testGetConnectionTime(): void
    {
        $stats = new ClientInfo([
            'connect_time' => 180
        ]);

        self::assertSame(180, $stats->getConnectTime());
    }

    public function testGetIp(): void
    {
        $stats = new ClientInfo([
            'remote_ip' => '0.0.0.0'
        ]);

        self::assertSame('0.0.0.0', $stats->getIp());
    }
}
