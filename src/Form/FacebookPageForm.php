<?php

/**
 * @file
 * Contains \Drupal\facebook_page\Form\FacebookPageForm.
 */

namespace Drupal\facebook_page\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;


/**
 * Configure facebook_page settings for this site.
 */
class FacebookPageForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'facebook_page_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['facebook_page.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get module configuration.
    $config = $this->config('facebook_page.settings');

    $form['authorise'] = array(
      '#markup' => t('Facebook Page Block requires connecting to a specific Facebook App. You need to create an app before use this module'),
    );

    $form['app_id'] = array(
      '#type' => 'textfield',
      '#title' => t('App Id'),
      '#description' => t('Your app ID'),
      '#default_value' => $config->get('app_id'),
    );

    $form['app_secret'] = array(
      '#type' => 'textfield',
      '#title' => t('App Secret'),
      '#description' => t('Your app secret value'),
      '#default_value' => $config->get('app_secret'),
    );

    $form['access_token'] = array(
      '#type' => 'textfield',
      '#title' => t('Access Token'),
      '#maxlength' => 180,
      '#description' => t('Your facebook access token, get your access token <a href="@link">Here</a>', array('@link' => 'https://developers.facebook.com/tools/debug/accesstoken')),
      '#default_value' => $config->get('access_token'),
    );

    $form['default_graph_version'] = array(
      '#type' => 'textfield',
      '#title' => t('Facebook Graph Version'),
      '#description' => t('Enter Facebook Graph version'),
      '#default_value' => $config->get('default_graph_version'),
    );

    $form['page_id'] = array(
      '#type' => 'textfield',
      '#title' => t('Page ID'),
      '#description' => t('Page ID, eg: your-page-name '),
      '#default_value' => $config->get('page_id'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $app_id = $form_state->getValue('app_id');
    $app_secret = $form_state->getValue('app_secret');
    $access_token = $form_state->getValue('access_token');
    $default_graph_version = $form_state->getValue('default_graph_version');
    $page_id = $form_state->getValue('page_id');

    // Get module configuration.
    $this->config('facebook_page.settings')
      ->set('app_id', $app_id)
      ->set('app_secret', $app_secret)
      ->set('access_token', $access_token)
      ->set('default_graph_version', $default_graph_version)
      ->set('page_id', $page_id)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
