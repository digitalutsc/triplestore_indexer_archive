<?php

namespace Drupal\triplestore_indexer;

/**
 * Class IndexingService.
 */
class IndexingService implements TripleStoreIndexingInterface {

  /**
   * Constructs a new IndexingService object.
   */
  public function __construct() {

  }


  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param string $where eg. WHERE {}
   * @return string
   */
  public function serialization (\Drupal\Core\Entity\EntityInterface $entity, String $op = "INSERT DATA",String $where = "") {
    global $base_url;

    //TODO: make GET request to any content with _format=jsonld
    $client = \Drupal::httpClient();
    $uri = "$base_url/node/" .$entity->id(). '?_format=jsonld';
    $request = $client->get($uri);
    $graph = json_decode($request->getBody())->{'@graph'};

    //TODO: convert jsonld to sparql grammar
    $data = '';
    // under @graph, 1st object is main one.
    foreach ($graph as $node) {
      $node = (array)$node;

      // send first 2 field is content type
      $data .= '<' . $node["@id"] . '> rdf:type "' . $node["@type"][0] . '".';

      foreach ($node as $field => $value) {

        if (!in_array($field, ['@id', '@type'])) {
          if (property_exists($value[0], "@id")) {
            $data .= '<' . $node["@id"] . '> <' . $field . '> "' . preg_replace("/\r|\n/", "", $value[0]->{"@id"}) . '".';
          } else if (property_exists($value[0], "@value")) {
            $data .= '<' . $node["@id"] . '> <' . $field . '> "' . preg_replace("/\r|\n/", "", $value[0]->{"@value"}) . '".';
          }
        }
      }
    }

    $params = "update=$op { $data } $where";
    print_log($params);
    return $params;
  }

  /**
   *
   * @param $params
   */
  public function post($data) {
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
        'Content-type: application/x-www-form-urlencoded',
      ),
    );

    if ($config->get("method-of-auth") == 'digest') {
      $opts[CURLOPT_USERPWD] = $config->get('admin-username') . ":" . base64_decode($config->get('admin-password'));
      $opts[CURLOPT_HTTPAUTH] = CURLAUTH_DIGEST;
      $opts[CURLOPT_HTTPHEADER] = array(
        'Content-type: application/x-www-form-urlencoded',
        'Authorization: Basic'
      );
    }
    curl_setopt_array($curl, $opts);

    $response = curl_exec($curl);
    print_log($response);
    curl_close($curl);
    return $response;
  }

  public function get($jsonld)
  {
    // TODO: Implement get() method.
  }

  public function put($jsonld)
  {
    // TODO: Implement put() method.
  }

  public function delete($jsonld)
  {
    // TODO: Implement delete() method.
  }



  public function oldSerialziation(\Drupal\Core\Entity\EntityInterface $entity) {
    global $base_url;

    // get nid from entity
    $nid = "<$base_url/node/" .$entity->id() .">";
    // get title
    $title = 'dc:title "' . $entity->getTitle(). '"';
    // get body
    $body = 'dc:description "' .  trim(preg_replace('/\s+/', ' ',strip_tags($entity->get('body')->getValue()[0]['value']) )). '"';

    $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);

    // get author
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($entity->id());

    // get author
    $owner = $node->getOwner()->getDisplayName();
    $author = 'dc:creator "' . $owner . '"' ;

    // get node type
    $type = 'dc:type "' . $entity->bundle() . '"';

    // get created time
    $published_at = 'dc:date "'.  date("F j, Y, g:i a", $node->getCreatedTime()) . '"';

    $data = "$nid $title; $body; $type; $author; $published_at";

    $params = "update=PREFIX  dc: <http://purl.org/dc/elements/1.1/> INSERT DATA { $data }";

    return $params;
  }

}
