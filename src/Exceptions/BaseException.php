<?php

namespace Miravel\Exceptions;

use Exception;
use Miravel\Utilities;
use Throwable;

class BaseException extends Exception
{
    /**
     * @var string the message of the exception.
     */
    protected $message = 'Miravel Exception';

    /**
     * Array of substitutions to use in the message.
     *
     * @var array
     */
    protected $context = [];

    /**
     * BaseException constructor.
     *
     * Register context variables and build the message.
     *
     * @param null|array $context
     * @param int $code
     * @param null|Throwable $previous
     */
    public function __construct(
        array $context = [],
        $code = 0,
        Throwable $previous = null
    ) {
        $this->context = $context;

        $message = $this->buildMessage();

        parent::__construct($message, $code, $previous);
    }

    /**
     * A getter for $this->context
     *
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set the file where the Exception actually occurred. Mainly for exceptions
     * wrapped by Miravel::exception() (which also logs them before re-throwing)
     *
     * @param string $file
     */
    public function setFile(string $file)
    {
        $this->file = $file;
    }

    /**
     * Set the line where the Exception actually occurred. Mainly for exceptions
     * wrapped by Miravel::exception() (which also logs them before re-throwing)
     *
     * @param int $line
     */
    public function setLine(int $line)
    {
        $this->line = $line;
    }

    /**
     * Take the message pattern that the exception comes with and replace all
     * moustache variables like {var} with the value from $this->context, if any
     *
     * @return string
     */
    protected function buildMessage()
    {
        return Utilities::fillMoustachePlaceholders(
            $this->message,
            $this->context
        );
    }
}
