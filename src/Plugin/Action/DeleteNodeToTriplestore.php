<?php

namespace Drupal\triplestore_indexer\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides an Index content to Triplestore when node is deleted.
 *
 * @Action(
 *   id = "delete_node_in_triplestore_advancedqueue",
 *   label = @Translation("Delete node in Triplestore [via Advanced Queue]"),
 *   type = "node",
 *   category = @Translation("Custom")
 * )
 *
 * @DCG
 * For a simple updating entity fields consider extending FieldUpdateActionBase.
 */
class DeleteNodeToTriplestore extends ActionBase {

  /**
   * Implements access()
   */
  public function access($node, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\node\NodeInterface $node */
    $access = $node->access('delete', $account, TRUE)
      ->andIf($node->title->access('edit', $account, TRUE));
    return $return_as_object ? $access : $access->isAllowed();
  }

  /**
   * Implements execute()
   */
  public function execute($node = NULL) {
    /** @var \Drupal\node\NodeInterface $node */

    // Delete previous indexed (if applicable)
    queue_process($node, 'delete');

  }

}
