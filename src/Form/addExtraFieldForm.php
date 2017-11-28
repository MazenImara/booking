<?php

namespace Drupal\booking\Form;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure statistics settings for this site.
 */
class addExtraFieldForm extends ConfigFormBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a \Drupal\user\SettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler) {
    parent::__construct($config_factory);

    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'addExtraFieldForm';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['booking.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('booking.settings');

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => t('Field title'),
      '#placeholder' => '',
      //'#default_value' => $config->get('title'),
      //'#description' => t('Field title.'),
      '#size' => 40,
      '#required' => "True",
    ];

    $form['objType'] = [
      '#title' => t(''),
      '#type' => 'radios',
      //'#default_value' => $config->get('objType'),
      '#options' => [
        'serverFields' => t('Server'),
        'clientFields' => t('Client'),
      ],
      '#required'=>TRUE
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('booking.settings');
    $fields = $config->get($form_state->getValue('objType'));
    if ($fields == NULL) {
      $config->set($form_state->getValue('objType'),[$form_state->getValue('title')])
      ->save();
    }
    else{
      array_push($fields,$form_state->getValue('title'));
      $config->set($form_state->getValue('objType'),$fields)
      ->save();
    }





    parent::submitForm($form, $form_state);
  }

}
