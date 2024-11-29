<?php

namespace Formwork\Fields\Layout;

use Formwork\Translations\Translation;

class Layout
{
    /**
     * Layout type
     */
    protected string $type;

    /**
     * Layout sections collection
     */
    protected SectionCollection $sections;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data, Translation $translation)
    {
        $this->type = $data['type'];
        $this->sections = new SectionCollection($data['sections'] ?? [], $translation);
    }

    /** Get layout type
     *
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * Get layout sections
     */
    public function sections(): SectionCollection
    {
        return $this->sections;
    }
}
