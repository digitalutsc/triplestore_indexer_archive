<?php

namespace Drupal\triplestore_indexer\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Class DefaultForm.
 */
class DefaultForm extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'default_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $form['triple'] = array(
      '#type' => 'textarea',
      '#title' => 'Triple: ',
    );

    $form['submit-insert'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    /*$form['submit-update'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update'),
    ];
    $form['submit-delete'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
    ];*/


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      \Drupal::messenger()->addMessage($key . ': ' . ($key === 'text_format' ? $value['value'] : $value));
    }

    try {
      //curl -D- -H 'Content-Type: application/json' --upload-file collection.json -X POST 'http://localhost:80/bigdata/sparql?context-uri=http://digital.utsc.localhost/collection'

      //curl -i -XPOST -d "PREFIX foaf: <http://xmlns.com/foaf/0.1/> INSERT DATA { <https://www.manutd.com> foaf:is [ foaf:name \"manchester united\" ] } " -H "Content-type: application/sparql-update" http://localhost:8080/bigdata/namespace/Test/sparql


      $data = "update=PREFIX foaf: <http://xmlns.com/foaf/0.1/> INSERT DATA { <https://www.kylehuynh.com> foaf:ownedby [ foaf:name \"Kyle Huynh-".time()." \" ] }";
      print_log($data);
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, "http://localhost:8080/bigdata/namespace/Test/sparql");

      curl_setopt($ch, CURLOPT_FAILONERROR, true);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
      curl_setopt($ch, CURLOPT_TIMEOUT, 3);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $server_output = curl_exec($ch);
      if (curl_errno($ch)) {
        $error = curl_error($ch);
      }

      if(!$error)
        echo $server_output;
      else
        echo $error;

      /*curl_setopt($tuCurl, CURLOPT_PORT , 443);
      curl_setopt($tuCurl, CURLOPT_VERBOSE, 0);
      curl_setopt($tuCurl, CURLOPT_HEADER, 0);
      curl_setopt($tuCurl, CURLOPT_SSLVERSION, 3);
      curl_setopt($tuCurl, CURLOPT_POST, 1);
      curl_setopt($tuCurl, CURLOPT_POSTFIELDS, $data);
      curl_setopt($tuCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/sparql-update","", "Content-length: ".strlen($data)));

      $tuData = curl_exec($tuCurl);
      if(!curl_errno($tuCurl)){
        $info = curl_getinfo($tuCurl);
        echo 'Took ' . $info['total_time'] . ' seconds to send a request to ' . $info['url'];
      } else {
        echo 'Curl error: ' . curl_error($tuCurl);
      }
      curl_close($tuCurl);

      print_log($tuData);*/

      /* //$messenger = \Drupal::messenger();
      //$messenger->addMessage($this->t('<pre>'. $tuData ."</pre>"));
      */

    } catch (RequestException $e) {
      print_r($e->getMessage());
      return null;
    }

  }

}
