<?php

namespace Ubermost;

/**
 * Base class for running the application.
 */
class Application
{
    /**
     * Setup all the hooks.
     */
    public function setup(array $hooks)
    {
        session_start();

        foreach ($hooks as $hook) {
            $hook->register();
        }
    }
}
