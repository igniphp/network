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
        self::assertSame(80, $config->getPort());
        self::assertSame('0.0.0.0', $config->getAddress());
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

    public function testSetMaxConnections(): void
    {
        $config = new Configuration();
        $config->setMaxConnections(10);
        self::assertSame(
            [
                'address' => Configuration::DEFAULT_ADDRESS,
                'port' => Configuration::DEFAULT_PORT,
                'max_conn' => 10,
            ],
            $config->toArray()
        );
    }

    public function testSetWorkers(): void
    {
        $config = new Configuration();
        $config->setWorkers(10);
        self::assertSame(
            [
                'address' => Configuration::DEFAULT_ADDRESS,
                'port' => Configuration::DEFAULT_PORT,
                'worker_num' => 10,
            ],
            $config->toArray()
        );
    }

    public function testSetMaxRequests(): void
    {
        $config = new Configuration();
        $config->setMaxRequests(10);
        self::assertSame(
            [
                'address' => Configuration::DEFAULT_ADDRESS,
                'port' => Configuration::DEFAULT_PORT,
                'max_request' => 10,
            ],
            $config->toArray()
        );
    }

    public function testSetMaximumBacklog(): void
    {
        $config = new Configuration();
        $config->setMaximumBacklog(10);
        self::assertSame(
            [
                'address' => Configuration::DEFAULT_ADDRESS,
                'port' => Configuration::DEFAULT_PORT,
                'backlog' => 10,
            ],
            $config->toArray()
        );
    }

    public function testSetDispatchMode(): void
    {
        $config = new Configuration();
        $config->setDispatchMode(Configuration::DISPATCH_FIXED_MODE);
        self::assertSame(
            [
                'address' => Configuration::DEFAULT_ADDRESS,
                'port' => Configuration::DEFAULT_PORT,
                'dispatch_mode' => 2,
            ],
            $config->toArray()
        );
    }

    public function testSetChroot(): void
    {
        $config = new Configuration();
        $config->setChroot(__DIR__);
        self::assertSame(
            [
                'address' => Configuration::DEFAULT_ADDRESS,
                'port' => Configuration::DEFAULT_PORT,
                'chroot' => __DIR__,
            ],
            $config->toArray()
        );
    }

    public function testSetOwnerGroup(): void
    {
        $config = new Configuration();
        $config->setOwnerGroup('test');
        self::assertSame(
            [
                'address' => Configuration::DEFAULT_ADDRESS,
                'port' => Configuration::DEFAULT_PORT,
                'group' => 'test',
            ],
            $config->toArray()
        );
    }

    public function testSetUploadDir(): void
    {
        $config = new Configuration();
        $config->setUploadDir(__DIR__);
        self::assertSame(
            [
                'address' => Configuration::DEFAULT_ADDRESS,
                'port' => Configuration::DEFAULT_PORT,
                'upload_tmp_dir' => __DIR__,
            ],
            $config->toArray()
        );
    }

    public function testSetBufferOutputSize(): void
    {
        $config = new Configuration();
        $config->setBufferOutputSize(10);
        self::assertSame(
            [
                'address' => Configuration::DEFAULT_ADDRESS,
                'port' => Configuration::DEFAULT_PORT,
                'buffer_output_size' => 10,
            ],
            $config->toArray()
        );
    }
}
