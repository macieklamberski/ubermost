<?php

use Timber\Post;
use UbermostCreate\Helper;

$post = get_post(get_the_ID());

$color = get_field('main_color', $post->ID) ?: get_field('default_color', 'option');
$color = get_post($color);

$size = get_field('socials_size', 'option');
$size = get_post($size);

if ( ! $post || ! $color || ! $size) {
  die;
}

$image = Helper::get_file_link('show', $post->ID, $color->ID, $size->ID);

$context = Timber::get_context();
$context['post'] = new Post($post->ID);
$context['image'] = $image;

Timber::render('views/pages/single.twig', $context);
