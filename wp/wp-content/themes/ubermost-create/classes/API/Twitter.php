<?php

namespace UbermostCreate\API;

use Abraham\TwitterOAuth\TwitterOAuth;
use UbermostCreate\API as AbstractAPI;

/**
 * Class for setting up Twitter connection.
 */
class Twitter extends AbstractAPI
{
  /**
   * Storing Twitter request handler.
   */
  protected $request;

  /**
   * Get request token.
   */
  protected function getRequestToken()
  {
    $data = $_SESSION['twitter_request_token'];

    if (empty($data)) {
      $data = $this->client->oauth('oauth/request_token', []);
    }

    $_SESSION['twitter_request_token'] = $data;

    return $data;
  }

  protected function clearRequestToken()
  {
    $_SESSION['twitter_request_token'] = [];
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
      'consumer_key' => get_field('twitter_consumer_key', 'option'),
      'consumer_secret' => get_field('twitter_consumer_secret', 'option'),
      'oauth_token' => get_field('twitter_oauth_token', 'option'),
      'oauth_token_secret' => get_field('twitter_oauth_token_secret', 'option'),
    ];

    $this->client = new TwitterOAuth(
      $this->data['consumer_key'],
      $this->data['consumer_secret'],
      $this->data['oauth_token'],
      $this->data['oauth_token_secret']
    );
  }

  public function generateConnectURL()
  {
    $token = $this->getRequestToken();
    $query = [
      'oauth_token' => $token['oauth_token'],
      'callback_url' => admin_url('tools.php?page=publisher'),
    ];

    return 'https://www.twitter.com/oauth/authorize?'.http_build_query($query);
  }

  public function authorize()
  {
    $token = $this->getRequestToken();
    $this->client->setOauthToken(
      $token['oauth_token'],
      $token['oauth_token_secret']
    );
    $this->clearRequestToken();

    $data = $this->client->oauth('oauth/access_token', ['oauth_verifier' => $_GET['oauth_verifier']]);

    update_field('twitter_oauth_token', $data['oauth_token'], 'option');
    update_field('twitter_oauth_token_secret', $data['oauth_token_secret'], 'option');

    $this->setup();
  }

  public function getUserData()
  {
    return $this->client->get('account/verify_credentials', []);
  }

  public function publishPost()
  {
    // fsdfsd
    // fsdfsd
  }
}
