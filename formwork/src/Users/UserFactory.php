<?php

namespace Formwork\Users;

use Formwork\Services\Container;

class UserFactory
{
    public function __construct(
        protected Container $container,
    ) {
    }

    /**
     * Create a new User instance
     *
     * @param array<string, mixed> $data
     */
    public function make(array $data, Role $role): User
    {
        return $this->container->build(User::class, compact('data', 'role'));
    }
}
