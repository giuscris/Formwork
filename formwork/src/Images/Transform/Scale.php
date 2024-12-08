<?php

namespace Formwork\Images\Transform;

use Formwork\Images\ImageInfo;
use GdImage;
use InvalidArgumentException;
use UnexpectedValueException;

class Scale extends AbstractTransform
{
    final public function __construct(
        protected float $factor,
    ) {
        if ($factor <= 0) {
            throw new InvalidArgumentException('Scale factor must be greater than 0');
        }
    }

    public static function fromArray(array $data): static
    {
        return new static($data['factor']);
    }

    public function apply(GdImage $gdImage, ImageInfo $imageInfo): GdImage
    {
        $width = (int) floor(imagesx($gdImage) * $this->factor);
        $height = (int) floor(imagesy($gdImage) * $this->factor);

        if ($width <= 0) {
            throw new UnexpectedValueException('Unexpected non-positive calculated width');
        }

        if ($height <= 0) {
            throw new UnexpectedValueException('Unexpected non-positive calculated height');
        }

        $resize = new Resize($width, $height);
        return $resize->apply($gdImage, $imageInfo);
    }
}
