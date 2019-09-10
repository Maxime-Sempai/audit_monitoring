<?php

namespace Drupal\audit_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\Core\Site\Settings;
use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\monitoring\Entity\SensorConfig;

/**
 * Monitors trusted_host_patterns channels for last execution.
 *
 * @SensorPlugin(
 *   id = "trusted_host_pattern",
 *   label = @Translation("Check trusted_host_patterns"),
 *   description = @Translation("Check the config trusted_host_pattern."),
 *   addable = TRUE
 * )
 */
class TrustedHostPattern extends SensorPluginBase {

  /**
   * Local variable that contains settings.
   *
   * @var settingsContainer
   */
  private $settingsContainer;

  /**
   * Instantiates a sensor object.
   *
   * @param \Drupal\monitoring\Entity\SensorConfig $sensor_config
   *   Sensor config object.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Site\Settings $settings_container
   *   The settingsContainer.
   */
  public function __construct(SensorConfig $sensor_config, $plugin_id, $plugin_definition, Settings $settings_container) {
    parent::__construct($sensor_config, $plugin_id, $plugin_definition);

    $this->settingsContainer = $settings_container;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, SensorConfig $sensor_config, $plugin_id, $plugin_definition) {
    /* @var \Drupal\Core\Site\Settings $settings_container */
    $settings_container = $container->get('settings');
    return new static(
      $sensor_config,
      $plugin_id,
      $plugin_definition,
      $settings_container
    );
  }

  /**
   * {@inheritdoc}
   */
  public function runSensor(SensorResultInterface $sensor_result) {
    $sensor_result->setStatus(SensorResultInterface::STATUS_OK);
    $trusted_host = $this->settingsContainer->get('trusted_host_patterns');
    if (empty($trusted_host)) {
      $sensor_result->addStatusMessage($this->t('trusted_host_patterns is not configured'));
      $sensor_result->setStatus(SensorResultInterface::STATUS_CRITICAL);
    }
    else {
      $sensor_result->addStatusMessage($this->t('trusted_host_patterns is configured'));
      $sensor_result->setStatus(SensorResultInterface::STATUS_OK);
    }
  }

}
