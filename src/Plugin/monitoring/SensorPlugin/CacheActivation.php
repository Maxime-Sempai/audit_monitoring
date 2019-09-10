<?php

namespace Drupal\audit_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;

/**
 * Monitors cache yml activation.
 *
 * @SensorPlugin(
 *   id = "cache_yml_activation",
 *   label = @Translation("Cache yml activation"),
 *   description = @Translation("Monitors cache yml activation"),
 *   addable = TRUE
 * )
 */
class CacheActivation extends SensorPluginBase {

  public function runSensor(SensorResultInterface $sensor_result) {
    $twig_config = \Drupal::getContainer()->getParameter('twig.config');
    $value = 0;
    if ($twig_config['debug'] !== FALSE || $twig_config['auto_reload'] !== NULL || $twig_config['cache'] !== TRUE) {
      $sensor_result->addStatusMessage($this->t('error in twig.config configuration. debug : @debug, auto_reload : @auto_reload, cache : @cache'), [
        '@debug' => $twig_config['debug'],
        '@auto_reload' => $twig_config['auto_reload'],
        '@cache' => $twig_config['cache'],
      ]);
      $value++;
    }
    else {
      $sensor_result->addStatusMessage($this->t('debug : @debug, auto_reload : @auto_reload, cache : @cache'), [
        '@debug' => $twig_config['debug'],
        '@auto_reload' => $twig_config['auto_reload'],
        '@cache' => $twig_config['cache'],
      ]);
    }
    $sensor_result->setValue($value);
  }

}
