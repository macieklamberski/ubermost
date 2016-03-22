<?php
namespace UbermostCreate;

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
  public function generate($parameters) {
    $lettering  = $this->prepareLettering($parameters);
    $mask       = $this->prepareMask($lettering, $parameters);
    $foreground = $this->prepareForeground($mask, $parameters);

    return $this->compileImage($foreground, $parameters);
  }

  /**
   * Return image resource if cache file exists. If not - invoke callback method to
   * generate the image.
   */
  protected function getCacheFile($parameters, $callback) {
    $temp_hash = md5(implode($parameters));
    $cache_dir = WP_CONTENT_DIR.'/cache/wallpapers';
    $temp_file = rtrim($cache_dir, '/').'/'.$temp_hash.'.jpg';

    if (file_exists($temp_file)) {
      $image = $this->imagine->open($temp_file);
    } else {
      $image = $callback();
      $image->save($temp_file, ['jpeg_quality' => 100]);
    }

    return $image;
  }

  /**
   * Load and scale down the shape of lettering.
   */
  protected function prepareLettering($parameters) {
    return $this->getCacheFile(
      [
        $parameters['lettering_file'],
        $parameters['width'],
        $parameters['height'],
        $parameters['scale'],
      ],
      function () use ($parameters) {
        extract($parameters);

        $width  *= $scale;
        $height *= $scale;

        if ($width < $height) {
          $height = $width;
        } else {
          $width = $height;
        }

        $lettering = $this->imagine->open($lettering_file);
        $lettering = $lettering->thumbnail(new Box($width, $height));

        return $lettering;
      }
    );
  }

  /**
   * Create black/white mask from the lettering shape. It will be later used
   * to cut out needed parts of the foreground texture.
   */
  protected function prepareMask(Image $lettering, $parameters) {
    return $this->getCacheFile(
      [
        $parameters['lettering_file'],
        $parameters['width'],
        $parameters['height'],
      ],
      function () use ($lettering, $parameters) {
        extract($parameters);

        $mask = $this->imagine->create(new Box($width, $height));
        $mask = $this->trimImage($mask, $width, $height);
        $mask->paste($lettering, new Point(
          ($width - $lettering->getSize()->getWidth()) / 2,
          ($height - $lettering->getSize()->getHeight()) / 2
        ));

        return $mask;
      }
    );
  }

  /**
   * Load and cut out foreground texture using lettering shape as a mask.
   */
  protected function prepareForeground(Image $mask, $parameters) {
    $foreground = $this->getCacheFile(
      [
        $parameters['foreground_file'],
        $parameters['width'],
        $parameters['height'],
      ],
      function () use ($parameters) {
        extract($parameters);

        $foreground = $this->imagine->open($foreground_file);
        $foreground = $this->trimImage($foreground, $width, $height);

        return $foreground;
      }
    );

    $foreground->applyMask($mask);

    return $foreground;
  }

  /**
   * Having all parts of the output image generated, merge them together.
   */
  protected function compileImage(Image $foreground, $parameters) {
    $image = $this->getCacheFile(
      [
        $parameters['background_file'],
        $parameters['width'],
        $parameters['height'],
      ],
      function () use ($parameters) {
        extract($parameters);

        $image = $this->imagine->open($background_file);
        $image = $this->trimImage($image, $width, $height);

        return $image;
      }
    );

    $image->paste($foreground, new Point(0, 0));

    return $image;
  }

  /**
   * If image exceeds the size of generated image - scale it down and crop.
   */
  protected function trimImage(Image $image, $width, $height) {
    $image_width  = $image->getSize()->getWidth();
    $image_height = $image->getSize()->getHeight();

    if ($image_width > $width || $image_height > $height) {
      $image = $image->thumbnail(
        new Box($width, $height),
        Image::THUMBNAIL_OUTBOUND
      );
    }

    return $image;
  }
}
