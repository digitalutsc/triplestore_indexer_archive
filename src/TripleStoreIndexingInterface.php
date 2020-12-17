<?php

namespace Drupal\triplestore_indexer;

/**
 * Interface TripleStoreIndexingInterface.
 */
interface TripleStoreIndexingInterface {

  public function serialization(\Drupal\Core\Entity\EntityInterface $entity);
  public function get($jsonld);
  public function post($jsonld);
  public function put($jsonld);
  public function delete($jsonld);

}
