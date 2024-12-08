<?php

namespace Formwork\Images\Transform;

use Formwork\Images\ImageInfo;
use Formwork\Utils\Constraint;
use GdImage;
use InvalidArgumentException;

class Brightness extends AbstractTransform
{
    final public function __construct(
        protected int $amount,
    ) {
        if (!Constraint::isInIntegerRange($amount, -255, 255)) {
            throw new InvalidArgumentException(sprintf('$amount value must be in range -255-+255, %d given', $amount));
        }
    }

    public static function fromArray(array $data): static
    {
        return new static($data['amount']);
    }

    public function apply(GdImage $gdImage, ImageInfo $imageInfo): GdImage
    {
        imagefilter($gdImage, IMG_FILTER_BRIGHTNESS, $this->amount);
        return $gdImage;
    }
}
