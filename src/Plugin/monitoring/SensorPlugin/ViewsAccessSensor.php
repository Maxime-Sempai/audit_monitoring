<?php

namespace Drupal\audit_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Entity\View;
use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;

/**
 * Monitors check_views_access channels for last execution.
 *
 * @SensorPlugin(
 *   id = "check_views_access",
 *   label = @Translation("Views access sensor"),
 *   description = @Translation("Check Views access sensor."),
 *   addable = FALSE
 * )
 */
class ViewsAccessSensor extends SensorPluginBase {

  /**
   * Contain the following views to exclude.
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
    // If views is not enabled return with INFO.
    /* @var View[] $views */
    $views = View::loadMultiple();
    $value = 0;
    $exclude = $this->sensorConfig->getSetting('views');
    // Iterate through views and their displays.
    foreach ($views as $view_name => $view) {
      if (!$view->status() || $exclude[$view_name]) {
        continue;
      }
      foreach ($view->get('display') as $display_name => $display) {
        $access = &$display['display_options']['access'];
        if (isset($access) && $access['type'] == 'none') {
          // Access is not controlled for this display.
          $sensor_result->addStatusMessage($this->t('Access is not controlled for the display @display of the view @view'), [
            '@display' => $display_name,
            '@view' => $view->id(),
          ]);
          $value++;
          $sensor_result->setStatus(SensorResultInterface::STATUS_CRITICAL);
        }
      }
    }
    $sensor_result->setValue($value);
  }

}
