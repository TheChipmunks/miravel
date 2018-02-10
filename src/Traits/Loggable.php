<?php

namespace Miravel\Traits;

use Miravel\ExceptionHandler;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Miravel\Logger;
use Throwable;

/**
 * Trait Loggable
 *
 * Provides an interface to Miravel Logger. Any class using this trait can just
 * do: $this->info(...).
 *
 * Normally, it should be sufficient that Miravel implements it on Facade level,
 * so any other class can just call Miravel::info() etc.
 *
 * @package Miravel
 */
trait Loggable
{
    /**
     * Set the logger object, compatible with Psr3 Logger interface. By default,
     * Miravel will use the Laravel's Monolog instance, unless instructed
     * otherwise e.g. when booting the service provider.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        Logger::setLogger($logger);
    }

    /**
     * Log a message at arbitrary level.
     *
     * @param string $message  the message to log.
     * @param string $level    the level, see use Psr\Log\LogLevel
     * @param array $data      the context data, if any.
     */
    public function log(
        string $message,
        $level = LogLevel::INFO,
        array $data = []
    ) {
        Logger::log($message, $level, $data);
    }

    /**
     * Log a message at the debug level.
     *
     * @param string $message  the message to log.
     * @param array $data      the context data, if any.
     */
    public function debug(string $message, array $data = [])
    {
        return $this->log($message, LogLevel::DEBUG, $data);
    }

    /**
     * Log a message at the info level.
     *
     * @param string $message  the message to log.
     * @param array $data      the context data, if any.
     */
    public function info(string $message, array $data = [])
    {
        return $this->log($message, LogLevel::INFO, $data);
    }

    /**
     * Log a message at the notice level.
     *
     * @param string $message  the message to log.
     * @param array $data      the context data, if any.
     */
    public function notice(string $message, array $data = [])
    {
        return $this->log($message, LogLevel::NOTICE, $data);
    }

    /**
     * Log a message at the warning level.
     *
     * @param string $message  the message to log.
     * @param array $data      the context data, if any.
     */
    public function warning(string $message, array $data = [])
    {
        return $this->log($message, LogLevel::WARNING, $data);
    }

    /**
     * Log a message at the error level.
     *
     * @param string $message  the message to log.
     * @param array $data      the context data, if any.
     */
    public function error(string $message, array $data = [])
    {
        return $this->log($message, LogLevel::ERROR, $data);
    }

    /**
     * Log a message at the critical level.
     *
     * @param string $message  the message to log.
     * @param array $data      the context data, if any.
     */
    public function critical(string $message, array $data = [])
    {
        return $this->log($message, LogLevel::CRITICAL, $data);
    }

    /**
     * Log a message at the alert level.
     *
     * @param string $message  the message to log.
     * @param array $data      the context data, if any.
     */
    public function alert(string $message, array $data = [])
    {
        return $this->log($message, LogLevel::ALERT, $data);
    }

    /**
     * Log a message at the emergency level.
     *
     * @param string $message  the message to log.
     * @param array $data      the context data, if any.
     */
    public function emergency(string $message, array $data = [])
    {
        return $this->log($message, LogLevel::EMERGENCY, $data);
    }

    /**
     * An alias for warning()
     *
     * @param string $message  the message to log.
     * @param array $data      the context data, if any.
     */
    public function warn(string $message, array $data = [])
    {
        return $this->warning($message, $data);
    }

    /**
     * An alias for critical()
     *
     * @param string $message  the message to log.
     * @param array $data      the context data, if any.
     */
    public function crit(string $message, array $data = [])
    {
        return $this->critical($message, $data);
    }

    /**
     * An alias for emergency()
     *
     * @param string $message  the message to log.
     * @param array $data      the context data, if any.
     */
    public function emerg(string $message, array $data = [])
    {
        return $this->emergency($message, $data);
    }

    /**
     * Log the error and re-throw the exception in one call.
     *
     * @param string|Throwable $exception  the exception itself or an exception
     *                                     class.
     * @param array $context               the context data, if any.
     * @param string $file                 the file where the exception occurred
     * @param int $line                    the line where the exception occurred
     * @param string $logLevel             the logging level for the exception.
     *                                     see Psr\Log\LogLevel
     *
     * @throws Throwable
     */
    public function exception(
        $exception,
        array $context = [],
        string $file = '',
        int $line = 0,
        string $logLevel = LogLevel::ERROR
    ) {
        $exception = ExceptionHandler::makeThrowable(
            $exception,
            $context,
            $file,
            $line
        );

        $prefix    = ExceptionHandler::getMessagePrefixFileAndLine(
            $file,
            $line
        );

        $message   = $exception->getMessage();
        $message   = sprintf('%s %s', $prefix, $message);

        $this->log($message, $logLevel);

        throw $exception;
    }
}
