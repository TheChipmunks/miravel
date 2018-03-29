<?php

namespace Miravel\Factories;

use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Miravel\Utilities;
use Miravel;
use Log;

/**
 * Class LoggerFactory
 *
 * A class that creates instances of PSR3 compatible loggers.
 *
 * @package Miravel
 */
class LoggerFactory
{
    /**
     * Create a logger according to miravel configuration.
     *
     * @return LoggerInterface
     */
    public static function make(): LoggerInterface
    {
        $config         = static::getConfig();
        $logSetting     = $config['separate_log'] ?? false;

        $logger         = null;

        // see if its a falsy value
        if (!$logSetting) {
            $logger = static::getLaravelLogger();
        }

        // see if its a string pointing to a file location
        if (!$logger && is_string($logSetting)) {
            $logger = static::makeSpecificFileLogger($logSetting);
        }

        // logSetting is not falsy yet nor a string representing a file
        // use separate file with the default name
        if (!$logger) {
            $logger = static::makeDefaultFileLogger();
        }

        return $logger;
    }

    /**
     * Get a clone of Laravel default logger (monolog), with its logging level
     * set to one specified in miravel config
     *
     * @return LoggerInterface
     */
    public static function getLaravelLogger(): LoggerInterface
    {
        $config = static::getConfig();
        $name   = $config['name'] ?? 'miravel';
        $level  = $config['level'] ?? null;

        $log    = app()->make('log');

        // try to extract Monolog so we can set name and logging level
        $monolog = static::extractMonolog($log);

        if (!is_subclass_of($monolog, MonologLogger::class)) {
            // but don't get too much upset if we can't
            // hopefully Laravel will always have a LoggerInterface in its
            // service container
            return $log;
        }

        // now we have a Monolog so we can set logging level and name
        $monolog = $monolog->withName($name);
        if ($level) {
            static::setLoggingLevel($monolog, $level);
        }

        return $monolog;
    }

    /**
     * Get a file logger set to log to a specific file.
     *
     * @param $file                  the path to file, absolute or relative to
     *                               base_path.
     *
     * @return LoggerInterface|void  if the file cannot be written, returns null
     */
    public static function makeSpecificFileLogger($file)
    {
        $trimmed  = trim($file);
        if (!empty($trimmed)) {
            $absolute = Utilities::makeAbsolutePath($trimmed);
            if (Utilities::isWritable($absolute)) {
                return static::makeFileLogger($absolute);
            }
        }
    }

    /**
     * Get a file logger set to log to the default file (miravel.log).
     *
     * @return LoggerInterface
     */
    public static function makeDefaultFileLogger(): LoggerInterface
    {
        $defaultLogFile = storage_path('logs/miravel.log');
        $logger         = static::makeFileLogger($defaultLogFile);

        return $logger;
    }

    /**
     * Instantiage a logger which will log to a specified file. Does not check
     * file existence or writability. All other settings (rotation, permissions)
     * are taken from the miravel config.
     *
     * @param $file             the absolute path to file.
     *
     * @return LoggerInterface
     */
    public static function makeFileLogger($file): LoggerInterface
    {
        $config         = (array)config('miravel.log', []);
        $config['file'] = $file;

        $logger = new MonologLogger($config['logger_name']);

        $handler = static::getFileHandler($config);

        // add a formatter to convert log messsages to default format
        $formatter = static::getLineFormatter($config);

        // replaces {var} variables with context values in messages
        $processor = new PsrLogMessageProcessor();

        $handler->setFormatter($formatter);
        $handler->pushProcessor($processor);

        $logger->pushHandler($handler);

        return $logger;
    }

    /**
     * Gets logging handler that will write messages to a file.
     *
     * @param array $config   the configuration array holding values such as
     *                        rotation, permissions, logging level etc.
     *
     * @return StreamHandler  the handler that can be given to logger.
     */
    protected static function getFileHandler(array $config): StreamHandler
    {
        $handler = ('daily' == $config['rotation']) ?
            new RotatingFileHandler(
                $config['file'],
                $config['maxfiles'],
                $config['level'],
                true,
                $config['permissions']
            ) :
            new StreamHandler(
                $config['file'],
                $config['level'],
                true,
                $config['permissions']
            );

        return $handler;
    }

    /**
     * Get the line formatter. Line format and date format can be set in miravel
     * config. For more details, see
     * https://github.com/Seldaek/monolog/blob/master/doc/01-usage.md#customizing-the-log-format
     *
     * @param array $config   the configuration array containing "format" and
     *                        "date_format" options
     *
     * @return LineFormatter
     */
    protected static function getLineFormatter(array $config): LineFormatter
    {
        $formatter = new LineFormatter(
            $config['format'],
            $config['date_format'],
            true,
            true
        );

        return $formatter;
    }

    /**
     * Get the logging section of miravel configuration as an array.
     *
     * @return array
     */
    protected static function getConfig(): array
    {
        return (array)config('miravel.log');
    }

    /**
     * Sets the specified logging level on all handlers of a LoggerInterface
     * instance.
     *
     * @param LoggerInterface $logger  an instance of LoggerInterface to operate
     *                                 on.
     * @param string $level            the logging level, see Psr\Log\LogLevel
     */
    protected static function setLoggingLevel(
        MonologLogger $logger,
        string $level
    ) {
        $handlers = $logger->getHandlers();
        foreach ($handlers as $handler) {
            if (is_callable([$handler, 'setLevel'])) {
                $handler->setLevel($level);
            }
        }
    }

    /**
     * Trying to get the underlying Monolog instance from whatever logger is
     * available in Laravel app, so we can set the channel name and log level
     * in the known way.
     *
     * @param LoggerInterface $log
     *
     * @return MonologLogger|void
     */
    protected static function extractMonolog(LoggerInterface $log)
    {
        // laravel 5.5 and below
        if (is_callable([$log, 'getMonolog'])) {
            return Log::getMonolog();
        }

        // laravel 5.6 and above
        if (is_callable([$log, 'driver'])) {
            $driver = $log->driver();
            if (is_callable([$driver, 'getLogger'])) {
                $logger = $driver->getLogger();
                if (is_subclass_of($logger, MonologLogger::class)) {
                    return $logger;
                }
            }
        }
    }

}


