<?php

namespace Drupal\audit_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;
use Drupal\monitoring\Entity\SensorConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Site\Settings;

/**
 * Monitors private_file_directory channels for last execution.
 *
 * @SensorPlugin(
 *   id = "private_file_directory",
 *   label = @Translation("private file directory"),
 *   description = @Translation("Check the private file directory path."),
 *   addable = TRUE
 * )
 */
class PrivateFileDirectory extends SensorPluginBase {

  /**
   * Local variable that contains private file directory.
   *
   * @var privateFileDirectory
   */
  private $privateFileDirectory;

  /**
   * Instantiates a sensor object.
   *
   * @param \Drupal\monitoring\Entity\SensorConfig $sensor_config
   *   Sensor config object.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Site\Settings $files_directory
   *   The privateFileDirectory.
   */
  public function __construct(SensorConfig $sensor_config, $plugin_id, $plugin_definition, Settings $files_directory) {
    parent::__construct($sensor_config, $plugin_id, $plugin_definition);

    $this->privateFileDirectory = $files_directory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, SensorConfig $sensor_config, $plugin_id, $plugin_definition) {
    /* @var \Drupal\Core\Site\Settings $files_directory */
    $files_directory = $container->get('settings');
    return new static(
      $sensor_config,
      $plugin_id,
      $plugin_definition,
      $files_directory
    );
  }

  /**
   * {@inheritdoc}
   */
  public function runSensor(SensorResultInterface $sensor_result) {
    $sensor_result->setStatus(SensorResultInterface::STATUS_OK);
    // Get the value of $settings['file_private_path'].
    $private_path = $this->privateFileDirectory->get('file_private_path');
    $absolute_private_path = realpath($private_path);
    // Check if there is a match between the two paths.
    $find_in = strpos($absolute_private_path, DRUPAL_ROOT);
    // If there is a match turn the sensor result to critical.
    if ($find_in !== FALSE) {
      $sensor_result->addStatusMessage($this->t('the config directory is inside the site directory: @config'), [
        '@config' => $absolute_private_path,
      ]);
      $sensor_result->setStatus(SensorResultInterface::STATUS_CRITICAL);
    }
    else {
      $sensor_result->addStatusMessage($this->t('the config directory is outside the site directory: @config'), [
        '@config' => $absolute_private_path,
      ]);
      $sensor_result->setStatus(SensorResultInterface::STATUS_OK);
    }
  }

}
