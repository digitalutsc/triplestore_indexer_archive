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

    return "$graph ";
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
    print_log($response);
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
    $uri = "$base_url/$type/$nid" . '?_format=jsonld';


    // delete previously triples indexed
    $deleted = $this->delete($payload);

    // index with updated content
    if (isset($deleted)) {
      $insert = $this->post($data);
    }
    return $insert;
  }

  /**
   * @param $subject : must be urlencode
   */
  public function delete(array $payload)
  {
    global $base_url;
    $nid = $payload['nid'];
    $type = str_replace("_", "/", $payload['type']);
    $subject = "<$base_url/$type/$nid" . '?_format=jsonld>';

    $curl = curl_init();

    $config = \Drupal::config('triplestore_indexer.triplestoreindexerconfig');
    $server = $config->get("server-url");
    $namespace = $config->get("namespace");

    $opts = array(

      CURLOPT_URL => "$server/namespace/$namespace/sparql?s=". urlencode($subject),
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


  public function oldSerialziation(\Drupal\Core\Entity\EntityInterface $entity)
  {
    global $base_url;

    // get nid from entity
    $nid = "<$base_url/node/" . $entity->id() . ">";
    // get title
    $title = 'dc:title "' . $entity->getTitle() . '"';
    // get body
    $body = 'dc:description "' . trim(preg_replace('/\s+/', ' ', strip_tags($entity->get('body')->getValue()[0]['value']))) . '"';

    $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);

    // get author
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($entity->id());

    // get author
    $owner = $node->getOwner()->getDisplayName();
    $author = 'dc:creator "' . $owner . '"';

    // get node type
    $type = 'dc:type "' . $entity->bundle() . '"';

    // get created time
    $published_at = 'dc:date "' . date("F j, Y, g:i a", $node->getCreatedTime()) . '"';

    $data = "$nid $title; $body; $type; $author; $published_at";

    $params = "update=PREFIX  dc: <http://purl.org/dc/elements/1.1/> INSERT DATA { $data }";

    return $params;
  }

}
