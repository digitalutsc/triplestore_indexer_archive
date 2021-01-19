<?php

namespace Drupal\triplestore_indexer\Plugin\AdvancedQueue\JobType;

use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\JobResult;
use Drupal\advancedqueue\Plugin\AdvancedQueue\JobType\JobTypeBase;

/**
 * @AdvancedQueueJobType(
 *   id = "triplestore_index_job",
 *   label = @Translation("Triplestore Indexing"),
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
      global $base_url;
      $status = 0;

      $payload = $job->getPayload();
      $service = \Drupal::service('triplestore_indexer.indexing');

      switch ($payload['action']) {
        case "insert": {
          //for insert
          $data = $service->serialization($payload);
          $response = $service->post($data);
          break;
        }
        case "update": {
          // for update
          $data = $service->serialization($payload);
          $response = $service->put($payload, $data);
          break;
        }
        case "delete": {
          // for delete
          $response = $service->delete($payload);
          break;
        }
        default: {
          return JobResult::failure("No action assigned.");
        }
      }

      $result = simplexml_load_string($response);

      if ($result['modified'] > 0 && $result['milliseconds'] > 0) {
        return JobResult::success('Server response: '. $response);
      }else {
        return JobResult::failure('Server response: '. $response);
      }
    } catch (\Exception $e) {
      return JobResult::failure($e->getMessage());
    }
  }
}
