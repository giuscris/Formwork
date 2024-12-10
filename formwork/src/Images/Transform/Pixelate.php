<?php

namespace Formwork\Images\Transform;

use Formwork\Images\ImageInfo;
use GdImage;

final class Pixelate extends AbstractTransform
{
    public function __construct(
        private int $amount,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self($data['amount']);
    }

    public function apply(GdImage $gdImage, ImageInfo $imageInfo): GdImage
    {
        imagefilter($gdImage, IMG_FILTER_PIXELATE, $this->amount);
        return $gdImage;
    }
}
