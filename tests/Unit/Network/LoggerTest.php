<?php declare(strict_types=1);

namespace Igni\Tests\Unit\Network\Server;

use Igni\Network\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class LoggerTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        self::assertInstanceOf(LoggerInterface::class, new Logger());
    }

    public function testEmergency(): void
    {
        self::expectOutputRegex('/\[\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}\]\[EMERGENCY\]\s\-\sTest message 1/');
        $logger = new Logger();
        $logger->emergency('Test message {a}', ['a' => 1]);
    }

    public function testAlert(): void
    {
        self::expectOutputRegex('/\[\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}\]\[ALERT\]\s\-\sTest message 1/');
        $logger = new Logger();
        $logger->alert('Test message {a}', ['a' => 1]);
    }

    public function testCritical(): void
    {
        self::expectOutputRegex('/\[\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}\]\[CRITICAL\]\s\-\sTest message 1/');
        $logger = new Logger();
        $logger->critical('Test message {a}', ['a' => 1]);
    }

    public function testError(): void
    {
        self::expectOutputRegex('/\[\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}\]\[ERROR\]\s\-\sTest message 1/');
        $logger = new Logger();
        $logger->error('Test message {a}', ['a' => 1]);
    }

    public function testWarning(): void
    {
        self::expectOutputRegex('/\[\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}\]\[WARNING\]\s\-\sTest message 1/');
        $logger = new Logger();
        $logger->warning('Test message {a}', ['a' => 1]);
    }

    public function testNotice(): void
    {
        self::expectOutputRegex('/\[\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}\]\[NOTICE\]\s\-\sTest message 1/');
        $logger = new Logger();
        $logger->notice('Test message {a}', ['a' => 1]);
    }

    public function testInfo(): void
    {
        self::expectOutputRegex('/\[\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}\]\[INFO\]\s\-\sTest message 1/');
        $logger = new Logger();
        $logger->info('Test message {a}', ['a' => 1]);
    }

    public function testDebug(): void
    {
        self::expectOutputRegex('/\[\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}\]\[DEBUG\]\s\-\sTest message 1/');
        $logger = new Logger();
        $logger->debug('Test message {a}', ['a' => 1]);
    }
}
