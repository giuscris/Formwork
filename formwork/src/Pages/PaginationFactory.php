<?php

namespace Formwork\Pages;

use Formwork\Cms\App;
use Formwork\Router\Router;

class PaginationFactory
{
    public function __construct(protected App $app, protected Router $router)
    {
    }

    public function make(PageCollection $pageCollection, int $length): Pagination
    {
        return new Pagination($pageCollection, $length, $this->app->site(), $this->router);
    }
}
