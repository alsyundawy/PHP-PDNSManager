<?php

declare(strict_types=1);

namespace App\Core;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;

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

    public function __construct(string|\App\Core\Config $logPathOrConfig, string $channel = 'app', string $level = 'debug')
    {
        if ($logPathOrConfig instanceof \App\Core\Config) {
            $this->logPath = (string) ($logPathOrConfig->get('app.log_path') ?? dirname(__DIR__, 2) . '/storage/logs');
            $level = (string) ($logPathOrConfig->get('app.log_level') ?? $level);
        } else {
            $this->logPath = $logPathOrConfig;
        }
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

    private function parseLevel(string $level): Level
    {
        return match (strtolower($level)) {
            'debug'     => Level::Debug,
            'info'      => Level::Info,
            'notice'    => Level::Notice,
            'warning'   => Level::Warning,
            'error'     => Level::Error,
            'critical'  => Level::Critical,
            'alert'     => Level::Alert,
            'emergency' => Level::Emergency,
            default     => Level::Debug,
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
