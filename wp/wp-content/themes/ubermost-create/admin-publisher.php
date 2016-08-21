<?php

$tumblr = new Ubermost\API\Tumblr();
$twitter = new Ubermost\API\Twitter();
$facebook = new Ubermost\API\Facebook();
$buffer = new Ubermost\API\Buffer();

if ($buffer->isAuthorizing()) {
  $buffer->authorize();
}

if ($tumblr->isAuthorizing()) {
  $tumblr->authorize();
}

if ($twitter->isAuthorizing()) {
  $twitter->authorize();
}

if ($facebook->isAuthorizing()) {
  $facebook->authorize();
}

$context = Timber::get_context();
$context['tumblr'] = $tumblr;
$context['twitter'] = $twitter;
$context['facebook'] = $facebook;
$context['buffer'] = $buffer;

Timber::render('admin/publisher.twig', $context);
