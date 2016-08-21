<?php

namespace Ubermost\Controller;

use Ubermost\Controller;

class Publisher extends Controller
{
    /**
     * Main method for handling the logic.
     */
    public function run()
    {
        $APIs = [
            'tumblr' => new Ubermost\API\Tumblr(),
            'twitter' => new Ubermost\API\Twitter(),
            'facebook' => new Ubermost\API\Facebook(),
            'buffer' => new Ubermost\API\Buffer(),
        ];

        foreach ($APIs as $api) {
            if ($api->isAuthorizing()) {
                $api->authorize();
            }
        }

        $this->render('admin/publisher', $APIs);
    }
}
