<?php

namespace Drupal\audit_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\monitoring\Entity\SensorConfig;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Monitors list of modules.
 *
 * @SensorPlugin(
 *   id = "maillog_config",
 *   label = @Translation("Maillog configuration"),
 *   description = @Translation("Check if maillog is installed and configurate"),
 *   addable = TRUE
 * )
 */
class MaillogConfig extends SensorPluginBase {

  /**
   * Local variable that contains the list of modules.
   *
   * @var moduleHandler
   */
  private $moduleHandler;

  /**
   * Local variable that contains ConfigFactory.
   *
   * @var confiFactory
   */
  private $confiFactory;

  /**
   * Instantiates a sensor object.
   *
   * @param \Drupal\monitoring\Entity\SensorConfig $sensor_config
   *   Sensor config object.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The moduleHandler.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configFactory.
   */
  public function __construct(SensorConfig $sensor_config, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler, ConfigFactoryInterface $config_factory) {
    parent::__construct($sensor_config, $plugin_id, $plugin_definition);
    $this->moduleHandler = $module_handler;
    $this->confiFactory = $config_factory;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, SensorConfig $sensor_config, $plugin_id, $plugin_definition) {
    /* @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
    $module_handler = $container->get('module_handler');
    /* @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');

    return new static(
      $sensor_config,
      $plugin_id,
      $plugin_definition,
      $module_handler,
      $config_factory
    );
  }

  /**
   * {@inheritDoc}
   */
  public function runSensor(SensorResultInterface $sensor_result) {

    $sensor_result->setStatus(SensorResultInterface::STATUS_OK);
    // Get the modules list names.
    $installed_modules = $this->moduleHandler->getModuleList();
    $installed_modules_names = array_keys($installed_modules);
    if (!in_array('maillog', $installed_modules_names)) {
      $sensor_result->setStatus(SensorResultInterface::STATUS_CRITICAL);
      $sensor_result->addStatusMessage($this->t('Maillog module is not install or enable'));
    }
    else {
      $maillog_config = $this->confiFactory->get('maillog.settings')->get('send');
      if ($maillog_config !== FALSE) {
        $sensor_result->setStatus(SensorResultInterface::STATUS_CRITICAL);
        $sensor_result->addStatusMessage($this->t('(in pre-prod environment) the e-mails are allowed to be sent'));
      }
      $sensor_result->setStatus(SensorResultInterface::STATUS_OK);
      $sensor_result->addStatusMessage($this->t('(in pre-prod environment) the e-mails are not allowed to be sent'));
    }
  }

}
