<?php

namespace Drupal\triplestore_indexer\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\Command;

/**
 * Class IndexingCommand.
 *
 * Drupal\Console\Annotations\DrupalCommand (
 *     extension="triplestore_indexer",
 *     extensionType="module"
 * )
 */
class IndexingCommand extends Command {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('triplestore:indexing')
      ->setDescription($this->trans('Queue to index Drupal content to Triple Store (Blazegraph)'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->getIo()->info('execute');
    $this->getIo()->info($this->trans('commands.triplestore.indexing.messages.success'));
  }

}
