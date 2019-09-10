<?php

namespace Drupal\audit_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;
use Drupal\monitoring\Entity\SensorConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Monitors list of modules.
 *
 * @SensorPlugin(
 *   id = "list_of_modules",
 *   label = @Translation("List of modules"),
 *   description = @Translation("Monitors installed modules."),
 *   addable = TRUE
 * )
 */
class ListOfModules extends SensorPluginBase {

  /**
   * Local variable that contains the list of modules.
   *
   * @var moduleHandler
   */
  private $moduleHandler;

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
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The moduleHandler.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $form_result
   *   The formResult.
   */
  public function __construct(SensorConfig $sensor_config, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler, ConfigFactoryInterface $form_result) {
    parent::__construct($sensor_config, $plugin_id, $plugin_definition);
    $this->moduleHandler = $module_handler;
    $this->formResult = $form_result;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, SensorConfig $sensor_config, $plugin_id, $plugin_definition) {
    /* @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
    $module_handler = $container->get('module_handler');
    /* @var \Drupal\Core\Config\ConfigFactoryInterface $form_result */
    $form_result = $container->get('config.factory');
    return new static(
      $sensor_config,
      $plugin_id,
      $plugin_definition,
      $module_handler,
      $form_result

    );
  }

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    // List the common modules that must be installed.
    $common_modules = [
      'maillog' => 'maillog',
      'honeypot' => 'honeypot',
      'metatag' => 'metatag',
      'redirect' => 'redirect',
      'pathauto' => 'pathauto',
      'color' => 'color',
      'comment' => 'comment',
      'devel' => 'devel',
      'field_ui' => 'field_ui',
      'tour' => 'tour',
      'views_ui' => 'views_ui',
      'menu_ui' => 'menu_ui',
      'dblog' => 'dblog',
      'shortcut' => 'shortcut',
      'automated_cron' => 'automated_cron',
      'help' => 'help',
      'admin_toolbar' => 'admin_toolbar',
      'admin_toolbar_tools' => 'admin_toolbar_tools',
      'coffee' => 'coffee',
      'environment_indicator' => 'environment_indicator',
      'ultimate_cron' => 'ultimate_cron',
    ];
    // List the deployment modules that must be installed.
    $deployment_modules = [
      'default_content' => 'default_content',
      'entity_share' => 'entity_share',
      'fixed_block_content' => 'fixed_block_content',
    ];
    $form['common_modules'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('list of common modules to include for control'),
      '#options' => $common_modules,
      '#default_value' => $common_modules,
    ];
    $form['deployment_modules'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('list of deployment modules to include for control'),
      '#options' => $deployment_modules,
      '#default_value' => $deployment_modules,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->formResult = $form_state->getValue('settings');
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function runSensor(SensorResultInterface $sensor_result) {
    $sensor_result->setStatus(SensorResultInterface::STATUS_OK);
    $value = 0;
    // The common & deployment modules selected on settings.
    $common_machine_names = [];
    $deployment_machine_names = [];
    // Get the modules list names.
    $installed_modules = $this->moduleHandler->getModuleList();
    $installed_modules_names = array_keys($installed_modules);
    // Get an array with the return of the form.
    $form_config = $this->formResult->get('monitoring.sensor_config.enabled_modules')
      ->get('settings');
    $form_config_common_modules = $form_config['common_modules'];
    $form_config_deployment_modules = $form_config['deployment_modules'];
    foreach ($form_config_common_modules as $form_config_common_module => $machine_name) {
      if (!empty($machine_name)) {
        $common_machine_names[] = $machine_name;
      }
    }
    foreach ($form_config_deployment_modules as $form_config_deployment_module => $machine_name) {
      if (!empty($machine_name)) {
        $deployment_machine_names[] = $machine_name;
      }
    }
    // Compare $ common_modules and $ deployment_modules with module list
    // installed and return modules that Are not present.
    $diff_common_modules = array_diff($common_machine_names, $installed_modules_names);
    $diff_deployment_modules = array_diff($deployment_machine_names, $installed_modules_names);
    if (!empty($diff_common_modules)) {
      $sensor_result->addStatusMessage($this->t('note the following common modules are not installed or enabled : @common'), [
        '@common' => implode(', ', $diff_common_modules),
      ]);
      $sensor_result->setStatus(SensorResultInterface::STATUS_WARNING);
      $value++;
    }
    if (!empty($diff_deployment_modules)) {
      $sensor_result->addStatusMessage($this->t('note the following deployment modules are not installed or enabled : @deployment'), [
        '@deployment' => implode(', ', $diff_deployment_modules),
      ]);
      $sensor_result->setStatus(SensorResultInterface::STATUS_WARNING);
      $value++;
    }
    if (empty($value)) {
      $sensor_result->addStatusMessage($this->t('all the selected modules are enabled'));
      $sensor_result->setStatus(SensorResultInterface::STATUS_OK);
    }
    $sensor_result->addStatusMessage(t('list of the enabled modules : @enabled'), [
      '@enabled' => implode(', ', $installed_modules_names),
    ]);
  }

}
