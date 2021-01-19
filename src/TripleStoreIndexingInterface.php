<?php

namespace Drupal\triplestore_indexer;

/**
 * Interface TripleStoreIndexingInterface.
 */
interface TripleStoreIndexingInterface {

  public function serialization(\Drupal\Core\Entity\EntityInterface $entity);
  public function get($jsonld);
  public function post($data);
  public function put($nid, $data);
  public function delete($subject);

}
