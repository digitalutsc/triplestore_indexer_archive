<?php

namespace Drupal\triplestore_indexer\Plugin\AdvancedQueue\JobType;

use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\JobResult;
use Drupal\advancedqueue\Plugin\AdvancedQueue\JobType\JobTypeBase;

/**
 * @AdvancedQueueJobType(
 *   id = "triplestore_indexing_job",
 *   label = @Translation("Single Triplestore Indexing Job"),
 *   max_retries = 1,
 *   retry_delay = 79200,
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

      $result = $this->_indexing($payload['data'], $payload['op']);
      print_log($result);
      if ($result != null) {
        return JobResult::success('Success');
      }
      return JobResult::failure('Failed.');

    } catch (\Exception $e) {
      return JobResult::failure($e->getMessage());
    }
  }

  /**
   * funcation call embedded after hook_insert,hook_update,hook_delete executed
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param $action
   */
  function _indexing(\Drupal\Core\Entity\EntityInterface $entity, $action)
  {
    //TODO: check if the entity is content type is selected and triggered event selected
    $config = \Drupal::config('triplestore_indexer.triplestoreindexerconfig');
    $triggeredEvents = array_keys(array_filter($config->get('events-to-index')));
    $indexedContentTypes = array_keys(array_filter($config->get('content-type-to-index')));

    $result = null;
    switch ($action) {
      case 'insert':
      {
        if (in_array("created", $triggeredEvents) && in_array($entity->bundle(), $indexedContentTypes)) {
          $service = \Drupal::service('triplestore_indexer.indexing');
          $data = $service->serialization($entity);
          $result = $service->post($data);
        }
        break;
      }
      case 'update':
      {
        if (in_array("updated", $triggeredEvents) && in_array($entity->getEntityType(), $indexedContentTypes)) {
          $service = \Drupal::service('triplestore_indexer.indexing');
          $data = $service->serialization($entity);
          $result = $service->put($data);
        }
        break;
      }
      case 'delete':
      {
        if (in_array("deleted", $triggeredEvents) && in_array($entity->getEntityType(), $indexedContentTypes)) {
          $service = \Drupal::service('triplestore_indexer.indexing');
          $data = $service->serialization($entity);
          $result = $service->delete($data);
        }
        break;
      }
      default:
      {
        break;
      }
    }
    return $result;
  }

}
