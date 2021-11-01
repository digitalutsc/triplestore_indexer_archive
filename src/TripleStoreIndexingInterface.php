<?php

namespace Drupal\triplestore_indexer;

/**
 * Interface TripleStoreIndexingInterface declaration.
 */
interface TripleStoreIndexingInterface {

  /**
   * Serialization payload.
   */
  public function serialization(array $payload);

  /**
   * GET request.
   */
  public function get(array $payload);

  /**
   * POST request.
   */
  public function post(String $data);

  /**
   * PUT request.
   */
  public function put(array $payload, $data);

  /**
   * DELETE request.
   */
  public function delete(String $uri);

}
