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

if (
  strpos($_SERVER['HTTP_USER_AGENT'], 'facebookexternalhit/') !== false ||
  strpos($_SERVER['HTTP_USER_AGENT'], 'Facebot') !== false ||
  strpos($_SERVER['HTTP_USER_AGENT'], 'Twitterbot') !== false
) {
  $image = Helper::get_file_link('show', $post->ID, $color->ID, $size->ID);

  $context = Timber::get_context();
  $context['post'] = new Post($post->ID);
  $context['image'] = $image;

  Timber::render('views/pages/single.twig', $context);
} else {
  header('Location: '.get_field('blog_link', $post->ID));
  exit;
}
