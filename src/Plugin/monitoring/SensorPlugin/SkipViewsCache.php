<?php

namespace Drupal\audit_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\monitoring\Entity\SensorConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Monitors config_directory_path channels for last execution.
 *
 * @SensorPlugin(
 *   id = "skip_views_cache",
 *   label = @Translation("Skip views cache"),
 *   description = @Translation("Check if the option 'Disable caching of Views data' is disabled"),
 *   addable = TRUE
 * )
 */
class SkipViewsCache extends SensorPluginBase {

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
    $skip_cache = $this->configFactory->get('views.settings')->get('skip_cache');
    if ($skip_cache === TRUE) {
      $sensor_result->addStatusMessage($this->t('the option "Disable caching of Views data" is enabled'));
    }
    else {
      $sensor_result->addStatusMessage($this->t('the option "Disable caching of Views data" is disabled'));
    }
    $sensor_result->setValue($skip_cache);
  }

}
