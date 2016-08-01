<?php

namespace UbermostCreate\API;

use Timber;
use UbermostCreate\API as AbstractAPI;

/**
 * Class for setting up Facebook connection.
 */
class Facebook extends AbstractAPI
{
  public function isConfigured()
  {
    return (bool) $this->data['app_id'] && $this->data['app_secret'] && $this->data['page_id'];
  }

  public function isConnected()
  {
    return (bool) $this->data['oauth_token'];
  }

  public function isAuthorizing()
  {
    return (bool) isset($_GET['code']) && isset($_GET['state']);
  }

  protected function setup()
  {
    $this->data = [
      'app_id' => get_field('facebook_app_id', 'option'),
      'app_secret' => get_field('facebook_app_secret', 'option'),
      'oauth_token' => get_field('facebook_oauth_token', 'option'),
      'page_id' => get_field('facebook_page_id', 'option'),
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
      ['email', 'manage_pages', 'publish_pages', 'status_update']
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

  public function compilePost($postId)
  {
    $post = get_post($postId);

    return [
      'name' => $post->post_title,
      'link' => get_field('blog_link', $post->ID),
      'caption' => $post->post_content,
      'message' => Timber::compile('admin/api/facebook.twig', ['id' => $postId]),
    ];
  }

  public function publishPost($postId)
  {
    $pageAccessToken = $this->client
      ->get(
        '/'.$this->data['page_id'].'/?fields=access_token',
        $this->data['oauth_token']
      )
      ->getDecodedBody();

    return $this->client
      ->post('/'.$this->data['page_id'].'/feed', array_merge(
        $this->compilePost($postId),
        ['access_token' => $pageAccessToken['access_token']]
      ))
      ->getGraphObject()
      ->asArray();
  }

  public function getPagesList()
  {
    $result = $this->client
      ->get('/me/accounts', $this->data['oauth_token'])
      ->getDecodedBody();

    return $result['data'];
  }
}
