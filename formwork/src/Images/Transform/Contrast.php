<?php

namespace Formwork\Images\Transform;

use Formwork\Images\ImageInfo;
use Formwork\Utils\Constraint;
use GdImage;
use InvalidArgumentException;

final class Contrast extends AbstractTransform
{
    public function __construct(
        private int $amount,
    ) {
        if (!Constraint::isInIntegerRange($amount, -100, 100)) {
            throw new InvalidArgumentException(sprintf('$amount value must be in range -100-+100, %d given', $amount));
        }
    }

    public static function fromArray(array $data): self
    {
        return new self($data['amount']);
    }

    public function apply(GdImage $gdImage, ImageInfo $imageInfo): GdImage
    {
        // For GD -100 = max contrast, 100 = min contrast; we change $amount sign for a more predictable behavior
        imagefilter($gdImage, IMG_FILTER_CONTRAST, -$this->amount);
        return $gdImage;
    }
}
