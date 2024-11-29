<?php

namespace Formwork\Schemes;

use Formwork\Services\Container;

class SchemeFactory
{
    public function __construct(protected Container $container)
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function make(string $id, array $data = []): Scheme
    {
        return $this->container->build(Scheme::class, compact('id', 'data'));
    }
}
