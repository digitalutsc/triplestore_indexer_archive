<?php

namespace Drupal\triplestore_indexer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\advancedqueue\Entity\Queue;
use Drupal\advancedqueue\Job;

/**
 * Class TripleStoreIndexerConfigForm.
 */
class TripleStoreIndexerConfigForm extends ConfigFormBase
{

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames()
  {
    return [
      'triplestore_indexer.triplestoreindexerconfig',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'triplestore_indexer_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $config = $this->config('triplestore_indexer.triplestoreindexerconfig');

    $form['container'] = array(
      '#type' => 'container',
    );

    $form['container']['triplestore-server-config'] = array(
      '#type' => 'details',
      '#title' => 'General Settings',
      '#open' => true
    );
    $form['container']['triplestore-server-config']['server-url'] = array(
      '#type' => 'textfield',
      '#name' => 'server-url',
      '#title' => $this
        ->t('Server URL:'),
      '#required' => TRUE,
      '#default_value' => ($config->get("server-url") !== null) ? $config->get("server-url") : ""
    );
    $form['container']['triplestore-server-config']['namespace'] = array(
      '#type' => 'textfield',
      '#title' => $this
        ->t('Namespace:'),
      '#required' => TRUE,
      '#default_value' => ($config->get("namespace") !== null) ? $config->get("namespace") : ""
    );

    $form['container']['triplestore-server-config']['select-auth-method'] = [
      '#type' => 'select',
      '#title' => $this->t('Select method of authentication:'),
      '#options' => [
        '-1' => 'None',
        'digest' => 'Digest',
        'oauth' => 'OAuth',
      ],
      '#ajax' => [
        'wrapper' => 'questions-fieldset-wrapper',
        'callback' => '::promptCallback',
      ],
      '#default_value' => ($config->get("method-of-auth") !== null) ? $config->get("method-of-auth") : ""
    ];

    $form['container']['triplestore-server-config']['auth-config'] = [
      '#type' => 'details',
      '#title' => $this->t('Authentication Infomation:'),
      '#open' => TRUE,
      '#attributes' => ['id' => 'questions-fieldset-wrapper'],
    ];
    $form['container']['triplestore-server-config']['auth-config']['question'] = [
      '#markup' => $this->t('None.'),
    ];


    $question_type = ($config->get("method-of-auth") !== null && !isset($form_state->getValues()['select-auth-method'])) ? $config->get("method-of-auth") : $form_state->getValues()['select-auth-method'];

    if (!empty($question_type) && $question_type !== -1) {
      unset($form['container']['triplestore-server-config']['auth-config']['question']);
      switch ($question_type) {
        case 'digest':
        {
          $form['container']['triplestore-server-config']['auth-config']['admin-username'] = array(
            '#type' => 'textfield',
            '#title' => $this
              ->t('Username:'),
            '#required' => TRUE,
            '#default_value' => ($config->get("admin-username") !== null) ? $config->get("admin-username") : ""
          );
          $form['container']['triplestore-server-config']['auth-config']['admin-password'] = array(
            '#type' => 'password',
            '#title' => $this
              ->t('Password:'),
            '#required' => TRUE,
            '#attributes' => ['value' => ($config->get('admin-password') !== null) ? $config->get('admin-password') : "", 'readonly' => ($config->get('admin-password') !== null) ? 'readonly' : false],
            '#description' => $this->t('To reset the password, change Method of authentication to None first.')
          );

          break;
        }
        case 'oauth':
        {
          $form['container']['triplestore-server-config']['auth-config']['client-id'] = array(
            '#type' => 'textfield',
            '#title' => $this
              ->t('Client ID:'),
            '#required' => TRUE,
            '#default_value' => ($config->get("client_id") !== null) ? $config->get("client_id") : "",
            '#description' => $this->t('To reset the Client ID, change Method of authentication to None first.')
          );
          $form['container']['triplestore-server-config']['auth-config']['client-secret'] = array(
            '#type' => 'textfield',
            '#title' => $this
              ->t('Client Secret:'),
            '#required' => TRUE,
            '#default_value' => ($config->get("client-secret") !== null) ? $config->get("client-secret") : "",
            '#description' => $this->t('To reset the Client Secret, change Method of authentication to None first.')
          );
          break;
        }
        default:
          $form['container']['triplestore-server-config']['auth-config']['question'] = [
            '#markup' => $this->t('None.'),
          ];
          break;
      }
    }

    $form['container']['triplestore-server-config']['advancedqueue-id'] = array(
      '#type' => 'textfield',
      '#name' => 'advancedqueue-id',
      '#title' => $this
        ->t('Queue:'),
      '#required' => TRUE,
      '#default_value' => ($config->get("advancedqueue-id") !== null) ? $config->get("advancedqueue-id") : "default",
      '#description' => $this->t('<mark>Please enter <strong>machine name</strong> of the queue. To create or further detail of Advanced Queues, <a href="/admin/config/system/queues">Click here</a></mark>')
    );

    $form['configuration'] = array(
      '#type' => 'vertical_tabs',
    );
    $form['configuration']['#tree'] = true;


    $form['events'] = array(
      '#type' => 'details',
      '#title' => $this
        ->t('Triggered for Events (Nat)'),
      '#group' => 'configuration',
    );

    $form['events']['select-when'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Set indexing when:'),
      '#options' => array(
        'created' => t('When Content created'),
        'updated' => t('When content updated'),
        'deleted' => t('When content deleted.'),
      ),
      '#default_value' => array_keys(array_filter($config->get('events-to-index'))),
    );

    $form['content-type'] = array(
      '#type' => 'details',
      '#title' => $this
        ->t('Content Type'),
      '#group' => 'configuration',
    );

    // pull list of exsiting content types of the site
    $content_types = \Drupal::entityTypeManager()
      ->getStorage('node_type')
      ->loadMultiple();
    $options_contentypes = array();
    foreach ($content_types as $ct) {
      $options_contentypes[$ct->id()] = $ct->label();
    }

    $form['content-type']['select-content-types'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Which content type to be indexed:'),
      '#options' => $options_contentypes,
      '#default_value' => array_keys(array_filter($config->get('content-type-to-index'))),
    );

    $vocabularies = \Drupal\taxonomy\Entity\Vocabulary::loadMultiple();
    $options_taxonomy = array();
    foreach ($vocabularies as $vocal) {
      $options_taxonomy[$vocal->id()] = $vocal->label();
    }
    $form['taxonomy'] = array(
      '#type' => 'details',
      '#title' => $this
        ->t('Taxonomy'),
      '#group' => 'configuration',
    );
    $form['taxonomy']['select-vocabulary'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Which vocabulary to be indexed:'),
      '#options' => $options_taxonomy,
      '#default_value' => array_keys(array_filter($config->get('taxonomy-to-index'))),
    );

    $form['submit-save-config'] = array(
      '#type' => 'submit',
      '#name' => "submit-save-server-config",
      '#value' => "Save Configuration",
      '#attributes' => ['class' => ["button button--primary"]],
    );

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    // validate Server URL
    try {
      $client = \Drupal::service('http_client');
      // Get articles from the API.
      $response = $client->request('GET', $form_state->getValues()['server-url']);

      if ($response->getStatusCode() !== 200) {
        $form_state->setErrorByName("server-url",
          t('Your Server URL is not valid, please check it again.'));
      }
    }
    catch(\Exception $e) {
      $form_state->setErrorByName("server-url",
        t('Your Server URL is not valid, please check it again. <strong>Error message:</strong> '. $e->getMessage()));
    }


    // validate if entering a valid machine name of queue
    $q = Queue::load($form_state->getValues()['advancedqueue-id']);
    if (!isset($q)) {
      $form_state->setErrorByName("advancedqueue-id",
        t('This queue\'s machine name "' . $form_state->getValues()['advancedqueue-id'] . '" is not valid, please verify it by <a href="/admin/config/system/queues">clicking here</a>.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $configFactory = $this->configFactory->getEditable('triplestore_indexer.triplestoreindexerconfig');

    $configFactory->set('server-url', $form_state->getValues()['server-url'])
      ->set('namespace', $form_state->getValues()['namespace'])
      ->set('method-of-auth', $form_state->getValues()['select-auth-method']);
    switch ($form_state->getValues()['select-auth-method']) {
      case 'digest':
      {
        $configFactory->set('admin-username', $form_state->getValues()['admin-username']);
        if ($configFactory->get('admin-password') === null) {

          //$service = \Drupal::service('triplestore_indexer.indexing');
          $configFactory->set('admin-password', base64_encode($form_state->getValues()['admin-password']));
        }

        $configFactory->set('client-id', null);
        $configFactory->set('client-secret', null);

        break;
      }
      case 'oauth':
      {
        $configFactory->set('client-id', $form_state->getValues()['client-id']);
        $configFactory->set('client-secret', $form_state->getValues()['client-secret']);
        $configFactory->set('admin-username', null);
        $configFactory->set('admin-password', null);
        break;
      }
      default:
      {
        $configFactory->set('client-id', null);
        $configFactory->set('client-secret', null);
        $configFactory->set('admin-username', null);
        $configFactory->set('admin-password', null);
        break;
      }
    }
    $configFactory->set('events-to-index', $form_state->getValues()['select-when']);
    $configFactory->set('advancedqueue-id', $form_state->getValues()['advancedqueue-id']);
    $configFactory->set('content-type-to-index', $form_state->getValues()['select-content-types']);
    $configFactory->set('taxonomy-to-index', $form_state->getValues()['select-vocabulary']);
    $configFactory->save();

    parent::submitForm($form, $form_state);

  }

  /**
   * For Ajax callback for depending dropdown list
   *
   * @param array $form
   * @param FormStateInterface $form_state
   * @return mixed
   */
  public function promptCallback(array $form, FormStateInterface $form_state)
  {
    return $form['container']['triplestore-server-config']['auth-config'];
  }

}
