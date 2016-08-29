<?php

namespace Ubermost\Hooks;

use Ubermost\Hooks;
use Ubermost\API\Buffer;
use Ubermost\API\Tumblr;
use Ubermost\API\Twitter;
use Ubermost\API\Facebook;

/**
 * Sack for all the custom filters and actions.
 */
class CPTs extends Hooks
{
    /**
     * Register every hook.
     */
    public function register()
    {
        add_action('init', [$this, 'register_posts']);
        add_action('init', [$this, 'register_groups_taxonomy']);
        add_action('new_to_publish', [$this, 'publish_on_social_media']);
        add_action('auto-draft_to_publish', [$this, 'publish_on_social_media']);
        add_action('draft_to_publish', [$this, 'publish_on_social_media']);
        add_action('future_to_publish', [$this, 'publish_on_social_media']);
    }

    /**
     * Register a Color, Size post types.
     */
    public function register_posts()
    {
        $types = [
            'color' => 'dashicons-admin-customizer',
            'size' => 'dashicons-image-crop',
            'book' => 'dashicons-book-alt',
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
     * Publish post on social media while publishing it in generator.
     */
    public function publish_on_social_media($postId)
    {
        if (get_post_type($postId) !== 'post') {
            return;
        }

        $tumblr = new Tumblr();

        if ( ! $tumblr->isConfigured() || ! $tumblr->isEnabled()) {
            return;
        }

        $tumblrPost = $tumblr->publishPost($postId);

        if ( ! $tumblrPost) {
            return;
        }

        $blogImage = $tumblrPost->photos[0]->original_size->url;
        $blogLink = $tumblrPost->post_url;
        $reblogLink = sprintf('https://www.tumblr.com/reblog/%s/%s', $tumblrPost->id, $tumblrPost->reblog_key);

        update_field('blog_image', $blogImage, $postId);
        update_field('blog_link', $blogLink, $postId);
        update_field('reblog_link', $reblogLink, $postId);

        $twitter = new Twitter();
        if ($twitter->isConfigured() && $twitter->isEnabled()) {
            $twitter->publishPost($postId);
        }

        $facebook = new Facebook();
        if ($facebook->isConfigured() && $facebook->isEnabled()) {
            $facebook->publishPost($postId);
        }

        $buffer = new Buffer();
        if ($buffer->isConfigured() && $buffer->isEnabled()) {
            $buffer->publishPost($postId);
        }
    }
}
