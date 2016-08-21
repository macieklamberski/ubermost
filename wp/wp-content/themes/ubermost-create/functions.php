<?php
/**
 * Include features initialization mechanism.
 */
require_once dirname(__FILE__).'/vendor/autoload.php';

/**
 * Register all declared custom actions and filters.
 */
(new UbermostCreate\Application())->setup([
  new UbermostCreate\Hooks\Common(),
  new UbermostCreate\Hooks\CPTs(),
  new UbermostCreate\Hooks\AJAX(),
]);
