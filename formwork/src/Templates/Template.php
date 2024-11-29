<?php

namespace Formwork\Templates;

use Closure;
use Formwork\Cms\Site;
use Formwork\Schemes\Scheme;
use Formwork\Utils\Constraint;
use Formwork\Utils\FileSystem;
use Formwork\View\Exceptions\RenderingException;
use Formwork\View\Renderer;
use Formwork\View\ViewFactory;
use Stringable;

class Template implements Stringable
{
    /**
     * Create a new Template instance
     *
     * @param array<string, mixed>   $vars
     * @param array<string, Closure> $methods
     */
    public function __construct(
        protected string $name,
        protected array $vars,
        protected string $path,
        protected array $methods,
        protected Scheme $scheme,
        protected Site $site,
        protected ViewFactory $viewFactory
    ) {
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function scheme(): Scheme
    {
        return $this->scheme;
    }

    public function title(): string
    {
        return $this->scheme->title();
    }

    public function path(): string
    {
        return $this->path;
    }

    /**
     * Render template
     *
     * @param array<string, mixed> $vars
     */
    public function render(array $vars = []): string
    {
        if (!Constraint::hasKeys($vars, ['page'])) {
            throw new RenderingException('Missing "page" variable');
        }

        $page = $vars['page'];

        $isCurrentPage = $page->isCurrent();

        $controllerVars = $this->loadController($vars) ?? [];

        // Render correct page if the controller has changed the current one
        if ($isCurrentPage && !$page->isCurrent()) {
            if ($this->site->currentPage() === null) {
                throw new RenderingException('Invalid current page');
            }
            return $this->site->currentPage()->render();
        }

        $view = $this->viewFactory->make(
            $this->name,
            [...$this->vars, ...$vars, ...$controllerVars],
            $this->path,
            [...$this->methods]
        );

        return $view->render();
    }

    /**
     * Load template controller if exists
     *
     * @param array<string, mixed> $vars
     *
     * @return array<string, mixed>|null
     */
    protected function loadController(array $vars = []): ?array
    {
        $controllerFile = FileSystem::joinPaths($this->path, 'controllers', $this->name . '.php');

        if (FileSystem::exists($controllerFile)) {
            return (array) Renderer::load($controllerFile, [...$this->vars, ...$vars], $this);
        }

        return null;
    }
}
