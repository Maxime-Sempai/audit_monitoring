<?php

namespace Drupal\audit_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\monitoring\Entity\SensorConfig;
use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\PermissionHandlerInterface;

/**
 * Monitors restrict_access channels for last execution.
 *
 * @SensorPlugin(
 *   id = "restrict_access",
 *   label = @Translation("Restrict access by role"),
 *   description = @Translation("Check the restrict access for each role."),
 *   addable = TRUE
 * )
 */
class RestrictAccess extends SensorPluginBase {

  /**
   * Contain object PermissionHandlerInterface.
   *
   * @var restrictAccess
   */
  protected $restrictAccess;

  /**
   * Instantiates a sensor object.
   *
   * @param \Drupal\monitoring\Entity\SensorConfig $sensor_config
   *   Sensor config object.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\user\PermissionHandlerInterface $restrict_access
   *   The restrictAccess.
   */
  public function __construct(SensorConfig $sensor_config, $plugin_id, $plugin_definition, PermissionHandlerInterface $restrict_access) {
    parent::__construct($sensor_config, $plugin_id, $plugin_definition);
    $this->restrictAccess = $restrict_access;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, SensorConfig $sensor_config, $plugin_id, $plugin_definition) {
    /* @var \Drupal\user\PermissionHandlerInterface $restrict_access */
    $restrict_access = $container->get('user.permissions');
    return new static(
      $sensor_config,
      $plugin_id,
      $plugin_definition,
      $restrict_access
    );
  }

  /**
   * {@inheritDoc}
   */
  public function runSensor(SensorResultInterface $sensor_result) {

    $value = 0;
    // Get all permissions.
    $permissions = $this->restrictAccess->getPermissions();
    // Initializing an empty array to collect
    // Get all permissions that are restrict access : TRUE.
    $access_contents = [];
    foreach ($permissions as $permission => $information) {
      // Have only permissions that are restrict access: TRUE.
      if (isset($information['restrict access'])) {
        $access_contents[] = $permission;
      }
    }

    // Get users permissions.
    $user_roles = user_roles();
    $user_roles_permissions = [];
    foreach ($user_roles as $user_role => $role) {
      if (!$role->isAdmin()) {
        $user_roles_permissions[$user_role] = $role->getPermissions();
      }
    }
    // Get the permissions by role.
    $array_restrict_access = [];
    foreach ($user_roles_permissions as $user_permission => $permission) {
      $diff_permissions = array_diff($access_contents, $permission);
      $restrict_access = array_diff($access_contents, $diff_permissions);
      $values_restrict_access = array_values($restrict_access);
      $array_restrict_access[$user_permission] = $values_restrict_access;
    }
    foreach ($array_restrict_access as $role => $permissions) {
      if (!empty($values)) {
        $value++;
        $sensor_result->addStatusMessage($this->t('Permissions: @permissions is enabled for @roles users.'), [
          '@permissions' => implode(',', $permissions),
          '@roles' => $role,
        ]);
      }
    }
    $sensor_result->setValue($value);
  }

}
