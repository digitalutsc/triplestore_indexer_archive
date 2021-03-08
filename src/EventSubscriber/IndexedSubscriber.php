<?php

namespace Drupal\triplestore_indexer\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class IndexedSubscriber.
 */
class IndexedSubscriber implements EventSubscriberInterface
{

  /**
   * Constructs a new IndexedSubscriber object.
   */
  public function __construct()
  {

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents()
  {
    $events['advancedqueue.post_process'] = ['onPostRespond'];
    $events['advancedqueue.pre_process'] = ['onPreRespond'];
    return $events;
  }

  /**
   * This method is called when the advancedqueue.post_process is dispatched.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The dispatched event.
   */
  public function onPostRespond(Event $event)
  {
    //\Drupal::messenger()->addMessage('Event advancedqueue.post_process thrown by Subscriber in module triplestore_indexer.', 'status', TRUE);
  }

  /**
   * This method is called when the advancedqueue.post_process is dispatched.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The dispatched event.
   */
  public function onPreRespond(Event $event)
  {
    //\Drupal::messenger()->addMessage('Event advancedqueue.pre_process thrown by Subscriber in module triplestore_indexer.', 'status', TRUE);
  }

}
