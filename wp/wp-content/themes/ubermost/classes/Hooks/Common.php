<?php

namespace Ubermost\Hooks;

use Ubermost\Hooks;
use Ubermost\Wallpapers;

/**
 * Sack for all the custom filters and actions.
 */
class Common extends Hooks
{
    /**
     * Register every hook.
     */
    public function register()
    {
        add_filter('upload_mimes', [$this, 'allow_svg_upload']);
        add_action('wp', [$this, 'check_domain']);
        add_action('admin_menu', [$this, 'show_published_by_default']);
        add_action('admin_menu', [$this, 'register_utility_pages']);
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
     * Add custom utility pages.
     */
    public function register_utility_pages()
    {
        add_management_page(
            'Regnerate Wallpapers',
            'Regen. Wallpapers',
            'publish_posts',
            'regenerate-wallpapers',
            function () {
                require get_stylesheet_directory().'/admin-wallpapers.php';
            }
        );

        add_management_page(
            'Publisher',
            'Publisher',
            'publish_posts',
            'publisher',
            function () {
                require get_stylesheet_directory().'/admin-publisher.php';
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
     * Show only publised posts on the initial list of Posts in WP Admin.
     */
    public function show_published_by_default()
    {
        global $submenu;
        $submenu['edit.php'][5][2] = 'edit.php?post_status=publish';
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
     * Check if the domain is correct.
     */
    public function check_domain()
    {
        if (is_singular()) {
            return;
        }

        $url = parse_url(get_site_url());

        if ($_SERVER['HTTP_HOST'] !== $url['host']) {
            wp_redirect(get_site_url(null, $_SERVER['REQUEST_URI']), 301);
            exit;
        }
    }
}
