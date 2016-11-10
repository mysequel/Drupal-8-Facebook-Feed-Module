<?php

/**
 * @file
 * Contains \Drupal\facebook_page\Plugin\Block\FacebookPageBlock.
 */

namespace Drupal\facebook_page\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Url;
use Facebook\Facebook as Facebook;
use Facebook\FacebookRequest as FacebookRequest;
/**
 * Provides an Facebook Page block.
 *
 * @Block(
 *   id = "facebook_page_block",
 *   admin_label = @Translation("Facebook Page block"),
 *   category = @Translation("Social")
 * )
 */
class FacebookPageBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The HTTP client to fetch the feed data with.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Constructs a InstagramBlockBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \GuzzleHttp\Client $http_client
   *   The Guzzle HTTP client.
   * @param ConfigFactory $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, Client $http_client, ConfigFactory $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->httpClient = $http_client;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'cache_time_minutes' => 1440
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form['cache_time_minutes'] = array(
      '#type' => 'number',
      '#title' => t('Cache time in minutes'),
      '#description' => t("Default is 1440 - 24 hours. This is important for performance reasons and so the Instagram API limits isn't reached on busy sites."),
      '#default_value' => $this->configuration['cache_time_minutes'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    if ($form_state->hasAnyErrors()) {
      return;
    }
    else {
     $this->configuration['cache_time_minutes'] = $form_state->getValue('cache_time_minutes');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Build a render array to return the Instagram Images.
    $build = array();
    $module_config = $this->configFactory->get('facebook_page.settings')->get();

    // If no configuration was saved, don't attempt to build block.
    if (empty($module_config['app_id']) || empty($module_config['app_secret']) 
          || empty($module_config['default_graph_version'])) {
      // @TODO Display a message instructing user to configure module.
      return $build;
    }
    
    $fb = new Facebook([
      'app_id' => $module_config['app_id'],
      'app_secret' => $module_config['app_secret'],
      'default_graph_version' => $module_config['default_graph_version'],
      'default_access_token' => (string) $module_config['access_token']
    ]);

   $request = $fb->request('GET', '/'.$module_config['page_id'].'/feed');

   try {
      $response = $fb->getClient()->sendRequest($request);
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      // When Graph returns an error
      echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
      // When validation fails or other local issues
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }

    $result = $response->getBody();

  
    if (!$result) {
      return $build;
    }
    
    $result = json_decode($result);

    foreach ($result->data as $key => $post) {
      if (isset($post->message)) {
        $build['children'][$post->id] = array(
          '#theme' => 'facebook_page_post',
          '#data' => $post->message,
          '#text' => $post->message
        );
      }
    }

    // Add css.
    if (!empty($build)) {
      $build['#attached']['library'][] = 'facebook_page/facebook_page';
    }

    // Cache for a day.
    $build['#cache']['keys'] = ['facebook_page', 'block'];
    $build['#cache']['max_age'] = $this->configuration['cache_time_minutes'] * 60;

    return $build;
  }

}
