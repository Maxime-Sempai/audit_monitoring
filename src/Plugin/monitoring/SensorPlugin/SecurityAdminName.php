<?php

namespace Drupal\audit_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Form\FormStateInterface;
use Drupal\monitoring\Entity\SensorConfig;
use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Monitors username_security channels for last execution.
 *
 * @SensorPlugin(
 *   id = "username_security",
 *   label = @Translation("Username"),
 *   description = @Translation("Checks the security of user names."),
 *   addable = TRUE
 * )
 */
class SecurityAdminName extends SensorPluginBase {

  /**
   * Local variable that contains database connection.
   *
   * @var databaseConnection
   */
  private $databaseConnection;

  /**
   * Local variable that contains the list of modules.
   *
   * @var formResult
   */
  private $formResult;

  /**
   * Instantiates a sensor object.
   *
   * @param \Drupal\monitoring\Entity\SensorConfig $sensor_config
   *   Sensor config object.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Database\Connection $database_connection
   *   The databaseConnection.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $form_result
   *   The formResult.
   */
  public function __construct(SensorConfig $sensor_config, $plugin_id, $plugin_definition, Connection $database_connection, ConfigFactoryInterface $form_result) {
    parent::__construct($sensor_config, $plugin_id, $plugin_definition);

    $this->databaseConnection = $database_connection;
    $this->formResult = $form_result;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, SensorConfig $sensor_config, $plugin_id, $plugin_definition) {
    /* @var \Drupal\Core\Database\Connection $database_connection */
    $database_connection = $container->get('database');
    /* @var \Drupal\Core\Config\ConfigFactoryInterface $form_result */
    $form_result = $container->get('config.factory');
    return new static(
      $sensor_config,
      $plugin_id,
      $plugin_definition,
      $database_connection,
      $form_result
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $common_usernames = [
      '123456' => '123456',
      '111111' => '111111',
      'abc123' => 'abc123',
      'dir' => 'dir',
      'expert' => 'expert',
      'home' => 'home',
      'qwerty' => 'qwerty',
      'test' => 'test',
      'azerty' => 'azerty',
      'utilisateur' => 'utilisateur',
      'utilisateur1' => 'utilisateur1',
      'webmaster' => 'webmaster',
      'contact' => 'contact',
      'entreprise' => 'entreprise',
      'administrateur' => 'administrateur',
      'admin' => 'admin',
      'administrator' => 'administrator',
      'root' => 'root',
      'adm' => 'adm',
      'manager' => 'manager',
      'system' => 'system',
      'superuser' => 'superuser',
    ];
    $form['common_username'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('list of common username to include for control'),
      '#options' => $common_usernames,
      '#default_value' => $common_usernames,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->formResult = $form_state->getValue('settings');
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function runSensor(SensorResultInterface $sensor_result) {

    $common_username_form_result = $this->formResult->get('monitoring.sensor_config.username_security')->get('settings');
    $common_username_selected = $common_username_form_result['common_username'];
    /*
     * Query to search the database for username and compare them with
     * the common user names
     * */
    $or = new Condition('OR');
    foreach ($common_username_selected as $username) {
      $or->condition('u.name', $username, 'LIKE');
    }

    $query = $this->databaseConnection->select('users_field_data', 'u')->fields('u', ['name']);
    $query->condition($or);

    $sensor_result->setValue(FALSE);
    foreach ($query->execute()->fetchAll() as $users_names => $user_name) {
      $sensor_result->addStatusMessage($this->t('username : @username is too common'), [
        '@username' => $user_name->name,
      ]);
      $sensor_result->setValue(TRUE);
    }
  }

}
