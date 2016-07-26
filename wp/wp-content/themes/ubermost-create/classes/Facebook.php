<?php

namespace UbermostCreate;

/**
 * Class for setting up Facebook connection.
 */
class Facebook
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

  public function isConfigured()
  {
    return (bool) $this->data['app_id'] && $this->data['app_secret'];
  }

  public function isConnected()
  {
    return (bool) $this->data['oauth_token'];
  }

  public function isAuthorizing()
  {
    return (bool) isset($_GET['code']) && isset($_GET['state']);
  }

  public function getData()
  {
    return $this->data;
  }

  public function setup()
  {
    $this->data = [
      'app_id' => get_field('facebook_app_id', 'option'),
      'app_secret' => get_field('facebook_app_secret', 'option'),
      'oauth_token' => get_field('facebook_oauth_token', 'option'),
    ];

    $this->client = new \Facebook\Facebook([
      'app_id' => $this->data['app_id'],
      'app_secret' => $this->data['app_secret'],
      'default_graph_version' => 'v2.7',
    ]);
  }

  public function generateConnectURL()
  {
    $helper = $this->client->getRedirectLoginHelper();
    $loginUrl = $helper->getLoginUrl(
      admin_url('tools.php?page=publisher'),
      ['email']
    );

    return $loginUrl;
  }

  public function authorize()
  {
    $helper = $this->client->getRedirectLoginHelper();

    try {
      $accessToken = $helper->getAccessToken();
    } catch (Facebook\Exceptions\FacebookResponseException $e) {
      echo 'Graph returned an error: '.$e->getMessage();
      exit;
    } catch (Facebook\Exceptions\FacebookSDKException $e) {
      echo 'Facebook SDK returned an error: '.$e->getMessage().'.';
      exit;
    }

    if ( ! isset($accessToken)) {
      if ($helper->getError()) {
        header('HTTP/1.0 401 Unauthorized');
        echo 'Error: '.$helper->getError()."\n";
        echo 'Error Code: '.$helper->getErrorCode()."\n";
        echo 'Error Reason: '.$helper->getErrorReason()."\n";
        echo 'Error Description: '.$helper->getErrorDescription()."\n";
      } else {
        header('HTTP/1.0 400 Bad Request');
        echo 'Bad request';
      }
      exit;
    }

    // The OAuth 2.0 client handler helps us manage access tokens
    $oAuth2Client = $this->client->getOAuth2Client();
    $tokenMetadata = $oAuth2Client->debugToken($accessToken);
    $tokenMetadata->validateAppId($this->data['app_id']);
    $tokenMetadata->validateExpiration();

    // Exchanges a short-lived access token for a long-lived one
    if ( ! $accessToken->isLongLived()) {
      try {
        $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
      } catch (Facebook\Exceptions\FacebookSDKException $e) {
        echo '<p>Error getting long-lived access token: '.$helper->getMessage()."</p>\n\n";
        exit;
      }
    }

    update_field('facebook_oauth_token', (string) $accessToken, 'option');

    $this->setup();
  }

  public function getUserData()
  {
    return $this->client->get('/me?fields=id,name', $this->data['oauth_token'])
      ->getGraphUser();
  }
}
