<?php

namespace Formwork\Pages;

class PageCollectionFactory
{
    public function __construct(protected PaginationFactory $paginationFactory)
    {
    }

    /**
     * @param array<int|string, mixed> $data
     */
    public function make(array $data): PageCollection
    {
        return new PageCollection($data, $this->paginationFactory);
    }
}
