<?php
namespace Ubernator;

use Ubernator\Generator;

/**
 * Sack for static helper methods used in templates.
 */
class Helper {

    /**
     * Return path (or URL) to the wallpaper file with generated for given
     * post, color and size.
     */
    public static function get_cache_file($post_id, $color_id, $size_id, $path = true) {
        $cache_dir = wp_upload_dir();
        $cache_dir = $path ? $cache_dir['basedir'] : $cache_dir['baseurl'];
        $cache_dir = $cache_dir.'/wallpapers';

        return $cache_dir.'/'.$post_id.'-'.$color_id.'-'.$size_id.'.jpg';
    }

    /**
     * Load post given in parameter or load default one if given does not
     * meet requirements of being selected or it does not exist.
     */
    public static function load_selected_post($post_id) {
        if ($post_id) {
            $post = get_post($post_id);
        }

        if (!$post || $post->post_status != 'publish' || $post->post_type != 'post') {
            $posts = wp_get_recent_posts([
                'post_type'      => 'post',
                'post_status'    => 'publish',
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
    public static function load_selected_color($color_id) {
        if ($color_id) {
            $color = get_post($color_id);
        }

        if (!$color || $color->post_status != 'publish' || $color->post_type != 'color' || !get_field('public', $color->ID)) {
            $colors = get_posts([
                'post_type'      => 'color',
                'post_status'    => 'publish',
                'posts_per_page' => 1,
                'meta_key'       => 'public',
                'meta_value'     => true,
            ], OBJECT);

            return isset($colors[0]) ? $colors[0] : null;
        }

        return $color;
    }

    /**
     * Load size given in parameter if it meet requirements of being selected.
     */
    public static function load_selected_size($size_id) {
        if ($size_id) {
            $size = get_post($size_id);
        }

        if ($size && $size->post_status == 'publish' && $size->post_type == 'size' && get_field('public', $size->ID)) {
            return $size;
        }
    }

    /**
     * Invoke download action if all the parameters are set correctly.
     */
    public static function download($post, $color, $size) {
        $download = $_GET['action'] == 'download';
        $post     = $post->ID == $_GET['post_id'] ? $post : null;
        $color    = $color->ID == $_GET['color_id'] ? $color : null;
        $size     = $size->ID == $_GET['size_id'] ? $size : null;

        if (!$download || !$post || !$color || !$size) {
            return;
        }

        $file = self::get_cache_file($post->ID, $color->ID, $size->ID);
        $info = getimagesizefromstring(file_get_contents($file));
        $name = 'Ubermost - '.$post->post_title.' - '.$size->post_title.'.jpg';
        header('Content-type: '.$info['mime']);
        header('Content-Transfer-Encoding: Binary');
        header('Content-disposition: attachment; filename="'.$name.'"');
        readfile($file);
    }

    /**
     * Load Posts that can be publicly visible.
     */
    public static function load_public_posts() {
        $public_posts = [];
        $all_posts    = get_posts([
            'post_type'      => 'post',
            'posts_per_page' => -1,
        ]);

        foreach ($all_posts as $post) {
            if (!get_field('lettering', $post->ID)) {
                continue;
            }

            $public_posts[] = $post;
        }

        return $public_posts;
    }

    /**
     * Load Colors that can be publicly visible.
     */
    public static function load_public_colors() {
        return get_posts([
            'post_type'      => 'color',
            'posts_per_page' => -1,
            'meta_query'     => [
                [
                    'key'   => 'public',
                    'value' => true,
                ]
            ],
        ]);
    }

    /**
     * Load Sizes that can be publicly visible.
     */
    public static function load_public_sizes($group = null) {
        return get_posts([
            'post_type'      => 'size',
            'posts_per_page' => -1,
            'group'          => $group,
            'meta_query'     => [
                [
                    'key'   => 'public',
                    'value' => true,
                ]
            ],
        ]);
    }
}
