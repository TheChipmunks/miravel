<?php

namespace Miravel\Traits;

trait HasOptions
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
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        if (empty($this->options)) {
            $this->options = (array)$this->defaultOptions;
        }

        $this->options = array_merge($this->options, $options);
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
        return array_get($this->options, $optionName, $default);
    }
}
