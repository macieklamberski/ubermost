<?php

namespace Ubermost\Controller;

use Timber;
use Timber\Post;
use Ubermost\Helper;
use Ubermost\Controller;

class Index extends Controller
{
    /**
     * Main method for handling the logic.
     */
    public function run()
    {
        $current_post = Helper::load_selected_post($_GET['post_id'], current_user_can('administrator'));
        $current_color = Helper::load_selected_color($_GET['color_id']);
        $current_size = Helper::load_selected_size($_GET['size_id']);

        if ($current_post && $current_color && $current_size) {
            Helper::get_file($_GET['action'], $current_post, $current_color, $current_size);
        }

        $size_groups = Timber::get_terms('group');
        $sizes = [];

        foreach ($size_groups as $group) {
            $sizes[$group->name] = Helper::load_public_sizes($group->name);
        }

        $this->render('pages/home', [
            'api_endpoint' => admin_url('admin-ajax.php'),
            'current_post' => new Post($current_post),
            'current_color' => new Post($current_color),
            'current_size' => $current_size ? new Post($current_size) : null,
            'public_colors' => Helper::load_public_colors(),
            'size_groups' => $size_groups,
            'sizes' => $sizes,
        ]);
    }
}
