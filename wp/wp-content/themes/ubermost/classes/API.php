<?php

namespace Ubermost;

/**
 * Base class for APIs.
 */
abstract class API
{
    /**
     * Storing API keys, etc.
     */
    protected $data;

    /**
     * Storing Tumblr API client.
     */
    protected $client;

    /**
     * Constructor, biatch.
     */
    public function __construct()
    {
        $this->setup();
    }

    public function getData()
    {
        return $this->data;
    }

    abstract protected function setup();
    abstract public function isEnabled();
    abstract public function isConfigured();
    abstract public function isConnected();
    abstract public function isAuthorizing();
    abstract public function generateConnectURL();
    abstract public function authorize();
    abstract public function getUserData();
    abstract public function publishPost($postId);
}
