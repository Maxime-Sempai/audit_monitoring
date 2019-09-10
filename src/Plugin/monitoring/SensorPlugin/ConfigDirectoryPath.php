<?php

namespace Drupal\audit_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;

/**
 * Monitors config_directory_path channels for last execution.
 *
 * @SensorPlugin(
 *   id = "config_directory_path",
 *   label = @Translation("Config Directory path"),
 *   description = @Translation("Check the config directory path."),
 *   addable = FALSE
 * )
 */
class ConfigDirectoryPath extends SensorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function runSensor(SensorResultInterface $sensor_result) {

    $conf_directory = config_get_config_directory(CONFIG_SYNC_DIRECTORY);
    $absolute_conf_dir = realpath($conf_directory);

    $find_in = strpos($absolute_conf_dir, DRUPAL_ROOT);

    if ($find_in !== FALSE) {
      $sensor_result->addStatusMessage($this->t('the config directory is inside the site directory: @config'), [
        '@config' => $absolute_conf_dir,
      ]);
    }
    else {
      $sensor_result->addStatusMessage($this->t('the config directory is outside the site directory: @config'), [
        '@config' => $absolute_conf_dir,
      ]);
    }
    $sensor_result->setValue($find_in);
  }

}
