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
          $result = simplexml_load_string($response);
          break;
        }
        case "update": {
          // for update
          $data = $service->serialization($payload);
          $response = $service->put($payload, $data);
          $result = simplexml_load_string($response);
          break;
        }
        case "delete": {
          // for delete
          $nid = $payload['nid'];
          $type = str_replace("_", "/", $payload['type']);

          $urijld = "<$base_url/$type/$nid" . '?_format=jsonld>';
          $response = $service->delete($urijld);
          $result = simplexml_load_string($response);

          if ($result['modified'] <= 0) {
            $uri = "<$base_url/$type/$nid" . '>';
            $response = $service->delete($uri);
            $result = simplexml_load_string($response);
          }
          break;
        }
        default: {
          return JobResult::failure("No action assigned.");
        }
      }

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
