<?php

namespace Ubermost\API;

use Timber;
use Ubermost\Helper;
use Tumblr\API\Client;
use Ubermost\API as AbstractAPI;

/**
 * Class for setting up Tumblr connection.
 */
class Tumblr extends AbstractAPI
{
    /**
     * Storing Tumblr request handler.
     */
    protected $request;

    /**
     * Get request token.
     */
    protected function getRequestToken()
    {
        $data = $_SESSION['tumblr_request_token'];

        if (empty($data)) {
            $data = $this->request
                ->request('POST', 'oauth/request_token', [])
                ->body->__toString();

            parse_str($data, $data);
        }

        $_SESSION['tumblr_request_token'] = $data;

        return $data;
    }

    protected function clearRequestToken()
    {
        $_SESSION['tumblr_request_token'] = [];
    }

    public function isEnabled()
    {
        return (bool) $this->data['enabled'];
    }

    public function isConfigured()
    {
        return (bool) $this->data['consumer_key'] && $this->data['consumer_secret'];
    }

    public function isConnected()
    {
        return (bool) $this->data['oauth_token'] && $this->data['oauth_token_secret'];
    }

    public function isAuthorizing()
    {
        if ($this->isConnected()) {
            return false;
        }

        return (bool) isset($_GET['oauth_token']) && isset($_GET['oauth_verifier']);
    }

    protected function setup()
    {
        $this->data = [
            'enabled' => get_field('tumblr_enabled', 'option'),
            'consumer_key' => get_field('tumblr_key', 'option'),
            'consumer_secret' => get_field('tumblr_secret', 'option'),
            'oauth_token' => get_field('tumblr_oauth_token', 'option'),
            'oauth_token_secret' => get_field('tumblr_oauth_token_secret', 'option'),
        ];

        $this->client = new Client(
            $this->data['consumer_key'],
            $this->data['consumer_secret'],
            $this->data['oauth_token'],
            $this->data['oauth_token_secret']
        );

        $this->request = $this->client->getRequestHandler();
        $this->request->setBaseUrl('https://www.tumblr.com');
    }

    public function generateConnectURL()
    {
        $token = $this->getRequestToken();
        $query = [
            'oauth_token' => $token['oauth_token'],
            'callback_url' => admin_url('tools.php?page=publisher'),
        ];

        return 'https://www.tumblr.com/oauth/authorize?'.http_build_query($query);
    }

    public function authorize()
    {
        $token = $this->getRequestToken();
        $this->client->setToken(
            $token['oauth_token'],
            $token['oauth_token_secret']
        );
        $this->clearRequestToken();

        $data = $this->request
            ->request('POST', 'oauth/access_token', ['oauth_verifier' => $_GET['oauth_verifier']])
            ->body->__toString();

        parse_str($data, $data);

        update_field('tumblr_oauth_token', $data['oauth_token'], 'option');
        update_field('tumblr_oauth_token_secret', $data['oauth_token_secret'], 'option');

        $this->setup();
    }

    public function getUserData()
    {
        $this->request->setBaseUrl('https://api.tumblr.com/v2');

        $response = $this->request
            ->request('GET', 'user/info', [])
            ->body->__toString();

        return json_decode($response)->response->user;
    }

    public function compilePost($postId)
    {
        $post = get_post($postId);

        $color = get_field('main_color', $post->ID) ?: get_field('default_color', 'option');
        $color = get_post($color);

        $size = get_field('tumblr_size', 'option');
        $size = get_post($size);

        if ( ! $post || ! $color || ! $size) {
            return [];
        }

        $imageUrl = Helper::get_file_link('show', $post->ID, $color->ID, $size->ID);
        $tags = get_the_tags($post->ID);

        if ($imageUrl) {
            $imageSource = file_get_contents($imageUrl);
            $imageSource = base64_encode($imageSource);
        }

        if ($tags) {
            $tags = array_map(function ($tag) {return $tag->slug;}, $tags);
        }

        if (get_field('source_type', $post->ID) == 'book') {
            $book = get_field('book', $post->ID);
            $isbn = get_field('isbn_10', $book);

            if ($isbn) {
                $tags[] = 'isbn'.$isbn;
            }
        }

        if (is_array($tags)) {
            $tags = implode(',', $tags);
        } else {
            $tags = '';
        }

        return [
            'type' => 'photo',
            'format' => 'html',
            'tags' => $tags,
            'data64' => $imageSource,
            'caption' => Timber::compile('admin/api/tumblr.twig', ['id' => $postId]),
        ];
    }

    public function publishPost($postId)
    {
        $this->request->setBaseUrl('https://api.tumblr.com/v2');

        $createdPost = $this->request
            ->request('POST', 'blog/ubermost/post', $this->compilePost($postId))
            ->body->__toString();

        $createdPost = json_decode($createdPost);

        if ($createdPost->meta->status !== 201) {
            return false;
        }

        $latestPost = $this->request
            ->request('GET', 'blog/ubermost/posts', [
                'id' => $createdPost->response->id,
            ])
            ->body->__toString();

        return json_decode($latestPost)->response->posts[0];
    }
}
