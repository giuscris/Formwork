<?php

namespace Formwork\Pages\Traits;

use Formwork\Pages\Page;
use Formwork\Utils\Date;
use UnexpectedValueException;

trait PageStatus
{
    /**
     * Page data
     *
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * Page status
     */
    protected string $status;

    /**
     * Get page status
     */
    public function status(): string
    {
        if (isset($this->status)) {
            return $this->status;
        }

        /**
         * @var bool
         */
        $published = $this->get('published', true);

        $now = time();

        if ($publishDate = ($this->data['publishDate'] ?? null)) {
            if (!is_string($publishDate)) {
                throw new UnexpectedValueException('Invalid publish date');
            }

            $published = $published && Date::toTimestamp($publishDate) < $now;
        }

        if ($unpublishDate = ($this->data['unpublishDate'] ?? null)) {
            if (!is_string($unpublishDate)) {
                throw new UnexpectedValueException('Invalid unpublish date');
            }

            $published = $published && Date::toTimestamp($unpublishDate) > $now;
        }

        $this->status = match (true) {
            $published  => Page::PAGE_STATUS_PUBLISHED,
            !$published => Page::PAGE_STATUS_NOT_PUBLISHED
        };

        return $this->status;
    }
}
