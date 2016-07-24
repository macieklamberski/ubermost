<?php

namespace UbermostCreate;

use Imagine\GD\Image;
use Imagine\Image\Box;
use Imagine\GD\Imagine;
use Imagine\Image\Point;

/**
 * Utility class for creating merged lettering images.
 */
class Generator
{
  /**
   * Array with list of settings.
   */
  protected $settings;

  /**
   * Instance of Imagine\GD\Imagine.
   */
  protected $imagine;

  /**
   * Constructor used for initializing Imagine library as it's referred to
   * multiple times.
   */
  public function __construct(array $settings = [])
  {
    $this->imagine = new Imagine();
    $this->settings = array_merge([
      'partials_path' => sys_get_temp_dir(),
      'combined_path' => sys_get_temp_dir(),
      'quality' => 100,
    ], $settings);
  }

  /**
   * Generate final image from given colors, lettering shape and size.
   */
  public function combine(array $parameters, array $ids)
  {
    $lettering = $this->prepareLettering($parameters);
    $mask = $this->prepareMask($lettering, $parameters);
    $foreground = $this->prepareForeground($mask, $parameters);

    $this
      ->compileImage($foreground, $parameters)
      ->save(
        $this->getCombinedFile($ids),
        ['jpeg_quality' => $this->settings['quality']]
      );
  }

  /**
   * Return path to combined wallpaper file.
   */
  public function getCombinedFile(array $ids)
  {
    return $this->getFileName($this->settings['combined_path'], $ids);
  }

  /**
   * Compile array of parameters into hash for storing the file.
   */
  protected function getFileName($path, array $parameters)
  {
    $path = rtrim($path, '/').'/';
    $file = md5(implode($parameters)).'.jpg';
    return $path.$file;
  }

  /**
   * Return image resource if cache file exists. If not - invoke callback method to
   * generate the image.
   */
  protected function getPartialFile(array $parameters, callable $callback)
  {
    $temp_file = $this->getFileName($this->settings['partials_path'], $parameters);

    if (file_exists($temp_file)) {
      $image = $this->imagine->open($temp_file);
    } else {
      $image = $callback();
      $image->save($temp_file, [
        'jpeg_quality' => $this->settings['quality'],
      ]);
    }

    return $image;
  }

  /**
   * Load and scale down the shape of lettering.
   */
  protected function prepareLettering(array $parameters)
  {
    return $this->getPartialFile(
      [
        $parameters['lettering_file'],
        $parameters['width'],
        $parameters['height'],
        $parameters['scale'],
      ],
      function () use ($parameters) {
        extract($parameters);

        $width *= $scale;
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
  protected function prepareMask(Image $lettering, array $parameters)
  {
    return $this->getPartialFile(
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
  protected function prepareForeground(Image $mask, array $parameters)
  {
    $foreground = $this->getPartialFile(
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
  protected function compileImage(Image $foreground, array $parameters)
  {
    $image = $this->getPartialFile(
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
  protected function trimImage(Image $image, $width, $height)
  {
    $image_width = $image->getSize()->getWidth();
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
