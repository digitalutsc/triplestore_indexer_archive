<?php

namespace Drupal\triplestore_indexer\Plugin\AdvancedQueue\JobType;

use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\JobResult;
use Drupal\advancedqueue\Plugin\AdvancedQueue\JobType\JobTypeBase;

/**
 * Advanced Queue Job definition.
 *
 * @AdvancedQueueJobType(
 *   id = "triplestore_index_job",
 *   label = @Translation("Triplestore Indexing"),
 * )
 */
class TriplestoreIndexJob extends JobTypeBase {

  /**
   * {@inheritdoc}
   */
  public function process(Job $job) {
    try {
      global $base_url;
      $status = 0;

      $payload = $job->getPayload();

      // Set retry config.
      $this->pluginDefinition['max_retries'] = $payload['max_tries'];
      $this->pluginDefinition['retry_delay'] = $payload['retry_delay'];

      $service = \Drupal::service('triplestore_indexer.indexing');

      switch ($payload['action']) {
        case "insert":
          // For insert.
          $data = $service->serialization($payload);
          $response = $service->post($data);
          $result = simplexml_load_string($response);
          break;

        case "update":
          // For update.
          $data = $service->serialization($payload);
          $response = $service->put($payload, $data);
          $result = simplexml_load_string($response);
          break;

        case "delete":
        case '[Update] delete if exist':
          // For delete.
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

          // Delete terms and author associated with the deleting node.
          if (is_array($payload['others'])) {
            foreach ($payload['others'] as $ouri) {
              if (isset($ouri)) {
                $response = $service->delete("<$ouri>");
              }
            }
          }
          break;

        default:
          return JobResult::failure("No action assigned.");

      }

      if ($result['modified'] > 0 && $result['milliseconds'] > 0) {
        return JobResult::success($response);
      }
      else {
        return JobResult::failure($response);
      }
    }
    catch (\Exception $e) {
      return JobResult::failure($e->getMessage());
    }
  }

}
