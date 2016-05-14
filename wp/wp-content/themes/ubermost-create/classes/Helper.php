<?php

namespace UbermostCreate;

use WP_Post;

/**
 * Sack for static helper methods used in templates.
 */
class Helper
{
  /**
   * Load post given in parameter or load default one if given does not
   * meet requirements of being selected or it does not exist.
   */
  public static function load_selected_post($post_id)
  {
    if ($post_id) {
      $post = get_post($post_id);
    }

    if ( ! $post || $post->post_status != 'publish' || $post->post_type != 'post') {
      $posts = wp_get_recent_posts([
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => 1,
      ], OBJECT);

      return isset($posts[0]) ? $posts[0] : null;
    }

    return $post;
  }

  /**
   * Load color given in parameter or load default one if given does not
   * meet requirements of being selected or it does not exist.
   */
  public static function load_selected_color($color_id)
  {
    if ($color_id) {
      $color = get_post($color_id);
    }

    if ( ! $color || $color->post_status != 'publish' || $color->post_type != 'color' || ! get_field('public', $color->ID)) {
      $colors = get_posts([
        'post_type' => 'color',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'meta_key' => 'public',
        'meta_value' => true,
      ], OBJECT);

      return isset($colors[0]) ? $colors[0] : null;
    }

    return $color;
  }

  /**
   * Load size given in parameter if it meet requirements of being selected.
   */
  public static function load_selected_size($size_id)
  {
    if ($size_id) {
      $size = get_post($size_id);
    }

    if ($size && $size->post_status == 'publish' && $size->post_type == 'size') {
      return $size;
    }
  }

  /**
   * Load Posts that can be publicly visible.
   */
  public static function load_public_posts()
  {
    $public_posts = [];
    $all_posts = get_posts([
      'post_type' => 'post',
      'posts_per_page' => -1,
    ]);

    foreach ($all_posts as $post) {
      if ( ! get_field('lettering', $post->ID)) {
        continue;
      }

      $public_posts[] = $post;
    }

    return $public_posts;
  }

  /**
   * Load Colors that can be publicly visible.
   */
  public static function load_public_colors()
  {
    return get_posts([
      'post_type' => 'color',
      'posts_per_page' => -1,
      'meta_query' => [
        [
          'key' => 'public',
          'value' => true,
        ],
      ],
    ]);
  }

  /**
   * Load Sizes that can be publicly visible.
   */
  public static function load_public_sizes($group = null)
  {
    return get_posts([
      'post_type' => 'size',
      'posts_per_page' => -1,
      'group' => $group,
      'meta_query' => [
        [
          'key' => 'public',
          'value' => true,
        ],
      ],
    ]);
  }

  /**
   * Return absolute path to the file (based on the site URL).
   */
  protected static function get_local_path($url)
  {
    $uploads_dir = wp_upload_dir();
    $local_path = str_replace($uploads_dir['baseurl'], $uploads_dir['basedir'], $url);

    return $local_path;
  }

  public static function combine_wallpaper(WP_Post $post, WP_Post $color, WP_Post $size)
  {
    (new Generator())
      ->combine([
        'lettering_file' => self::get_local_path(get_field('lettering', $post->ID)),
        'foreground_file' => self::get_local_path(get_field('fg_texture', $color->ID)),
        'background_file' => self::get_local_path(get_field('bg_texture', $color->ID)),
        'width' => get_field('width', $size->ID),
        'height' => get_field('height', $size->ID),
        'scale' => get_field('scale', $size->ID),
      ], [
        $post->ID, $color->ID, $size->ID,
      ]);
  }

  /**
   * Invoke download action if all the parameters are set correctly.
   */
  public static function get_file($action, WP_Post $post, WP_Post $color, WP_Post $size)
  {
    if ( ! in_array($action, ['download', 'show'])) {
      return;
    }

    $file = (new Generator())->getCombinedFile([$post->ID, $color->ID, $size->ID]);

    if ( ! file_exists($file)) {
      Helper::combine_wallpaper($post, $color, $size);
    }

    $name = 'Ubermost - '.$post->post_title.' - '.$size->post_title.'.jpg';

    header('Content-Type: image/jpeg');

    if ($action == 'download') {
      header('Content-Transfer-Encoding: Binary');
      header('Content-disposition: attachment; filename="'.$name.'"');
    } else {
      header('content-disposition: inline; filename="'.$name.'";');
    }

    readfile($file);
  }

  /**
   * Return URL to the image generation script.
   */
  public static function get_file_link($action, $post_id, $color_id, $size_id)
  {
    return add_query_arg([
      'action' => $action,
      'post_id' => $post_id,
      'color_id' => $color_id,
      'size_id' => $size_id,
    ], site_url());
  }
}
