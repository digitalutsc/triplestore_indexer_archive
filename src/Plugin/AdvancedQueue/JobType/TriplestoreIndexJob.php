<?php

namespace Drupal\triplestore_indexer\Plugin\AdvancedQueue\JobType;

use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\JobResult;
use Drupal\advancedqueue\Plugin\AdvancedQueue\JobType\JobTypeBase;

/**
 * @AdvancedQueueJobType(
 *   id = "triplestore_index_job",
 *   label = @Translation("Single Triplestore Indexing Job"),
 * )
 */
class TriplestoreIndexJob extends JobTypeBase
{
  /**
   * {@inheritdoc}
   */
  public function process(Job $job)
  {
    try {

      $status = 0;

      $payload = $job->getPayload();
      $service = \Drupal::service('triplestore_indexer.indexing');

      $data = $service->serialization($payload['nid']);
      $response = $service->post($data);
      $result = simplexml_load_string($response);

      if ($result['modified'] > 0 && $result['milliseconds'] > 0) {
        return JobResult::success('Success. Server response: '. $response);
      }else {
        return JobResult::success('Failure. Server response: '. $response);
      }
    } catch (\Exception $e) {
      return JobResult::failure($e->getMessage());
    }
  }
}
