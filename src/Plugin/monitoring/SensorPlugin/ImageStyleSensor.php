<?php

namespace Drupal\audit_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\monitoring\Entity\SensorConfig;
use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Monitors image_style_sensor channels for last execution.
 *
 * @SensorPlugin(
 *   id = "image_style_sensor",
 *   label = @Translation("Image style sensor"),
 *   description = @Translation("Check images style."),
 *   addable = FALSE
 * )
 */
class ImageStyleSensor extends SensorPluginBase {

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

    $entities = $this->configFactory;
    $value = 0;
    // Get list of all display entities.
    $names = (array) $entities->listAll('core.entity_view_display');
    $entities_views_display = $entities->loadMultiple($names);
    foreach ($entities_views_display as $entity_view_display => $values) {
      $data = $values->getRawData();
      // Checks for the existence of the image field content and if the field_image field is empty.
      foreach ($data['content'] as $settings => $image_style) {
        if (isset($image_style['type']) && $image_style['type'] == "image" && $image_style['settings']['image_style'] == "") {
          $image = $data['id'];
          $sensor_result->addStatusMessage($this->t('the image of : @image has not been reworked and may be too large'), [
            '@image' => $image,
          ]);
          $value++;
        }
      }
    }
    $sensor_result->setValue($value);
  }

}
