<?php
declare(strict_types=1);
namespace App\Core;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

/**
 * BUGFIX: PHPStan errors — class aliasing was missing, causing
 * "Instantiated class Monolog not found" errors at static analysis time.
 * All Monolog references now use fully-qualified aliases.
 */
class Logger
{
    private MonologLogger $logger;
    private array $channels = [];
    private string $logPath;

    public function __construct(string $logPath, string $channel = 'app', string $level = 'debug')
    {
        $this->logPath = $logPath;
        $this->logger = $this->createChannel($channel, $level);
    }

    private function createChannel(string $channel, string $level = 'debug'): MonologLogger
    {
        $log = new MonologLogger($channel);
        $handler = new RotatingFileHandler(
            $this->logPath . '/' . $channel . '.log',
            30,
            $this->parseLevel($level)
        );
        $handler->setFormatter(new LineFormatter(null, null, true, true));
        $log->pushHandler($handler);
        return $log;
    }

    private function parseLevel(string $level): int
    {
        return match (strtolower($level)) {
            'debug'     => MonologLogger::DEBUG,
            'info'      => MonologLogger::INFO,
            'notice'    => MonologLogger::NOTICE,
            'warning'   => MonologLogger::WARNING,
            'error'     => MonologLogger::ERROR,
            'critical'  => MonologLogger::CRITICAL,
            'alert'     => MonologLogger::ALERT,
            'emergency' => MonologLogger::EMERGENCY,
            default      => MonologLogger::DEBUG,
        };
    }

    public function getLogger(): MonologLogger
    {
        return $this->logger;
    }

    public function channel(string $channel): MonologLogger
    {
        if (!isset($this->channels[$channel])) {
            $this->channels[$channel] = $this->createChannel($channel);
        }
        return $this->channels[$channel];
    }

    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }
}
