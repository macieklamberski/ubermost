<?php

$tumblr = new UbermostCreate\API\Tumblr();
$facebook = new UbermostCreate\API\Facebook();

if ($tumblr->isAuthorizing()) {
  $tumblr->authorize();
}

if ($facebook->isAuthorizing()) {
  $facebook->authorize();
}

$context = Timber::get_context();
$context['tumblr'] = $tumblr;
$context['facebook'] = $facebook;

Timber::render('admin/publisher.twig', $context);
