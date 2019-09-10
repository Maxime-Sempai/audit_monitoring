<?php

namespace Drupal\audit_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\monitoring\Entity\SensorConfig;
use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Monitors session lifetime.
 *
 * @SensorPlugin(
 *   id = "session_lifetime",
 *   label = @Translation("Session lifetime"),
 *   description = @Translation("Monitors session lifetime"),
 *   addable = TRUE
 * )
 */
class SessionLifetime extends SensorPluginBase {

  /**
   * Local variable that contains session life time.
   *
   * @var sessionMaxLifeTime
   */
  private $sessionMaxLifeTime;

  /**
   * Instantiates a sensor object.
   *
   * @param \Drupal\monitoring\Entity\SensorConfig $sensor_config
   *   Sensor config object.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $session_max_time
   *   The sessionMaxLifeTime.
   */
  public function __construct(SensorConfig $sensor_config, $plugin_id, $plugin_definition, ContainerInterface $session_max_time) {
    parent::__construct($sensor_config, $plugin_id, $plugin_definition);

    $this->sessionMaxLifeTime = $session_max_time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, SensorConfig $sensor_config, $plugin_id, $plugin_definition) {
    /* @var \Symfony\Component\DependencyInjection\ContainerInterface $session_max_time */
    $session_max_time = $container->get('service_container');
    return new static(
      $sensor_config,
      $plugin_id,
      $plugin_definition,
      $session_max_time
    );
  }

  /**
   * {@inheritdoc}
   */
  public function runSensor(SensorResultInterface $sensor_result) {
    $sensor_result->setStatus(SensorResultInterface::STATUS_OK);
    $session_lifetime = $this->sessionMaxLifeTime->getParameter('session.storage.options');
    if ($session_lifetime['gc_maxlifetime'] > 18000) {
      $sensor_result->addStatusMessage($this->t('the maximum session time exceeds 18000 seconds. current duration: @session seconds'), [
        '@session' => $session_lifetime['gc_maxlifetime'],
      ]);
      $sensor_result->setStatus(SensorResultInterface::STATUS_CRITICAL);
    }
    else {
      $sensor_result->addStatusMessage($this->t('maximum session time : @session seconds'), [
        '@session' => $session_lifetime['gc_maxlifetime'],
      ]);
      $sensor_result->setStatus(SensorResultInterface::STATUS_OK);
    }
  }

}
