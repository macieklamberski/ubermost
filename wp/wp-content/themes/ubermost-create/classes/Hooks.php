<?php

namespace UbermostCreate;

use UbermostCreate\Helper;
use UbermostCreate\Wallpapers;

/**
 * Sack for all the custom filters and actions.
 */
class Hooks
{
  /**
   * Register every hook.
   */
  public function register()
  {
    add_filter('upload_mimes', [$this, 'allow_svg_upload']);
    add_action('init', [$this, 'register_posts']);
    add_action('init', [$this, 'register_groups_taxonomy']);
    add_action('wp_ajax_get_post', [$this, 'get_post']);
    add_action('wp_ajax_nopriv_get_post', [$this, 'get_post']);
    add_action('wp_ajax_get_posts', [$this, 'get_posts']);
    add_action('wp_ajax_nopriv_get_posts', [$this, 'get_posts']);
    add_action('wp_ajax_regenerate_wallpaper', [$this, 'regenerate_wallpaper']);
    add_action('admin_menu', [$this, 'show_published_by_default']);
    add_action('admin_menu', [$this, 'register_utility_page']);
    add_action('admin_menu', [$this, 'remove_menu_pages']);
    add_action('wp_before_admin_bar_render', [$this, 'remove_not_needed_items_from_admin_bar'], 11);
    add_action('admin_head', [$this, 'hide_link_to_mine_posts']);

    // Enable Options page if ACF plugins is enabled.
    if (function_exists('acf_add_options_page')) {
      acf_add_options_page();
    }

    // Define custom bulk actions (but only if dsds plugin is enabled).
    if (class_exists('\Seravo_Custom_Bulk_Action')) {
      $this->define_bulk_regenerate_posts_wallpapers();
    }
  }

  /**
   * Add custom utility page for regenerating wallpapers.
   */
  public function register_utility_page()
  {
    add_management_page(
      'Regnerate Wallpapers',
      'Regen. Wallpapers',
      'publish_posts',
      'regenerate-wallpapers',
      function () {
        require get_stylesheet_directory().'/wallpapers/template.php';
      }
    );
  }

  /**
   * Allow SVG files to be uploaded to Media Library.
   */
  public function allow_svg_upload($mime_types)
  {
    $mime_types['svg'] = 'image/svg+xml';
    return $mime_types;
  }

  /**
   * Register a Color, Size post types.
   */
  public function register_posts()
  {
    $types = [
      'color' => 'dashicons-admin-customizer',
      'size' => 'dashicons-image-crop',
    ];

    foreach ($types as $slug => $icon) {
      register_post_type($slug, [
        'public' => false,
        'show_ui' => true,
        'labels' => $this->generate_post_labels($slug),
        'rewrite' => ['slug' => $slug],
        'supports' => ['title'],
        'menu_position' => 5,
        'menu_icon' => $icon,
      ]);
    }
  }

  /**
   * Show only publised posts on the initial list of Posts in WP Admin.
   */
  public function show_published_by_default()
  {
    global $submenu;
    $submenu['edit.php'][5][2] = 'edit.php?post_status=publish';
  }

  /**
   * Create Groups taxonomy used for groupping device sizes.
   */
  public function register_groups_taxonomy()
  {
    $name = 'group';

    register_taxonomy($name, ['size'], [
      'hierarchical' => false,
      'labels' => $this->generate_taxonomy_labels($name),
      'show_ui' => true,
      'show_admin_column' => true,
      'query_var' => true,
      'rewrite' => ['slug' => $name],
    ]);
  }

  /**
   * Remove unnecessary pages from the WP Admin menu.
   */
  public function remove_menu_pages()
  {
    remove_menu_page('edit-comments.php');
    remove_menu_page('themes.php');
    remove_menu_page('users.php');
    remove_menu_page('edit.php?post_type=page');
  }

  /**
   * Hide Logo, Search, Comments etc. sections from Admin Bar.
   */
  public function remove_not_needed_items_from_admin_bar()
  {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('comments');
    $wp_admin_bar->remove_menu('wpfc-toolbar-parent');
    $wp_admin_bar->remove_menu('customize');
    $wp_admin_bar->remove_menu('wp-logo');
    $wp_admin_bar->remove_menu('search');
    $wp_admin_bar->remove_menu('new-content');
  }

  /**
   * Hide link to Mine posts.
   */
  public function hide_link_to_mine_posts()
  {
    echo '<style>.subsubsub .mine { display: none; }</style>';
  }

  /**
   * Return error message if API call is invalid.
   */
  public function return_api_error()
  {
    wp_send_json_error();
  }

  /**
   * Returns list of all custom post type labels with given name.
   */
  protected function generate_post_labels($name)
  {
    $name = ucfirst($name);

    return [
      'name' => $name.'s',
      'singular_name' => $name,
      'menu_name' => $name.'s',
      'name_admin_bar' => $name,
      'add_new' => 'Add New',
      'add_new_item' => 'Add New '.$name,
      'new_item' => 'New '.$name,
      'edit_item' => 'Edit '.$name,
      'view_item' => 'View '.$name,
      'all_items' => 'All '.$name.'s',
      'search_items' => 'Search '.$name.'s',
      'parent_item_colon' => 'Parent '.$name.'s:',
      'not_found' => 'No '.$name.'s found.',
      'not_found_in_trash' => 'No '.$name.'s found in Trash.',
    ];
  }

  /**
   * Returns list of all custom taxonomy labels with given name.
   */
  protected function generate_taxonomy_labels($name)
  {
    $name = ucfirst($name);

    return [
      'name' => $name.'s',
      'singular_name' => $name,
      'search_items' => 'Search '.$name.'s',
      'popular_items' => 'Popular '.$name.'s',
      'all_items' => 'All '.$name.'s',
      'parent_item' => 'Parent'.$name,
      'parent_item_colon' => 'Parent '.$name.':',
      'edit_item' => 'Edit'.$name,
      'update_item' => 'Update'.$name,
      'add_new_item' => 'Add New'.$name,
      'new_item_name' => 'New '.$name.' Name',
      'separate_items_with_commas' => 'Separate '.$name.'s with commas',
      'add_or_remove_items' => 'Add or remove '.$name.'s',
      'choose_from_most_used' => 'Choose from the most used '.$name.'s',
      'not_found' => 'No '.$name.'s found.',
      'menu_name' => $name.'s',
    ];
  }

  /**
   * Bulk action for generating wallpapers cache for selected Posts.
   */
  public function define_bulk_regenerate_posts_wallpapers()
  {
    $bulk_actions = new \Seravo_Custom_Bulk_Action([
      'post_type' => 'post',
    ]);

    $bulk_actions->register_bulk_action([
      'menu_text' => 'Regenerate Wallpapers',
      'admin_notice' => [
        'single' => 'Wallpapers for 1 Post was regenerated.',
        'plural' => 'Wallpapers for %s Posts was regenerated.',
      ],
      'callback' => function ($post_ids) {
        $url = admin_url('admin.php?page=regenerate-wallpapers');
        $url = add_query_arg('ids', implode($post_ids, ','), $url);
        wp_redirect($url);
        exit;
      },
    ]);

    $bulk_actions->init();
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
    if (empty($_GET['post_id']) || empty($_GET['color_id'])) {
      $this->return_api_error();
    }

    $post = get_post(intval($_GET['post_id']));
    $color = get_post(intval($_GET['color_id']));

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
      'author' => get_field('author', $post->ID),
      'source' => get_field('source', $post->ID),
      'link' => get_field('link', $post->ID),
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
}
