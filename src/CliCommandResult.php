<?php

namespace Miravel;

class CliCommandResult
{
    /**
     * @var array
     */
    private $output;

    /**
     * @var int
     */
    private $return;

    public function __construct(array $output, int $return)
    {
        $this->output = $output;
        $this->return = $return;
    }

    /**
     * @return int
     */
    public function getReturnCode(): int
    {
        return $this->return;
    }

    /**
     * @return array
     */
    public function getOutput(): array
    {
        return $this->output;
    }

    public function isSuccessful(): bool
    {
        return 0 == $this->return;
    }

    public function getLastOutputLine()
    {
        return end($this->output);
    }
}
