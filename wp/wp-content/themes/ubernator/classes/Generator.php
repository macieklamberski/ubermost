<?php
namespace Ubernator;

use Imagine\Imagick\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Imagick\Image;

/**
 * Utility class for creating merged lettering images.
 */
class Generator {

    /**
     * Instance of Imagine\Imagick\Imagine.
     */
    protected $imagine;

    /**
     * Constructor used for initializing Imagine library as it's referred to
     * multiple times.
     */
    public function __construct() {
        $this->imagine = new Imagine();
    }

    /**
     * Generate final image from given colors, lettering shape and size.
     */
    public function generate($lettering_file, $foreground_file, $background_file, $width, $height, $scale) {
        $lettering  = $this->prepareLettering($lettering_file, $width, $height, $scale);
        $mask       = $this->prepareMask($lettering, $width, $height);
        $foreground = $this->prepareForeground($foreground_file, $mask, $width, $height);

        return $this->compileImage($background_file, $foreground, $width, $height);
    }

    /**
     * Load and scale down the shape of lettering.
     */
    protected function prepareLettering($lettering_file, $width, $height, $scale) {
        $width    *= $scale;
        $height   *= $scale;

        if ($width < $height) {
            $height = $width;
        } else {
            $width = $height;
        }

        $lettering = $this->imagine->open($lettering_file);
        $lettering = $this->resizeImage($lettering, $width, $height);

        return $lettering;
    }

    /**
     * Create black/white mask from the lettering shape. It will be later used
     * to cut out needed parts of the foreground texture.
     */
    protected function prepareMask(Image $lettering, $width, $height) {
        $mask = $this->imagine->create(new Box($width, $height));
        $mask = $this->resizeImage($mask, $width, $height, true);
        $mask->paste($lettering, new Point(
            ($width - $lettering->getSize()->getWidth()) / 2,
            ($height - $lettering->getSize()->getHeight()) / 2
        ));

        return $mask;
    }

    /**
     * Load and cut out foreground texture using lettering shape as a mask.
     */
    protected function prepareForeground($foreground_file, Image $mask, $width, $height) {
        $foreground = $this->imagine->open($foreground_file);
        $foreground = $this->resizeImage($foreground, $width, $height, true);
        $foreground->applyMask($mask);

        return $foreground;
    }

    /**
     * Having all parts of the output image generated, merge them together.
     */
    protected function compileImage($background_file, Image $foreground, $width, $height) {
        $image = $this->imagine->open($background_file);
        $image = $this->resizeImage($image, $width, $height, true);
        $image->paste($foreground, new Point(0, 0));

        return $image;
    }

    /**
     * If image exceeds the size of generated image - scale it down and crop.
     */
    protected function resizeImage(Image $image, $width, $height, $crop = false) {
        $new     = new Box($width, $height);
        $current = $image->getSize();
        $ratio   = max([
            $new->getWidth() / $current->getWidth(),
            $new->getHeight() / $current->getHeight()
        ]);

        if ($new->contains($current)) {
            return $image;
        }

        if (!$current->contains($new)) {
            $new = new Box(
                min($current->getWidth(), $new->getWidth()),
                min($current->getHeight(), $new->getHeight())
            );
        } else {
            $current = $image->getSize()->scale($ratio);
            $image->resize($current);
        }
        $image->crop(new Point(
            max(0, round(($current->getWidth() - $new->getWidth()) / 2)),
            max(0, round(($current->getHeight() - $new->getHeight()) / 2))
        ), $new);

        return $image;
    }
}
