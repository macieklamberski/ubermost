<?php

$context = Timber::get_context();
$context['environment'] = 'development';

Timber::render('views/pages/404.twig', $context);
