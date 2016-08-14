<?php

use Timber\Post;
use UbermostCreate\Helper;

$current_post = Helper::load_selected_post($_GET['post_id'], false);
$current_color = Helper::load_selected_color($_GET['color_id']);
$current_size = Helper::load_selected_size($_GET['size_id']);

if ($current_post && $current_color && $current_size) {
  Helper::get_file($_GET['action'], $current_post, $current_color, $current_size);
}

$context = Timber::get_context();

$context['api_endpoint'] = admin_url('admin-ajax.php');
$context['current_post'] = new Post($current_post);
$context['current_color'] = new Post($current_color);
$context['current_size'] = $current_size ? new Post($current_size) : null;
$context['public_colors'] = Helper::load_public_colors();
$context['size_groups'] = Timber::get_terms('group');
$context['environment'] = 'development';

foreach ($context['size_groups'] as $group) {
  $context['sizes'][$group->name] = Helper::load_public_sizes($group->name);
}

Timber::render('views/pages/home.twig', $context);
