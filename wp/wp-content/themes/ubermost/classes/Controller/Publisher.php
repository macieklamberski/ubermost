<?php

namespace Ubermost\Controller;

use Ubermost\API\Buffer;
use Ubermost\API\Tumblr;
use Ubermost\Controller;
use Ubermost\API\Twitter;
use Ubermost\API\Facebook;

class Publisher extends Controller
{
    /**
     * Main method for handling the logic.
     */
    public function run()
    {
        $APIs = [
            'tumblr' => new Tumblr(),
            'twitter' => new Twitter(),
            'facebook' => new Facebook(),
            'buffer' => new Buffer(),
        ];

        foreach ($APIs as $api) {
            if ($api->isAuthorizing()) {
                $api->authorize();
            }
        }

        $this->render('admin/publisher', $APIs);
    }
}
