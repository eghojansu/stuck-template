<?php

declare(strict_types=1);

namespace Stuck\Template;

class Manager
{
    private $directories = array();
    private $extensions = array();
    private $callbacks = array();
    private $globals = array();

    public function __construct(
        string|array $directories = './templates',
        string|array $extensions = 'php',
    ) {
        $this->directories = array_map('self::fixSlashes', self::split($directories));
        $this->extensions = self::split($extensions);
    }

    public function render(string $view, array $data = null): string
    {
        return $this->getTemplate($view, $data)->render();
    }

    public function getTemplate(string $view, array $data = null): Template
    {
        list($filepath, $directory, $resolved) = $this->getTemplateInfo($view) ?? array(null, null, null);

        if (!$filepath) {
            throw new \RuntimeException(sprintf("Template not found: '%s'", $view));
        }

        return new Template($this, $filepath, $resolved, $directory, $data);
    }

    public function getTemplateInfo(string $view): ?array
    {
        $resolved = ltrim(self::fixSlashes($view), '/');

        foreach ($this->directories as $directory) {
            if (
                is_file($filepath = $directory . '/' . $resolved)
                || ($filepath = $this->resolveTemplateFile($directory, $resolved))
            ) {
                return array(realpath($filepath), $directory, $resolved);
            }
        }

        return null;
    }

    public function getGlobals(): array
    {
        return $this->globals;
    }

    public function setGlobals(array $globals): static
    {
        $this->globals = $globals;

        return $this;
    }

    public function addGlobal(string $name, $value): static
    {
        $this->globals[$name] = $value;

        return $this;
    }

    public function addCallback(string $name, callable $callback, bool $withThis = false): static
    {
        $this->callbacks[strtolower($name)] = $withThis ? function (...$arguments) use ($callback) {
            return $callback($this, ...$arguments);
        } : $callback;

        return $this;
    }

    public function escape(string $str): string
    {
        return htmlspecialchars($str);
    }

    public function esc(string $str): string
    {
        return $this->escape($str);
    }

    public function e(string $str): string
    {
        return $this->escape($str);
    }

    public function escapeAll(array $parts): array
    {
        return array_map(array($this, 'escape'), $parts);
    }

    public function chain(...$arguments): Chainable
    {
        return new Chainable($this, ...$arguments);
    }

    public function __call($name, $arguments)
    {
        $call = $this->callbacks[$name] ?? $this->callbacks[strtolower($name)] ?? null;

        if ($call) {
            return $call(...$arguments);
        }

        if (function_exists($name)) {
            return $name(...$arguments);
        }

        throw new \BadMethodCallException(sprintf("Unable to proxy call method: '%s'", $name));
    }

    protected function resolveTemplateFile(string $directory, string $view): ?string
    {
        $resolved = false === strpos($view, '..') ? strtr($view, '.', '/') : $view;

        foreach ($this->extensions as $extension) {
            if (is_file($filepath = $directory . '/' . $resolved . '.' . $extension)) {
                return $filepath;
            }
        }

        return null;
    }

    public static function fixSlashes(string $text): string
    {
        return rtrim(strtr($text, '\\', '/'), '/');
    }

    public static function split(string|array $argument): array
    {
        return is_string($argument) ? preg_split('/[,;|]/', $argument) : $argument;
    }

    public static function random(int $length = 7): string
    {
        return bin2hex(random_bytes($length));
    }
}