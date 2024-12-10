<?php

namespace Formwork\Schemes;

use Formwork\Services\Container;

final class SchemeFactory
{
    public function __construct(
        private Container $container,
    ) {
    }

    /**
     * Create a new Scheme instance
     *
     * @param array<string, mixed> $data
     */
    public function make(string $id, array $data = []): Scheme
    {
        return $this->container->build(Scheme::class, compact('id', 'data'));
    }
}
