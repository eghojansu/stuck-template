<?php

declare(strict_types=1);

namespace Stuck\Template;

class Template
{
    private $sections = array();
    private $replaces = array();

    /** @var self */
    private $parent;

    /** @var self */
    private $child;

    /** @var array */
    private $currentSection;

    public function __construct(
        private Manager $template,
        private string $filepath,
        private string $view,
        private string $directory,
        private ?array $data,
    ) {}

    public function render(array $data = null): string
    {
        return ($this->createLoader())($data);
    }

    public function __call($name, $arguments)
    {
        return $this->template->$name(...$arguments);
    }

    protected function extends(string $view, array $data = null): void
    {
        $this->parent = $this->template->getTemplate($view, $data);
        $this->parent->withChild($this);
    }

    protected function load(string $view, array $data = null): string
    {
        return $this->template->getTemplate($view, $data)->render();
    }

    protected function parent(): string
    {
        if (!$this->parent) {
            throw new \RuntimeException('No parent defined');
        }

        if (!$this->currentSection) {
            throw new \RuntimeException('No section defined');
        }

        $this->parent->pushReplace($token = $this->template->random());

        return $token;
    }

    protected function pushReplace(string $token): void
    {
        $this->replaces[] = $token;
    }

    protected function withChild(self $child): void
    {
        $this->child = $child;
    }

    protected function withSections(array $sections): void
    {
        $this->sections = $sections;
    }

    protected function getTree(array $section): array
    {
        $loop = $section;
        $tree = array();

        do {
            $tree[] = $loop['name'];
            $loop = $loop['parent'] ?? null;
        } while ($loop);

        return array_reverse($tree);
    }

    protected function getSection(string ...$tree): ?array
    {
        $check = $this->sections;
        $found = true;

        foreach ($tree as $name) {
            if (isset($check[$name])) {
                $check = $check[$name];
            } else {
                $found = false;

                break;
            }
        }

        return isset($name) && $found ? $check : null;
    }

    protected function checkSection(array|string $name, string &$content = null): bool
    {
        $tree = (array) $name;
        $section = $this->child?->getSection(...$tree) ?? $this->getSection(...$tree);
        $content = $section['content'] ?? null;

        return !!$section;
    }

    protected function show(array|string $name, string $default = null): ?string
    {
        return $this->checkSection($name, $content) ? $content : $default;
    }

    protected function section(string|bool $name = null, string $content = null): void
    {
        if ($name && is_string($name)) {
            $section = array('open' => true);

            if ($this->currentSection) {
                $this->currentSection['sections'][$name] = compact('name') + $section + array('parent' => &$this->currentSection);
                $this->currentSection = &$this->currentSection['sections'][$name];
            } else {
                $this->sections[$name] = compact('name') + $section;
                $this->currentSection = &$this->sections[$name];
            }

            if (null === $content) {
                ob_start();

                return;
            }

            $this->commitCurrentSection($content);

            return;
        }

        if (!$this->currentSection) {
            throw new \RuntimeException('No section defined');
        }

        $content = ob_get_clean();

        $this->commitCurrentSection($content, true === $name);
    }

    protected function commitCurrentSection(string $content, bool $out = false): void
    {
        if ($childSection = $this->child?->getSection(...$this->getTree($this->currentSection))) {
            if ($this->replaces) {
                $content = str_replace($this->replaces, $content, $childSection['content']);
            } else {
                $content = $childSection['content'];
            }
        }

        $this->currentSection['content'] = $content;
        $this->currentSection['open'] = false;

        if (isset($this->currentSection['parent'])) {
            $parent = &$this->currentSection['parent'];
            unset($this->currentSection['parent']);

            $this->currentSection = &$parent;
        } else {
            unset($this->currentSection);

            $this->currentSection = null;
        }

        if ($out) {
            echo $content;
        }
    }

    protected function createLoader(): \Closure
    {
        return function (array $data = null): string {
            extract(($this->data ?? array()) + ($data ?? array()) + $this->template->getGlobals());

            $___level = ob_get_level();

            try {
                ob_start();
                require $this->filepath;
                $content = ob_get_clean();

                if ($this->parent) {
                    $this->parent->withSections($this->sections);

                    return $this->parent->render($this->data);
                }

                return $content;
            } catch (\Throwable $error) {
                while (ob_get_level() > $___level) {
                    ob_end_clean();
                }

                throw new \RuntimeException(sprintf("Unable to render view: '%s' (%s)", $this->view, $error->getMessage()), 0, $error);
            }
        };
    }
}