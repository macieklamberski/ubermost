<?php

namespace Ubermost\Controller;

use Ubermost\Helper;
use Ubermost\Controller;

class Wallpapers extends Controller
{
    /**
     * Main method for handling the logic.
     */
    public function run()
    {
        if ($_GET['ids']) {
            $posts = explode(',', $_GET['ids']);
        } else {
            $posts = array_map(function ($post) {
                return $post->ID;
            }, get_posts(['posts_per_page' => -1]));
        }

        $combinations = [];
        $public_posts = array_map(function ($post) {return $post->ID;}, Helper::load_public_posts());
        $public_colors = get_posts(['post_type' => 'color', 'posts_per_page' => -1]);
        $public_sizes = get_posts([
            'post_type' => 'size',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'regenerateable',
                    'value' => true,
                ],
            ],
        ]);

        $posts = array_intersect($posts, $public_posts);

        foreach ($posts as $post) {
            foreach ($public_colors as $color) {
                foreach ($public_sizes as $size) {
                    $combinations[] = [
                        'post' => $post,
                        'color' => $color->ID,
                        'size' => $size->ID,
                    ];
                }
            }
        }

        $this->render('admin/wallpapers', [
            'combinations' => $combinations,
            'specific_posts' =>  ! empty($_GET['ids']),
        ]);
    }
}
