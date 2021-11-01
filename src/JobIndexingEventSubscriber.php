<?php

namespace Drupal\triplestore_indexer;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\advancedqueue\Event\AdvancedQueueEvents;
use Drupal\advancedqueue\Event\JobEvent;
use Drupal\advancedqueue\Job;

/**
 * Class JobIndexingEventSubscriber definition.
 */
class JobIndexingEventSubscriber implements EventSubscriberInterface {

  /**
   * Method that is triggered on the response event.
   *
   * @param \Drupal\advancedqueue\Event\JobEvent $event
   *   Event job definition.
   *
   * @return bool
   *   Successful response to event.
   *
   * @throws \Exception
   */
  public function onRespond(JobEvent $event) {
    try {
      $job = $event->getJob();
      $state = $job->getState();

      // Don't do anything on a failed or requeued import Job.
      if ($state !== Job::STATE_SUCCESS) {
        return FALSE;
      }
    }
    catch (\Exception $e) {
      $logger = \Drupal::service('logger.factory');
      $logger->get('triplestore_indexing_queue')->error($e->getMessage());
    }
  }

  /**
   * Implements getSubscribedEvents().
   *
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    $events[AdvancedQueueEvents::POST_PROCESS][] = ['onRespond'];
    return $events;
  }

}
