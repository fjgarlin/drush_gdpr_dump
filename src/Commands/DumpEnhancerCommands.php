<?php

namespace Drupal\gdpr_dump\Commands;

use Consolidation\AnnotatedCommand\AnnotationData;
use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Input\InputInterface;

class DumpEnhancerCommands extends DrushCommands {

  /**
   * Options for the sql-dump command.
   *
   * @hook option @optionset_table_selection
   * @option gdpr Make a gdpr compliant db dump or sync by obfuscating data.
   */
  public function optionsDump($options = ['gdpr' => false]) {
  }

  /**
   * Init the sql-dump command.
   *
   * @todo Also sql-sync, when it recognizes --extra-dump.
   *   https://github.com/drush-ops/drush/issues/3512
   * @hook init sql-dump
   *
   * @throws \Exception
   */
  public function initializeDump(InputInterface $input, AnnotationData $annotationData) {
    if ($input->getOption('gdpr')) {
      // Bootstrap
      $success = drush_bootstrap_to_phase(DRUSH_BOOTSTRAP_DRUPAL_FULL);
      if (!$success) {
        throw new \Exception('Can not bootstrap drupal, but --gdpr needs this.');
      }
      // Ask API.
      /** @var \Drupal\gdpr_dump\GdprDumpExpressionCollectorInterface $dumpExpressionCollector */
      $dumpExpressionCollector = \Drupal::service('gdpr.dump-expression-collector');
      $expressions = $dumpExpressionCollector->getExpressions();
      $extraDumpOption = escapeshellarg(json_encode($expressions));
      // Append to prior extraDumpOption-dump option.
      if ($input->getOption('extra-dump')) {
        $extraDumpOption = $input->getOption('extra-dump') . ' ' . $extraDumpOption;
      }
      $input->setOption('extra-dump', $extraDumpOption);
    }
  }

}
