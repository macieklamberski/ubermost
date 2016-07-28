<?php

namespace UbermostCreate\API;

use Tumblr\API\Client;
use UbermostCreate\API as AbstractAPI;

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
    return (bool) isset($_GET['oauth_token']) && isset($_GET['oauth_verifier']);
  }

  protected function setup()
  {
    $this->data = [
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
    return 'https://www.tumblr.com/oauth/authorize?oauth_token='.$token['oauth_token'];
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

  public function publishPost()
  {
    // fsdfsd
    // fsdfsd
  }
}
