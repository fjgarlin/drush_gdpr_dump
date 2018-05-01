<?php

namespace Drupal\gdpr_dump\Commands;

use Consolidation\AnnotatedCommand\AnnotationData;
use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Input\InputInterface;

class DumpEnhancerCommands extends DrushCommands {

  /**
   * Options for the sql-dump  and sql-sync commands.
   *
   * @hook option @optionset_table_selection
   * @option gdpr Make a gdpr compliant db dump or sync by obfuscating data.
   * @default gdpr false
   *
   * @todo Can we move this to ::initializeDump()?
   */
  public function optionsDump() {
  }

  /**
   * Init the sql-dump and sql-sync commands.
   *
   * @hook init sql-dump
   * @hook init sql-sync
   *
   * @throws \Exception
   */
  public function initializeDump(InputInterface $input, AnnotationData $annotationData) {
    if ($input->getOption('gdpr')) {
      // Bootstrap
      $success = drush_bootstrap_to_phase(DRUSH_BOOTSTRAP_DRUPAL_FULL);
      if (!$success) {
        throw new \Exception('Option --gdpr needs an installed drupal site.');
      }
      // Ask API.
      /** @var \Drupal\gdpr_dump\GdprDumpExpressionCollectorInterface $dumpExpressionCollector */
      $dumpExpressionCollector = \Drupal::service('gdpr.dump-expression-collector');
      if (!$dumpExpressionCollector) {
        throw new \Exception('Option --gdpr needs enabled gdpr_dump module.');
      }
      $expressions = $dumpExpressionCollector->getExpressions();
      $extraDumpOption = 'gdpr-expressions=' . escapeshellarg(json_encode($expressions));
      // Append to prior extraDumpOption-dump option.
      if ($input->getOption('extra-dump')) {
        $extraDumpOption = $input->getOption('extra-dump') . ' ' . $extraDumpOption;
      }
      $input->setOption('extra-dump', $extraDumpOption);
      // @todo Make sure we use the right mysqldump.
      // Either via $PATH, or via (yet to implement) drush option
    }
  }

}
