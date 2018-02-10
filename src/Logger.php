<?php

namespace Miravel;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Class Logger
 *
 * The Miravel's logging class.
 *
 * @package Miravel
 */
class Logger
{
    /**
     * @var LoggerInterface
     */
    protected static $logger;

    /**
     * Set the logger object, compatible with Psr3 Logger interface.
     * By default, Miravel will set the Laravel's Monolog instance, unless
     * instructed otherwise e.g. when booting the service provider.
     *
     * @param LoggerInterface $logger  the logger object.
     */
    public static function setLogger(LoggerInterface $logger)
    {
        static::$logger = $logger;
    }

    /**
     * Log a message.
     *
     * @param string $message  the message to log.
     * @param string $level    see Psr\Log\LogLevel for available constants.
     * @param array $data      the context data, if any.
     *
     * @return void
     */
    public static function log(
        string $message,
        $level = LogLevel::INFO,
        array $data = []
    ) {
        if (static::$logger) {
            static::$logger->log($level, $message, $data);
        }
    }
}
