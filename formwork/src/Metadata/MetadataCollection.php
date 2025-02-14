<?php

namespace Formwork\Metadata;

use Formwork\Data\AbstractCollection;

class MetadataCollection extends AbstractCollection
{
    protected bool $associative = true;

    protected ?string $dataType = Metadata::class;

    protected bool $mutable = true;

    /**
     * @param array<Metadata> $data
     */
    public function __construct(array $data)
    {
        parent::__construct();
        $this->setMultiple($data);
    }

    /**
     * Set a metadata
     *
     * @param string $value
     */
    public function set(string $key, $value): void
    {
        $this->data[$key] = new Metadata($key, $value);
    }
}
