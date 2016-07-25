<?php

$tumblr = new UbermostCreate\Tumblr();

if ($tumblr->isAuthorizing()) {
  $tumblr->authorize();
}

$context = Timber::get_context();
$context['tumblr'] = $tumblr;

Timber::render('admin/publisher.twig', $context);
