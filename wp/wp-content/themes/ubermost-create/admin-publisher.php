<?php

$tumblr = new UbermostCreate\API\Tumblr();
$twitter = new UbermostCreate\API\Twitter();
$facebook = new UbermostCreate\API\Facebook();

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

Timber::render('admin/publisher.twig', $context);
