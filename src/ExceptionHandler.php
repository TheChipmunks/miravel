<?php

namespace Miravel;

use Miravel\Exceptions\BaseException;
use InvalidArgumentException;
use Exception;
use Throwable;

/**
 * Class ExceptionHandler
 *
 * Miravel's exception handler.
 *
 * @package Miravel
 */
class ExceptionHandler
{
    /**
     * Constructs a Throwable from mixed input (either throwable or string). If
     * file and line information are provided, and it's possible to inject them
     * (which is the case with Miravel\Exceptions\BaseException), do this.
     *
     * @param string|Throwable $exception  a string or a Throwable.
     * @param array $context               the context variables to fill into
     *                                     the message, if possible/necessary.
     * @param string $file                 the file where the exception occurred
     * @param int $line                    the line where the exception occurred
     *
     * @return Throwable
     */
    public static function makeThrowable(
        $exception,
        $context = [],
        string $file = '',
        int $line = 0
    ): Throwable {
        $exception = static::convertToException($exception, $context);

        if (is_callable([$exception, 'setFile'])) {
            $exception->setFile($file);
        }

        if (is_callable([$exception, 'setLine'])) {
            $exception->setLine($line);
        }

        return $exception;
    }

    /**
     * Build the line like "/path/to/file:234"
     *
     * @param string $file  the file path
     * @param int $line     the line number
     *
     * @return string
     */
    public static function getMessagePrefixFileAndLine(
        string $file,
        int $line = 0
    ): string {
        $prefix = $file;
        if ($line) {
            $prefix .= ":$line";
        }

        return $prefix;
    }

    /**
     * Given an Exception, return it as is; given a class name that extends from
     * BaseException, instantiate and return that class; given a string, make up
     * a simple Exception object.
     *
     * @param string|Throwable $exception  A string or a Throwable.
     * @param array $context               The context variables to fill into.
     *                                     the message, if possible.
     *
     * @return Throwable
     */
    protected static function convertToException(
        $exception,
        array $context = []
    ): Throwable {
        if ($exception instanceof Throwable) {
            return $exception;
        }

        if (
            is_string($exception) &&
            is_subclass_of($exception, BaseException::class)
        ) {
            return new $exception($context);
        }

        if (is_string($exception)) {
            $message = Utilities::fillMoustachePlaceholders(
                $exception,
                $context
            );

            return new Exception($message);
        }

        throw new InvalidArgumentException(
            sprintf(
                'Could not convert %s to Exception',
                gettype($exception)
            )
        );
    }
}
