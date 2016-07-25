<?php

$tumblr = new UbermostCreate\Tumblr();

if ($tumblr->isAuthorizing()) {

  $tumblr->authorize();

  print_r($tumblr->getPosts());

} else if ($tumblr->isConnected()) {

  print_r($tumblr->getPosts());

} else {

  var_dump($tumblr->generateConnectURL());

}

die;

// Timber::render('admin/publisher.twig', $context);
