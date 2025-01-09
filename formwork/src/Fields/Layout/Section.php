<?php

namespace Formwork\Fields\Layout;

use Formwork\Data\Traits\DataGetter;
use Formwork\Translations\Translation;
use Formwork\Utils\Str;

class Section
{
    use DataGetter;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        array $data,
        protected Translation $translation,
    ) {
        $this->data = $data;
    }

    /**
     * Get a value by key and return whether it is equal to boolean `true`
     */
    public function is(string $key, bool $default = false): bool
    {
        return $this->get($key, $default) === true;
    }

    /**
     * Get field label
     */
    public function label(): string
    {
        return Str::interpolate($this->get('label', ''), fn($key) => $this->translation->translate($key));
    }
}
