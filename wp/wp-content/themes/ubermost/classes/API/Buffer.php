<?php

namespace Ubermost\API;

use Timber;
use Buffer\App\BufferApp;
use Ubermost\API as AbstractAPI;

/**
 * Class for setting up Buffer connection.
 */
class Buffer extends AbstractAPI
{
  public function isEnabled()
  {
    return (bool) $this->data['enabled'];
  }

  public function isConfigured()
  {
    return (bool) $this->data['client_id'] && $this->data['client_secret'];
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
      'enabled' => get_field('buffer_enabled', 'option'),
      'client_id' => get_field('buffer_client_id', 'option'),
      'client_secret' => get_field('buffer_client_secret', 'option'),
      'oauth_token' => get_field('buffer_oauth_token', 'option'),
      'profile_id' => get_field('buffer_profile_id', 'option'),
    ];

    $_SESSION['oauth']['buffer']['access_token'] = $this->data['oauth_token'];

    $this->client = new BufferApp(
      $this->data['client_id'],
      $this->data['client_secret'],
      admin_url('tools.php?page=publisher')
    );
  }

  public function generateConnectURL()
  {
    return $this->client->get_login_url();
  }

  public function authorize()
  {
    $this->setup();
  }

  public function getUserData()
  {
    return $this->client->go('/user');
  }

  public function compilePost($postId)
  {
    $post = get_post($postId);

    return [
      'photo' => get_field('blog_image', $post->ID),
      'content' => Timber::compile('admin/api/buffer.twig', ['id' => $postId]),
    ];
  }

  public function publishPost($postId)
  {
    $data = $this->compilePost($postId);

    return $this->client->go('/updates/create', [
      'profile_ids[]' => $this->data['profile_id'],
      'text' => $data['content'],
      'now' => true,
      'media[photo]' => $data['photo'],
    ]);
  }

  public function getProfilesList()
  {
    return $this->client->go('/profiles');
  }
}
