<?php

namespace Ubermost\Hooks;

use Ubermost\Hooks;
use Ubermost\Helper;

/**
 * Sack for all the custom filters and actions.
 */
class AJAX extends Hooks
{
  /**
   * Register every hook.
   */
  public function register()
  {
    add_action('wp_ajax_get_post', [$this, 'get_post']);
    add_action('wp_ajax_nopriv_get_post', [$this, 'get_post']);
    add_action('wp_ajax_get_posts', [$this, 'get_posts']);
    add_action('wp_ajax_nopriv_get_posts', [$this, 'get_posts']);
    add_action('wp_ajax_get_isbn_links', [$this, 'get_isbn_links']);
    add_action('wp_ajax_nopriv_get_isbn_links', [$this, 'get_isbn_links']);
    add_action('wp_ajax_regenerate_wallpaper', [$this, 'regenerate_wallpaper']);
  }

  /**
   * Return error message if API call is invalid.
   */
  public function return_api_error()
  {
    wp_send_json_error();
  }

  /**
   * Regenerate wallpaper for given Post, Color and Size.
   */
  public function regenerate_wallpaper()
  {
    if (empty($_GET['post_id']) || empty($_GET['color_id']) || empty($_GET['size_id'])) {
      $this->return_api_error();
    }

    $post = get_post(intval($_GET['post_id']));
    $color = get_post(intval($_GET['color_id']));
    $size = get_post(intval($_GET['size_id']));

    if ( ! $post || ! $color || ! $size) {
      $this->return_api_error();
    }

    Helper::combine_wallpaper($post, $color, $size);

    return true;
  }

  /**
   * Return JSON with Post details.
   */
  public function get_post()
  {
    if (empty($_GET['post_id'])) {
      $this->return_api_error();
    }

    $post = get_post(intval($_GET['post_id']));

    if (empty($_GET['color_id'])) {
      $color = get_post(get_field('default_color', 'option'));
    } else {
      $color = get_post(intval($_GET['color_id']));
    }

    if ( ! $post || ! $color) {
      $this->return_api_error();
    }

    $preview_image = Helper::get_file_link(
      'show',
      $post->ID,
      $color->ID,
      get_field('preview_size', 'option')
    );

    wp_send_json_success([
      'ID' => $post->ID,
      'quote' => $post->post_content,
      'image' => $preview_image,
      'blog_link' => get_field('blog_link', $post->ID),
      'blog_image' => get_field('blog_image', $post->ID),
      'reblog_link' => get_field('reblog_link', $post->ID),
    ]);
  }

  /**
   * Return JSON with list of Posts.
   */
  public function get_posts()
  {
    $result = [];
    $posts = Helper::load_public_posts();

    foreach ($posts as $post) {
      $result[] = [
        'ID' => $post->ID,
        'permalink' => site_url('?post='.$post->ID),
        'thumbnail' => str_replace('_1280', '_250', get_field('blog_image', $post->ID)),
      ];
    }

    wp_send_json_success(['posts' => $result]);
  }

  /**
   * Return JSON with list of links to buy books on Amazon.
   */
  public function get_isbn_links()
  {
    $isbns = isset($_GET['isbns']) ? $_GET['isbns'] : [];
    $links = [];

    foreach ($isbns as $isbn) {
      $book = array_pop(get_posts([
        'post_type' => 'book',
        'meta_query' => [
          [
            'key' => 'isbn_10',
            'value' => str_replace('isbn', '', $isbn),
          ],
        ],
      ]));

      $links[$isbn] = get_field('link', $book->ID);
    }

    wp_send_json_success(['links' => $links]);
  }
}
