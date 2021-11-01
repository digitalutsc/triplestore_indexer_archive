<?php

namespace Drupal\triplestore_indexer\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a Bulk Index node to Triplestore action.
 *
 * @Action(
 *   id = "index_node_to_triplestore_advancedqueue",
 *   label = @Translation("Index node to Triplestore [via Advanced Queue]"),
 *   type = "node",
 *   category = @Translation("Custom")
 * )
 *
 * @DCG
 * For a simple updating entity fields consider extending FieldUpdateActionBase.
 */
class IndexNodeToTriplestore extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function access($node, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\node\NodeInterface $node */
    $access = $node->access('update', $account, TRUE)
      ->andIf($node->title->access('edit', $account, TRUE));
    return $return_as_object ? $access : $access->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function execute($node = NULL) {
    /** @var \Drupal\node\NodeInterface $node */

    // Delete previous indexed (if applicable)
    // queue_process($node, '[Update] delete if exist');
    // Index the latest version of the node.
    queue_process($node, 'insert');
  }

}
