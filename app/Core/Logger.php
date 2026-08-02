<?php
declare(strict_types=1);
namespace App\Core;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class Logger
{
    private MonologLogger $logger;
    private array $channels = [];
    public function __construct(Config $config)
    {
        $this->logger = new MonologLogger($config->get('app.name', 'PHP-PDNSManager'));
        $this->setupHandlers($config);
    }
    private function setupHandlers(Config $config): void
    {
        $logPath = $config->get('app.log_path', __DIR__ . '/../../storage/logs');
        $level = $config->get('app.log_level', 'warning');
        $handler = new RotatingFileHandler($logPath . '/app.log', 30, $level);
        $handler->setFormatter(new LineFormatter(null, null, true, true));
        $this->logger->pushHandler($handler);
        $securityHandler = new RotatingFileHandler($logPath . '/security.log', 30, $level);
        $securityHandler->setFormatter(new LineFormatter(null, null, true, true));
        $this->logger->pushHandler($securityHandler);
        $auditHandler = new RotatingFileHandler($logPath . '/audit.log', 30, $level);
        $auditHandler->setFormatter(new LineFormatter(null, null, true, true));
        $this->logger->pushHandler($auditHandler);
        $this->channels['security'] = new MonologLogger('security', [$securityHandler]);
        $this->channels['audit'] = new MonologLogger('audit', [$auditHandler]);
    }
    public function getLogger(): MonologLogger
    {
        return $this->logger;
    }
    public function channel(string $name): MonologLogger
    {
        return $this->channels[$name] ?? $this->logger;
    }
    public function emergency(string $message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }
    public function alert(string $message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }
    public function critical(string $message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }
    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }
    public function warning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }
    public function notice(string $message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }
    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }
    public function debug(string $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }
}
