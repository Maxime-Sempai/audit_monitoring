<?php

namespace Drupal\audit_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\monitoring\Entity\SensorConfig;
use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Monitors config_directory_path channels for last execution.
 *
 * @SensorPlugin(
 *   id = "disabled_views_debugging",
 *   label = @Translation("Disabled Views debugging"),
 *   description = @Translation("Check if debugging views are disabled."),
 *   addable = TRUE
 * )
 */
class DisabledViewsDebugging extends SensorPluginBase {

  /**
   * The ConfigFactory container.
   *
   * @var configFactory
   */
  private $configFactory;

  /**
   * Instantiates a sensor object.
   *
   * @param \Drupal\monitoring\Entity\SensorConfig $sensor_config
   *   Sensor config object.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\core\Config\ConfigFactoryInterface $config_factory
   *   The configFactory.
   */
  public function __construct(SensorConfig $sensor_config, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($sensor_config, $plugin_id, $plugin_definition);

    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, SensorConfig $sensor_config, $plugin_id, $plugin_definition) {
    /* @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');
    return new static(
      $sensor_config,
      $plugin_id,
      $plugin_definition,
      $config_factory
    );
  }

  /**
   * {@inheritdoc}
   */
  public function runSensor(SensorResultInterface $sensor_result) {
    $sql_signature = $this->configFactory->get('views.settings')->get('sql_signature');
    if ($sql_signature === TRUE) {
      $sensor_result->addStatusMessage($this->t('The debugging views mode is enable'));
    }
    else {
      $sensor_result->addStatusMessage($this->t('The debugging views mode is disabled'));
    }
    $sensor_result->setValue($sql_signature);
  }

}
