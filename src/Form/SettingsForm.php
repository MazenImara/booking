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
class SettingsForm extends ConfigFormBase {

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
    return 'bookingSettingsForm';
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

    $form['alowedBookNum'] = [
      '#type' => 'textfield',
      '#title' => t('Book number'),
      '#placeholder' => '1',
      '#default_value' => $config->get('alowedBookNum'),
      '#description' => t('Alowed number of booking for each client.'),
      '#size' => 20,
      '#required' => "True",
    ];

    $form['period'] = [
      '#type' => 'textfield',
      '#title' => t('Period'),
      '#placeholder' => '60',
      '#default_value' => $config->get('period'),
      '#description' => t('Period for each slot in minutes ex: 60 min.'),
      '#size' => 20,
      '#required' => "True",
    ];

    $form['dayStart'] = [
      '#type' => 'textfield',
      '#title' => t('Day start time'),
      '#placeholder' => '8:00',
      '#default_value' => $config->get('dayStart'),
      '#description' => t('Time for starting working day ex 8:00.'),
      '#size' => 20,
      '#date_date_element' => 'none',
      '#date_time_element' => 'time',
      '#required' => "True",
    ];

    $form['dayEnd'] = [
      '#type' => 'textfield',
      '#title' => t('Day end time'),
      '#placeholder' => '17:00',
      '#default_value' => $config->get('dayEnd'),
      '#description' => t('Time for end of working day ex 17:00.'),
      '#size' => 20,
      '#date_date_element' => 'none',
      '#date_time_element' => 'time',
      '#required' => "True",
    ];

    $options = [];
    $options["1"] = t('Monday');
    $options["2"] = t('Tuesday');
    $options["3"] = t('Wednesday');
    $options["4"] = t('Thursday');
    $options["5"] = t('Friday');
    $options["6"] = t('Saturday');
    $options["7"] = t('Sunday');


    $form['daysOff'] = array(
      '#title' => t(''),
      '#type' => 'checkboxes',
      '#description' => t(''),
      '#options' => $options,
      '#default_value' => ['6', '7'],
      '#required' => "True",
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('booking.settings')
      ->set('alowedBookNum', $form_state->getValue('alowedBookNum'))
      ->set('period', $form_state->getValue('period'))
      ->set('dayEnd', $form_state->getValue('dayEnd'))
      ->set('dayStart', $form_state->getValue('dayStart'))
      ->set('daysOff', $form_state->getValue('daysOff'))
      ->save();




    parent::submitForm($form, $form_state);
  }

}
