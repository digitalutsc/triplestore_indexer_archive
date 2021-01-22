<?php

namespace Drupal\triplestore_indexer;

/**
 * Interface TripleStoreIndexingInterface.
 */
interface TripleStoreIndexingInterface {

  public function serialization(array $payload);
  public function get(array $payload);
  public function post(String $data);
  public function put(array $payload, $data);
  public function delete(String $uri);

}
