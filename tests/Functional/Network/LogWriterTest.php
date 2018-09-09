<?php declare(strict_types=1);

namespace Igni\Tests\Functional\Network\Server;

use Igni\Network\Client;
use Igni\Network\Logger;
use Igni\Network\LogWriter;
use Igni\Network\Server;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Mockery;

final class LogWriterTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        self::assertInstanceOf(LoggerInterface::class, new LogWriter());
        self::assertInstanceOf(LoggerInterface::class, new LogWriter(new Logger()));
    }

    public function testOnClose(): void
    {
        self::expectOutputRegex('/\[\d{4}-\d{2}-\d{2} \d{2}\:\d{2}\:\d{2}\]\[INFO\] \- Client .*? closed connection/');
        $writer = new LogWriter(new Logger());
        $writer->onClose(Mockery::mock(Server::class), Mockery::mock(Client::class));
    }

    public function testOnConnect(): void
    {
        self::expectOutputRegex('/\[\d{4}-\d{2}-\d{2} \d{2}\:\d{2}\:\d{2}\]\[INFO\] \- Client .*? connected/');
        $writer = new LogWriter(new Logger());
        $writer->onConnect(Mockery::mock(Server::class), Mockery::mock(Client::class));
    }

    public function testOnShutdown(): void
    {
        self::expectOutputRegex('/\[\d{4}-\d{2}-\d{2} \d{2}\:\d{2}\:\d{2}\]\[ALERT\] \- Server shutdown/');
        $writer = new LogWriter(new Logger());
        $writer->onShutdown(Mockery::mock(Server::class));
    }

    public function testOnStart(): void
    {
        $server = Mockery::mock(Server::class);
        $server
            ->shouldReceive('getConfiguration')
            ->andReturn(new Server\Configuration());
        self::expectOutputRegex('/\[\d{4}-\d{2}-\d{2} \d{2}\:\d{2}\:\d{2}\]\[ALERT\] \- Server is listening .*/');
        $writer = new LogWriter(new Logger());
        $writer->onStart($server);
    }

    public function testDecoratedMethods(): void
    {
        $loggerMock = Mockery::mock(LoggerInterface::class);
        $writer = new LogWriter($loggerMock);

        $loggerMock->shouldReceive('warning')
            ->withArgs(function($message, $context) {
                self::assertSame('Test warning {a}', $message);
                self::assertSame(['a' => 1], $context);
                return true;
            });
        $writer->warning('Test warning {a}', ['a' => 1]);

        $loggerMock->shouldReceive('error')
            ->withArgs(function($message, $context) {
                self::assertSame('Test error {a}', $message);
                self::assertSame(['a' => 1], $context);
                return true;
            });
        $writer->error('Test error {a}', ['a' => 1]);

        $loggerMock->shouldReceive('critical')
            ->withArgs(function($message, $context) {
                self::assertSame('Test critical {a}', $message);
                self::assertSame(['a' => 1], $context);
                return true;
            });
        $writer->critical('Test critical {a}', ['a' => 1]);

        $loggerMock->shouldReceive('alert')
            ->withArgs(function($message, $context) {
                self::assertSame('Test alert {a}', $message);
                self::assertSame(['a' => 1], $context);
                return true;
            });
        $writer->alert('Test alert {a}', ['a' => 1]);

        $loggerMock->shouldReceive('emergency')
            ->withArgs(function($message, $context) {
                self::assertSame('Test emergency {a}', $message);
                self::assertSame(['a' => 1], $context);
                return true;
            });
        $writer->emergency('Test emergency {a}', ['a' => 1]);

        $loggerMock->shouldReceive('notice')
            ->withArgs(function($message, $context) {
                self::assertSame('Test notice {a}', $message);
                self::assertSame(['a' => 1], $context);
                return true;
            });
        $writer->notice('Test notice {a}', ['a' => 1]);

        $loggerMock->shouldReceive('info')
            ->withArgs(function($message, $context) {
                self::assertSame('Test info {a}', $message);
                self::assertSame(['a' => 1], $context);
                return true;
            });
        $writer->info('Test info {a}', ['a' => 1]);

        $loggerMock->shouldReceive('debug')
            ->withArgs(function($message, $context) {
                self::assertSame('Test debug {a}', $message);
                self::assertSame(['a' => 1], $context);
                return true;
            });
        $writer->debug('Test debug {a}', ['a' => 1]);
    }
}
