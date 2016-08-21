<?php

namespace Ubermost\Controller;

use Timber\Post;
use Ubermost\Helper;
use Ubermost\Controller;

class Single extends Controller
{
    /**
     * Main method for handling the logic.
     */
    public function run()
    {
        $post = get_post(get_the_ID());

        $color = get_field('main_color', $post->ID) ?: get_field('default_color', 'option');
        $color = get_post($color);

        $size = get_field('socials_size', 'option');
        $size = get_post($size);

        if ( ! $post || ! $color || ! $size) {
            die;
        }

        if ($this->isVisitedByRobot() || $this->isPreview()) {
            $this->render('pages/single', [
                'post' => new Post($post->ID),
                'image' => Helper::get_file_link('show', $post->ID, $color->ID, $size->ID),
            ]);
        } else {
            wp_redirect(get_field('blog_link', $post->ID));
            exit;
        }
    }

    /**
     * Check if page is visited by social media robots.
     */
    protected function isVisitedByRobot()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        $bots = [
            'facebookexternalhit/',
            'Facebot',
            'Twitterbot',
        ];

        foreach ($bots as $bot) {
            if (strpos($agent, $bot) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if page is previewed by admin.
     */
    protected function isPreview()
    {
        return $_GET['preview'] === 'true' && current_user_can('administrator');
    }
}
