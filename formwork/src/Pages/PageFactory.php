<?php

namespace Formwork\Pages;

use Formwork\Services\Container;

class PageFactory
{
    public function __construct(
        protected Container $container,
    ) {
    }

    /**
     * Create a new Page instance
     *
     * @param array<string, mixed> $data
     */
    public function make(array $data = []): Page
    {
        return $this->container->build(Page::class, ['data' => $data]);
    }
}
