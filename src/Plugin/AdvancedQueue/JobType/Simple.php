<?php

namespace Drupal\triplestore_indexer\Plugin\AdvancedQueue\JobType;

use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\JobResult;
use Drupal\advancedqueue\Plugin\AdvancedQueue\JobType\JobTypeBase;

/**
 * @AdvancedQueueJobType(
 *   id = "simple",
 *   label = @Translation("Simple"),
 * )
 */
class Simple extends JobTypeBase {

  /**
   * {@inheritdoc}
   */
  public function process(Job $job) {
    print_log("simple processing");
    return JobResult::success();
  }

}
