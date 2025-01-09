<?php

namespace Formwork\Pages;

final class PageCollectionFactory
{
    public function __construct(
        private PaginationFactory $paginationFactory,
    ) {}

    /**
     * Create a new PageCollection instance
     *
     * @param array<int|string, mixed> $data
     */
    public function make(array $data): PageCollection
    {
        return new PageCollection($data, $this->paginationFactory);
    }
}
