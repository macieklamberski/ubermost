<?php

namespace Ubermost;

use Timber;

/**
 * Base class for all controllers.
 */
abstract class Controller
{
    /**
     * Main method for handling the logic.
     */
    abstract public function run();

    /**
     * Helper method for rendering the view.
     */
    protected function render(string $view, array $data)
    {
        $context = Timber::get_context();

        foreach ($data as $key => $value) {
            $context[$key] = $value;
        }

        Timber::render('views/'.$view.'.twig', $context);
    }
}
