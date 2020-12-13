<?php

namespace Drupal\triplestore_indexer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

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
    //return parent::buildForm($form, $form_state);

    //$form = parent::buildForm($form, $form_state);

    $form['container'] = array(
      '#type' => 'container',
    );

    $form['container']['triplestore-server-config'] = array(
      '#type' => 'details',
      '#title' => 'General Settings',
      '#open' => true
    );
    $form['container']['triplestore-server-config']['url'] = array(
      '#type' => 'textfield',
      '#title' => $this
        ->t('Server URL:'),
      '#required' => TRUE,
      //'#default_value' => ($config->get("client_id") !== null) ? $config->get("client_id") : ""
    );
    $form['container']['triplestore-server-config']['namespace'] = array(
      '#type' => 'textfield',
      '#title' => $this
        ->t('Namespace:'),
      '#required' => TRUE,
      //'#default_value' => ($config->get("client_id") !== null) ? $config->get("client_id") : ""
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
    ];

    $form['container']['triplestore-server-config']['questions_fieldset'] = [
      '#type' => 'details',
      '#title' => $this->t('Authentication Infomation:'),
      '#open' => TRUE,
      '#attributes' => ['id' => 'questions-fieldset-wrapper'],
    ];


    $question_type = $form_state->getValues()['select-auth-method'];

    if (!empty($question_type) && $question_type !== -1) {

      $form['container']['triplestore-server-config']['questions_fieldset']['question'] = [
        '#markup' => $this->t('None.'),
      ];
      switch ($question_type) {
        case 'digest':
        {
          $form['container']['triplestore-server-config']['questions_fieldset']['admin-username'] = array(
            '#type' => 'textfield',
            '#title' => $this
              ->t('Username:'),
            '#required' => TRUE,
            //'#default_value' => ($config->get("client_id") !== null) ? $config->get("client_id") : ""
          );
          $form['container']['triplestore-server-config']['questions_fieldset']['admin-password'] = array(
            '#type' => 'textfield',
            '#title' => $this
              ->t('Password:'),
            '#required' => TRUE,
            //'#default_value' => ($config->get("client_id") !== null) ? $config->get("client_id") : ""
          );

          break;
        }
        case 'oauth':
        {
          $form['container']['triplestore-server-config']['questions_fieldset']['client-id'] = array(
            '#type' => 'textfield',
            '#title' => $this
              ->t('Client ID:'),
            '#required' => TRUE,
            //'#default_value' => ($config->get("client_id") !== null) ? $config->get("client_id") : ""
          );
          $form['container']['triplestore-server-config']['questions_fieldset']['client-secret'] = array(
            '#type' => 'textfield',
            '#title' => $this
              ->t('Client Secret:'),
            '#required' => TRUE,
            //'#default_value' => ($config->get("client_id") !== null) ? $config->get("client_id") : ""
          );
          break;
        }
        default:

          break;
      }
    }





    $form['container']['triplestore-server-config']['submit-save-config'] = array(
      '#type' => 'submit',
      '#name' => "submit-save-server-config",
      '#value' => "Save",
      '#attributes' => ['class' => ["button button--primary"]],
      //'#submit' => array([$this, 'submitForm'])
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
      //'#default_value' => variable_get( 'options', array('key1', 'key3') ),
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

    $form['content-type']['selecht-which'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Which content type to be indexed:'),
      '#options' => $options_contentypes
      //'#default_value' => variable_get( 'options', array('key1', 'key3') ),
    );


    $form['taxonomy'] = array(
      '#type' => 'details',
      '#title' => $this
        ->t('Taxonomy'),
      '#group' => 'configuration',
    );
    $form['taxonomy']['select-which'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Set indexing when:'),
      '#options' => array(
        'content-type-1' => t('content-type-1'),
        'content-type-2' => t('content-type-1'),
        'content-type-3' => t('content-type-1.'),
      ),
      //'#default_value' => variable_get( 'options', array('key1', 'key3') ),
    );


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    parent::submitForm($form, $form_state);

    $this->config('triplestore_indexer.triplestoreindexerconfig')
      ->save();
  }

  public function promptCallback(array $form, FormStateInterface $form_state)
  {
    print_log(promptCallback);
    return $form['container']['triplestore-server-config']['questions_fieldset'];
  }

}
