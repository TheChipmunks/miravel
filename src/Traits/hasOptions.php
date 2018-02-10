<?php

namespace Miravel\Traits;

trait hasOptions
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $defaultOptions = [];

    /**
     * @return array
     */
    public function getDefaultOptions(): array
    {
        return $this->defaultOptions;
    }

    /**
     * @param array $defaultOptions
     */
    public function setDefaultOptions(array $defaultOptions)
    {
        $this->defaultOptions = $defaultOptions;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return array_merge_recursive(
            (array)$this->defaultOptions,
            (array)$this->options
        );
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param string $optionName
     *
     * @param mixed $default
     *
     * @return mixed
     */
    public function getOption(string $optionName, $default = null)
    {
        return $this->options[$optionName] ??
               $this->defaultOptions[$optionName] ??
               $default;
    }
}
