<?php


namespace Drupal\triplestore_indexer\Plugin\Action;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a Index content to Triplestore when node is deleted
 *
 * @Action(
 *   id = "delete_node_advancedqueue",
 *   label = @Translation("Delete node in Triplestore [via Advanced Queue]"),
 *   type = "node",
 *   category = @Translation("Custom")
 * )
 *
 * @DCG
 * For a simple updating entity fields consider extending FieldUpdateActionBase.
 */
class DeleteNodeToTriplestore extends ActionBase
{

  public function access($node, AccountInterface $account = NULL, $return_as_object = FALSE)
  {
    /** @var \Drupal\node\NodeInterface $node */
    $access = $node->access('delete', $account, TRUE)
      ->andIf($node->title->access('edit', $account, TRUE));
    return $return_as_object ? $access : $access->isAllowed();
  }

  public function execute($node = NULL) {
    /** @var \Drupal\node\NodeInterface $node */

    // delete previous indexed (if applicable)
    queueIndexing($node, 'delete');

  }
}
