<?php

namespace Drupal\triplestore_indexer;

/**
 * Class IndexingService.
 */
class IndexingService implements TripleStoreIndexingInterface
{

  /**
   * Constructs a new IndexingService object.
   */
  public function __construct()
  {

  }


  /**
   * @param array $payload
   * @return string
   */
  public function serialization(array $payload)
  {
    global $base_url;
    $nid = $payload['nid'];
    $type = str_replace("_", "/", $payload['type']);

    //make GET request to any content with _format=jsonld
    $client = \Drupal::httpClient();
    $uri = "$base_url/$type/$nid" . '?_format=jsonld';
    $request = $client->get($uri);
    $graph = $request->getBody();

    return $graph;
  }

  /**
   * @param array $payload
   * @return string
   */
  public function getOtherConmponentAssocNode(array $payload)
  {
    global $base_url;
    $nid = $payload['nid'];
    $type = str_replace("_", "/", $payload['type']);

    //make GET request to any content with _format=jsonld
    $client = \Drupal::httpClient();
    $uri = "$base_url/$type/$nid" . '?_format=jsonld';
    $request = $client->get($uri);
    $graph = ((array)json_decode($request->getBody()))['@graph'];

    $config = \Drupal::config('triplestore_indexer.triplestoreindexerconfig');
    $indexedContentTypes = array_keys(array_filter($config->get('content-type-to-index')));
    $others = [];
    for($i = 1; $i < count($graph); $i ++) {
      $component = (array)$graph[$i];

      //check if this component is set to be allowed to delete in config form.
      $vocal = getVocabularyFromTermID(getTermIDfromURI($component['@id']));
      if (isset($vocal) && in_array($vocal, $indexedContentTypes)) {
        array_push($others, $component['@id']);
      }
    }

    return $others;
  }

  /**
   * @param $data serialized json-ld
   * @return bool|string
   */
  public function post(String $data)
  {
    $config = \Drupal::config('triplestore_indexer.triplestoreindexerconfig');
    $server = $config->get("server-url");
    $namespace = $config->get("namespace");

    $curl = curl_init();
    $opts = array(
      CURLOPT_URL => "$server/namespace/$namespace/sparql",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $data,
      CURLOPT_HTTPHEADER => array(
        'Content-type: application/ld+json',
      ),
    );

    if ($config->get("method-of-auth") == 'digest') {
      $opts[CURLOPT_USERPWD] = $config->get('admin-username') . ":" . base64_decode($config->get('admin-password'));
      $opts[CURLOPT_HTTPAUTH] = CURLAUTH_DIGEST;
      $opts[CURLOPT_HTTPHEADER] = array(
        'Content-type: application/ld+json',
        'Authorization: Basic'
      );
    }
    curl_setopt_array($curl, $opts);

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
  }

  /**
   * @param $jsonld
   */
  public function get(array $payload)
  {
    // TODO: Implement get() method.
  }

  /**
   * @param $nid
   * @param $data serialized json-ld
   * @return bool|string
   */
  public function put(array $payload, $data)
  {
    global $base_url;

    $nid = $payload['nid'];
    $type = str_replace("_", "/", $payload['type']);

    // delete previously triples indexed
    $urijld = "<$base_url/$type/$nid" . '?_format=jsonld>';
    $response = $this->delete($urijld);

    // check ?s may be insert with uri with ?_format=jsonld
    $result = simplexml_load_string($response);
    if ($result['modified'] <= 0) {
      $uri = "<$base_url/$type/$nid>";
      $response = $this->delete($uri);
    }

    // index with updated content
    if (isset($response)) {
      $insert = $this->post($data);
    }
    return $insert;
  }

  /**
   * @param $subject : must be urlencode
   */
  public function delete(String $uri)
  {
    $curl = curl_init();

    $config = \Drupal::config('triplestore_indexer.triplestoreindexerconfig');
    $server = $config->get("server-url");
    $namespace = $config->get("namespace");

    $opts = array(

      CURLOPT_URL => "$server/namespace/$namespace/sparql?s=". urlencode($uri),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'DELETE',
      CURLOPT_POSTFIELDS => "",
      CURLOPT_HTTPHEADER => array(
        'Content-type: text/plain'
      ),
    );

    if ($config->get("method-of-auth") == 'digest') {
      $opts[CURLOPT_USERPWD] = $config->get('admin-username') . ":" . base64_decode($config->get('admin-password'));
      $opts[CURLOPT_HTTPAUTH] = CURLAUTH_DIGEST;
      $opts[CURLOPT_HTTPHEADER] = array(
        'Content-type: text/plain',
        'Authorization: Basic'
      );
    }
    curl_setopt_array($curl, $opts);

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;

  }

}
