<?php


namespace Drupal\triplestore_indexer;

use Drupal\advancedqueue\Event\AdvancedQueueEvents;
use Drupal\advancedqueue\Event\JobEvent;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Logger;
use Drupal\advancedqueue\Job;

class JobIndexingEventSubscriber implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{

  /**
   * Method that is triggered on the response event.
   *
   * @param \Drupal\advancedqueue\Event\JobEvent $event
   *
   * @return bool
   *   Successful response to event.
   *
   * @throws \Exception
   */
  public function onRespond(JobEvent $event)
  {
    try {
      $job = $event->getJob();
      $state = $job->getState();

      // Don't do anything on a failed or requeued import Job.
      if ($state !== Job::STATE_SUCCESS) {
        return FALSE;
      }
    }
    catch(\Exception $e) {
      $logger = \Drupal::service('logger.factory');
      $logger->get('triplestore_indexing_queue')->error($e->getMessage());
    }
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents()
  {
    $events[AdvancedQueueEvents::POST_PROCESS][] = ['onRespond'];
    return $events;
  }
}
