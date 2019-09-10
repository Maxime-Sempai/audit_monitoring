<?php

namespace Drupal\audit_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\Core\Form\FormStateInterface;
use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;
use Drupal\views\Entity\View;

/**
 * Monitors views_caches_sensor channels for last execution.
 *
 * @SensorPlugin(
 *   id = "views_caches_sensor",
 *   label = @Translation("Check Views caches"),
 *   description = @Translation("Check Views caches sensor."),
 *   addable = TRUE
 * )
 */
class ViewsCachesSensor extends SensorPluginBase {

  /**
   * Contain the list of views to exclude.
   *
   * @var array
   */
  protected $exclude = [];

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // Form that lists all the views that wants to exclude from control.
    $form = parent::buildConfigurationForm($form, $form_state);
    $views = View::loadMultiple();
    $options = [];
    foreach ($views as $view_name => $view) {
      $options[$view_name] = $view->label();
    }
    $form['views'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Exclude following views'),
      '#options' => $options,
      '#default_value' => $this->sensorConfig->getSetting('views'),
    ];
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function runSensor(SensorResultInterface $sensor_result) {
    // If views is not cached.
    /* @var View[] $views */
    $views = View::loadMultiple();
    $value = 0;
    $exclude = $this->sensorConfig->getSetting('views');
    // Check for each view is in cache.
    foreach ($views as $view_name => $view) {
      if (!$view->get('display') || $exclude[$view_name]) {
        continue;
      }
      foreach ($view->get('display') as $display_name => $display) {
        $cache = &$display['display_options']['cache'];
        if ($cache['type'] == 'none') {
          // Access is not controlled for this display.
          $sensor_result->addStatusMessage($this->t('This views : @view is not cached'), [
            '@view' => $view->id(),
          ]);
          $value++;
          break;
        }
      }
    }
    $sensor_result->setValue($value);
  }

}
