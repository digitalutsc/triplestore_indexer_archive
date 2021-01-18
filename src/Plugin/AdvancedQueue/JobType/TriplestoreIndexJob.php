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
      global $base_url;
      $status = 0;

      $payload = $job->getPayload();
      $service = \Drupal::service('triplestore_indexer.indexing');

      switch ($payload['action']) {
        case "insert": {
          $data = $service->serialization($payload['nid']);
          $response = $service->post($data);
          break;
        }
        case "update": {
          $data = $service->serialization($payload['nid']);
          $response = $service->post($data);
          break;
        }
        case "delete": {
          $uri = "<$base_url/node/" . $payload['nid'] . '?_format=jsonld>';
          $response = $service->delete($uri);
          break;
        }
        default: {
          return JobResult::failure("No action assigned.");
        }
      }

      $result = simplexml_load_string($response);

      if ($result['modified'] > 0 && $result['milliseconds'] > 0) {
        return JobResult::success('Success. Server response: '. $response);
      }else {
        return JobResult::failure('Failure. Server response: '. $response);
      }
    } catch (\Exception $e) {
      return JobResult::failure($e->getMessage());
    }
  }
}
