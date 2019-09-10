<?php

namespace Drupal\audit_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;

/**
 * Monitors role configuration.
 *
 * @SensorPlugin(
 *   id = "role_configuration",
 *   label = @Translation("Role configuration"),
 *   description = @Translation("Monitors role configuration"),
 *   addable = TRUE
 * )
 */
class RoleConfiguration extends SensorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function runSensor(SensorResultInterface $sensor_result) {
    $sensor_result->setStatus(SensorResultInterface::STATUS_OK);
    $usersRoles = user_roles();
    $anonymousPermissions = $usersRoles['anonymous']->getPermissions();
    $authenticatedPermissions = $usersRoles['authenticated']->getPermissions();

    if (!in_array('use text format restricted_html', $anonymousPermissions)) {
      $sensor_result->addStatusMessage($this->t('HTML permissions for anonymous users are too permissive'));
      $sensor_result->setStatus(SensorResultInterface::STATUS_CRITICAL);
    }
    if (!in_array('use text format basic_html', $authenticatedPermissions)) {
      $sensor_result->addStatusMessage($this->t('HTML permissions for authenticated users are too permissive'));
      $sensor_result->setStatus(SensorResultInterface::STATUS_CRITICAL);
    }
    foreach ($usersRoles as $userRole) {
      $permissions = $userRole->getPermissions();
      if (in_array('use text format full_html', $permissions)) {
        $sensor_result->addStatusMessage($this->t('Warning! other roles than the administrator have full HTML permissions. Please check that it is really necessary.'));
        $sensor_result->setStatus(SensorResultInterface::STATUS_WARNING);
      }
    }
  }

}
