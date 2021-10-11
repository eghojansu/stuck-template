<?php

declare(strict_types=1);

namespace Stuck\Template;

class Chainable
{
    /** @var array */
    private $arguments;

    public function __construct(
        private Manager $manager,
        ...$arguments,
    ) {
        $this->arguments = $arguments;
    }

    public function __get($name)
    {
        if ('value' === $name) {
            return reset($this->arguments);
        }

        throw new \OutOfBoundsException(sprintf("Property not exists: '%s'", $name));
    }

    public function __call($name, $arguments)
    {
        $this->arguments = array($this->manager->$name(...$this->arguments, ...$arguments));

        return $this;
    }

    public function __toString()
    {
        if (!$this->arguments) {
            return '';
        }

        if (is_array($this->arguments[0]) || $this->arguments[0] instanceof \JsonSerializable) {
            return json_encode($this->arguments[0]);
        }

        return $this->manager->esc((string) $this->arguments[0]);
    }
}