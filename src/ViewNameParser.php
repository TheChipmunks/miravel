<?php

namespace Miravel;

use Illuminate\View\FileViewFinder as LaravelFileViewFinder;
use InvalidArgumentException;

class ViewNameParser
{
    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $raw;

    /**
     * @var array
     */
    protected $parts = [];

    /**
     * ViewNameParser constructor.
     *
     * @param string $viewName
     */
    public function __construct(string $viewName)
    {
        $this->raw = $viewName;

        $this->parse($viewName);
    }

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return mixed
     */
    public function getParts()
    {
        return $this->parts;
    }

    public function getPart(int $index)
    {
        return $this->parts[$index] ?? null;
    }

    public function getTheme()
    {
        return $this->countParts() ? $this->parts[0] : null;
    }

    public function getType()
    {
        return ($this->countParts() > 2) ? $this->parts[1] : null;
    }

    public function getName()
    {
        $count = $this->countParts();
        $last  = $count - 1;

        if ($count == 2 || $count == 3) {
            return $this->parts[$last];
        }

        if ($count > 3) {
            return implode('.', array_slice($this->parts, 2));
        }
    }

    public function countParts()
    {
        return count($this->parts);
    }

    public function getNameWithoutNamespace(string $delimiter = '.')
    {
        return implode($delimiter, $this->parts);
    }

    /**
     * @return string
     */
    public function getRaw(): string
    {
        return $this->raw;
    }

    public function isMiravelNamespacedView()
    {
        $count = count($this->parts);

        return ($this->namespace == 'miravel') && ($count >= 3);
    }

    public function isMiravelLayout()
    {
        return $this->isMiravelNamespacedView() &&
               'layouts' == $this->getType();
    }

    public function isMiravelElement()
    {
        return $this->isMiravelNamespacedView() &&
               'elements' == $this->getType();
    }

    protected function parse(string $viewName)
    {
        $namespaceDelimiter = LaravelFileViewFinder::HINT_PATH_DELIMITER;

        $segments = explode($namespaceDelimiter, $viewName);

        switch (count($segments)) {
            case 1:
                $this->parts = $this->split($viewName);
                break;
            case 2:
                $this->namespace = $segments[0];
                $this->parts = $this->split($segments[1]);
                break;
            default:
                throw new InvalidArgumentException(
                    "View [$viewName] has an invalid name."
                );
        }
    }

    protected function split(string $viewName)
    {
        return preg_split('/[.\/\\\\]/', $viewName);
    }
}
