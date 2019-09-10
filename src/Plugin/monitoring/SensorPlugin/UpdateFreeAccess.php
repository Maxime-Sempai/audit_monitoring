<?php

namespace Drupal\audit_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\Core\Site\Settings;
use Drupal\monitoring\Entity\SensorConfig;
use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Monitors update_free_access channels for last execution.
 *
 * @SensorPlugin(
 *   id = "update_free_access",
 *   label = @Translation("Update free access"),
 *   description = @Translation("check permissions on update.php file"),
 *   addable = TRUE
 * )
 */
class UpdateFreeAccess extends SensorPluginBase {

  /**
   * Contain an Object Settings.
   *
   * @var updateAccess
   */
  private $updateAccess;

  /**
   * Instantiates a sensor object.
   *
   * @param \Drupal\monitoring\Entity\SensorConfig $sensor_config
   *   Sensor config object.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Site\Settings $update_access
   *   The updateAccess.
   */
  public function __construct(SensorConfig $sensor_config, $plugin_id, $plugin_definition, Settings $update_access) {
    parent::__construct($sensor_config, $plugin_id, $plugin_definition);
    $this->updateAccess = $update_access;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, SensorConfig $sensor_config, $plugin_id, $plugin_definition) {
    /* @var \Drupal\Core\Site\Settings $update_access */
    $update_access = $container->get('settings');
    return new static(
      $sensor_config,
      $plugin_id,
      $plugin_definition,
      $update_access
    );
  }

  /**
   * {@inheritDoc}
   */
  public function runSensor(SensorResultInterface $sensor_result) {
    $update_free_access = $this->updateAccess->get('update_free_access');
    if ($update_free_access != FALSE) {
      $sensor_result->addStatusMessage($this->t('access to update.php is not restricted'));
    }
    else {
      $sensor_result->addStatusMessage($this->t('access to update.php is protected'));
    }
    $sensor_result->setValue($update_free_access);
  }

}
