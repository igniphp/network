<?php declare(strict_types=1);

namespace Igni\Tests\Unit\Network\Server;

use Igni\Network\Exception\ConfigurationException;
use Igni\Network\Server\Configuration;
use PHPUnit\Framework\TestCase;

final class ConfigurationTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $config = new Configuration();
        self::assertInstanceOf(Configuration::class, $config);
        self::assertSame(
            [
                'address' => Configuration::DEFAULT_ADDRESS,
                'port' => Configuration::DEFAULT_PORT,
            ],
            $config->toArray()
        );
    }

    public function testEnableSsl(): void
    {
        $config = new Configuration();
        $config->enableSsl(
            FIXTURES_DIR . '/bob.crt',
            FIXTURES_DIR . '/bob.key'
        );

        self::assertTrue($config->isSslEnabled());
    }

    public function testEnableSslWithInvalidCertFile(): void
    {
        $this->expectException(ConfigurationException::class);
        $config = new Configuration();
        $config->enableSsl('invalid', FIXTURES_DIR . '/bob.key');
    }

    public function testEnableSslWithInvalidKeyFile(): void
    {
        $this->expectException(ConfigurationException::class);
        $config = new Configuration();
        $config->enableSsl(FIXTURES_DIR . '/bob.crt', 'invalid');
    }

    public function testEnableDaemon(): void
    {
        $config = new Configuration();
        $config->enableDaemon(FIXTURES_DIR . '/file.pid');

        self::assertTrue($config->isDaemonEnabled());
        self::assertSame(
            [
                'address' => Configuration::DEFAULT_ADDRESS,
                'port' => Configuration::DEFAULT_PORT,
                'daemonize' => true,
                'pid_file' => FIXTURES_DIR . '/file.pid',
            ],
            $config->toArray()
        );
    }
}
