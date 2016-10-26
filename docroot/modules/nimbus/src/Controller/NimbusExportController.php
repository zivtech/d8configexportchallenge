<?php

namespace Drupal\nimbus\Controller;

use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\StorageComparer;
use Drupal\nimbus\config\ProxyFileStorage;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class NimbusExportController.
 *
 * @package Drupal\nimbus\Controller
 */
class NimbusExportController {
  /**
   * @var
   */
  private $fileStorage;
  /**
   * @var \Drupal\Core\Config\FileStorage
   */
  protected $configTarget;
  /**
   * @var \Drupal\Core\Config\ConfigManager
   */
  private $configManager;
  /**
   * @var \Drupal\Core\Config\CachedStorage
   */
  private $configActive;

  /**
   * NimbusExportController constructor.
   *
   * @param \Drupal\Core\Config\StorageInterface $config_target
   * @param \Drupal\Core\Config\ConfigManagerInterface $config_manager
   * @param \Drupal\Core\Config\StorageInterface $config_active
   */
  public function __construct(StorageInterface $config_target, ConfigManagerInterface $config_manager, StorageInterface $config_active) {
    $this->configTarget = $config_target;
    $this->configManager = $config_manager;
    $this->configActive = $config_active;
  }

  /**
   * Configuration Export.
   *
   * @param string $destination
   *    The destination.
   * @param string $destination_dir
   *    The destination dir.
   * @param string $branch
   *    The branch.
   *
   * @return array|bool
   *    Return successfull or not
   */
  public function configurationExport(InputInterface $input, OutputInterface $output) {
    $output->writeln('Override Export');
    // Do the actual config export operation.
    $result = array();

    $config_comparer = new StorageComparer($this->configActive, $this->configTarget, $this->configManager);

    if (!$config_comparer->createChangelist()->hasChanges()) {
      $output->writeln('The active configuration is identical to the configuration in the export directories.');
      return TRUE;
    }

    $output->writeln("Differences of the active config to the export directory:");

    $change_list = array();
    foreach ($config_comparer->getAllCollectionNames() as $collection) {
      $change_list[$collection] = $config_comparer->getChangelist(NULL, $collection);
    }

    $this->createTable($change_list, $output);
    $helper = new QuestionHelper();

    $configTarget = $this->configTarget;

    $question = new ConfirmationQuestion('The .yml files in your export directory (' . $this->configTarget->getWriteDirectories() . ") will be deleted and replaced with the active config. \n(y/n) ", FALSE);
    try {
      $value = $input->getArgument('accept');
      if ($input->isInteractive()) {
        $input->setInteractive(!$value);
      }
    }
    catch (\Exception $e) {
      $input->setInteractive(FALSE);
    }
    if (!$helper->ask($input, $output, $question)) {
      $output->writeln('Aborted !');
      return FALSE;
    }

    // Write all .yml files.
    $source_storage = $this->configActive;
    $destination_storage = $this->configTarget;
    if (isset($change_list[''])) {

      foreach ($change_list['']['delete'] as $name) {
        if (is_string($name)) {
          $destination_storage->delete($name);
        }
      }
      unset($change_list['']['delete']);

      foreach ($change_list[''] as $update_categories) {
        foreach ($update_categories as $name) {
          if (is_string($name)) {
            $destination_storage->write($name, $this->configActive->read($name));
          }
        }
      }
    }

    // Export configuration collections.
    $live_collection = $this->configActive->getAllCollectionNames();
    $collections_iteration = array_merge($live_collection, $destination_storage->getAllCollectionNames());
    array_unique($collections_iteration);

    foreach ($collections_iteration as $collection) {
      $source_storage = $source_storage->createCollection($collection);
      $destination_storage = $destination_storage->createCollection($collection);
      if (isset($change_list[$collection])) {
        if (isset($change_list[$collection]['delete'])) {
          foreach ($change_list[$collection]['delete'] as $name) {
            if (is_string($name)) {
              $destination_storage->delete($name);
            }
          }
          unset($change_list[$collection]['delete']);
        }
        foreach ($change_list[$collection] as $update_categories) {
          foreach ($update_categories as $name) {
            if (is_string($name)) {
              $destination_storage->write($name, $source_storage->read($name));
            }
          }
        }
      }
    }

    $output->writeln('Configuration successfully exported to ' . $this->configTarget->getWriteDirectories() . ". \n");

    return $result;
  }

  /**
   * @param $rows
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   */
  public function createTable($rows, OutputInterface $output) {
    $file_storage = $this->fileStorage;
    $headers = ['Collection', 'Config', 'Operation'];
    $elements = [];

    if ($file_storage instanceof ProxyFileStorage) {
      $headers[] = 'Directory';
    }

    foreach ($rows as $collection => $row) {
      foreach ($row as $key => $config_names) {
        foreach ($config_names as $config_name) {
          $element = [
            $collection,
            $config_name,
            $key,
          ];
          if ($file_storage instanceof ProxyFileStorage) {
            $path = ($key == 'delete') ? $file_storage->getFilePath($config_name) : $file_storage->getWriteDirectories();
            $element[] = $path;
          }
          $elements[] = $element;
        }
      }

    }
    $table = new Table($output);
    $table
      ->setHeaders($headers)
      ->setRows($elements);
    $table->render();
  }

}
