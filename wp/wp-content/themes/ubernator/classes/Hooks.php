<?php
namespace Ubernator;

use Ubernator\Generator;
use Ubernator\Helper;

/**
 * Sack for all the custom filters and actions.
 */
class Hooks {

    /**
     * Register every hook.
     */
    public function register() {
        add_filter('upload_mimes', [$this, 'allow_svg_upload']);
        add_action('init', [$this, 'register_posts']);
        add_action('init', [$this, 'register_groups_taxonomy']);
        add_action('wp_ajax_get_post', [$this, 'get_post']);
        add_action('wp_ajax_nopriv_get_post', [$this, 'get_post']);
        add_action('wp_ajax_get_posts', [$this, 'get_posts']);
        add_action('wp_ajax_nopriv_get_posts', [$this, 'get_posts']);
        add_action('admin_menu', [$this, 'show_published_by_default']);

        // Enable Options page if ACF plugins is enabled.
        if (function_exists('acf_add_options_page')) {
            acf_add_options_page();
        }

        // Define custom bulk actions (but only if dsds plugin is enabled).
        if (class_exists('\Seravo_Custom_Bulk_Action')) {
            $this->define_bulk_regenerate_posts_cache();
        }
    }

    /**
     * Allow SVG files to be uploaded to Media Library.
     */
    public function allow_svg_upload($mime_types) {
        $mime_types['svg'] = 'image/svg+xml';
        return $mime_types;
    }

    /**
     * Register a Color, Size post types.
     */
    public function register_posts() {
        $types = [
            'color' => 'dashicons-admin-customizer',
            'size'  => 'dashicons-image-crop',
        ];

        foreach ($types as $slug => $icon) {
            register_post_type($slug, [
                'public'        => false,
                'show_ui'       => true,
                'labels'        => $this->generate_post_labels($slug),
                'rewrite'       => ['slug' => $slug],
                'supports'      => ['title'],
                'menu_position' => 5,
                'menu_icon'     => $icon,
            ]);
        }
    }

    /**
     * Show only publised posts on the initial list of Posts in WP Admin.
     */
    public function show_published_by_default() {
        global $submenu;
        $submenu['edit.php'][5][2] = 'edit.php?post_status=publish';
    }

    /**
     * Create Groups taxonomy used for groupping device sizes.
     */
    public function register_groups_taxonomy() {
        $name = 'group';

        register_taxonomy($name, ['size'], [
            'hierarchical'      => false,
            'labels'            => $this->generate_taxonomy_labels($name),
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => $name],
        ]);
    }

    /**
     * Return error message if API call is invalid.
     */
    public function return_api_error() {
        wp_send_json_error();
    }

    /**
     * Returns list of all custom post type labels with given name.
     */
    protected function generate_post_labels($name) {
        $name = ucfirst($name);

        return [
            'name'               => $name.'s',
            'singular_name'      => $name,
            'menu_name'          => $name.'s',
            'name_admin_bar'     => $name,
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New '.$name,
            'new_item'           => 'New '.$name,
            'edit_item'          => 'Edit '.$name,
            'view_item'          => 'View '.$name,
            'all_items'          => 'All '.$name.'s',
            'search_items'       => 'Search '.$name.'s',
            'parent_item_colon'  => 'Parent '.$name.'s:',
            'not_found'          => 'No '.$name.'s found.',
            'not_found_in_trash' => 'No '.$name.'s found in Trash.',
        ];
    }

    /**
     * Returns list of all custom taxonomy labels with given name.
     */
    protected function generate_taxonomy_labels($name) {
        $name = ucfirst($name);

        return [
            'name'                       => $name.'s',
            'singular_name'              => $name,
            'search_items'               => 'Search '.$name.'s',
            'popular_items'              => 'Popular '.$name.'s',
            'all_items'                  => 'All '.$name.'s',
            'parent_item'                => 'Parent'.$name,
            'parent_item_colon'          => 'Parent '.$name.':',
            'edit_item'                  => 'Edit'.$name,
            'update_item'                => 'Update'.$name,
            'add_new_item'               => 'Add New'.$name,
            'new_item_name'              => 'New '.$name.' Name',
            'separate_items_with_commas' => 'Separate '.$name.'s with commas',
            'add_or_remove_items'        => 'Add or remove '.$name.'s',
            'choose_from_most_used'      => 'Choose from the most used '.$name.'s',
            'not_found'                  => 'No '.$name.'s found.',
            'menu_name'                  => $name.'s',
        ];
    }

    /**
     * Return absolute path to the file (based on the site URL).
     */
    protected function get_local_path($url) {
        $uploads_dir = wp_upload_dir();
        $local_path  = str_replace($uploads_dir['baseurl'], $uploads_dir['basedir'], $url);

        return $local_path;
    }

    /**
     * Bulk action for generating cache for selected Posts.
     */
    public function define_bulk_regenerate_posts_cache() {
        $bulk_actions = new \Seravo_Custom_Bulk_Action([
            'post_type' => 'post',
        ]);

        $bulk_actions->register_bulk_action([
            'menu_text' => 'Regenerate Cache',
            'admin_notice' => [
                'single' => 'Cache for 1 Post was regenerated.',
                'plural' => 'Cache for %s Posts was regenerated.',
            ],
            'callback' => function ($post_ids) {
                $generator = new Generator();
                $sizes     = get_posts(['post_type' => 'size', 'posts_per_page' => -1]);
                $colors    = get_posts(['post_type' => 'color', 'posts_per_page' => -1]);

                foreach ($sizes as $id => $size) {
                    $sizes[$id] = [
                        'ID'     => $size->ID,
                        'width'  => get_field('width', $size->ID),
                        'height' => get_field('height', $size->ID),
                        'scale'  => get_field('scale', $size->ID),
                    ];
                }

                foreach ($colors as $id => $color) {
                    $colors[$id] = [
                        'ID'         => $color->ID,
                        'foreground' => $this->get_local_path(get_field('fg_texture', $color->ID)),
                        'background' => $this->get_local_path(get_field('bg_texture', $color->ID)),
                    ];
                }

                foreach ($post_ids as $post_id) {
                    $lettering = $this->get_local_path(get_field('lettering', $post_id));

                    if (!$lettering) {
                        continue;
                    }

                    foreach ($colors as $color) {
                        foreach ($sizes as $size) {
                            $cache_file = Helper::get_cache_file(
                                $post_id, $color['ID'], $size['ID']
                            );

                            $generator
                                ->generate(
                                    $lettering,
                                    $color['foreground'],
                                    $color['background'],
                                    $size['width'],
                                    $size['height'],
                                    $size['scale']
                                )
                                ->save($cache_file, ['jpeg_quality' => 100]);
                        }
                    }
                }

                die(memory_get_peak_usage());

                return true;
            }
        ]);

        $bulk_actions->init();
    }

    /**
     * Return JSON with Post details.
     */
    public function get_post() {
        if (empty($_GET['post_id']) || empty($_GET['color_id'])) {
            $this->return_api_error();
        }

        $post  = get_post(intval($_GET['post_id']));
        $color = get_post(intval($_GET['color_id']));

        if (!$post || !$color) {
            $this->return_api_error();
        }

        $preview_image = Helper::get_cache_file(
            $post->ID, $color->ID, get_field('preview_size', 'option'), false
        );

        wp_send_json_success([
            'ID'          => $post->ID,
            'quote'       => $post->post_content,
            'image'       => $preview_image,
            'author'      => get_field('author', $post->ID),
            'source'      => get_field('source', $post->ID),
            'link'        => get_field('link', $post->ID),
            'blog_link'   => get_field('blog_link', $post->ID),
            'blog_image'  => get_field('blog_image', $post->ID),
            'reblog_link' => get_field('reblog_link', $post->ID),
        ]);
    }

    /**
     * Return JSON with list of Posts.
     */
    public function get_posts() {
        $result = [];
        $posts  = Helper::load_public_posts();

        foreach ($posts as $post) {
            $result[] = [
                'ID'        => $post->ID,
                'permalink' => site_url('?post='.$post->ID),
                'thumbnail' => str_replace('_1280', '_250', get_field('blog_image', $post->ID)),
            ];
        }

        wp_send_json_success(['posts' => $result]);
    }
}
