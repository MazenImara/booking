<?php

namespace Drupal\booking\Form;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Drupal\booking\Functions\functions;

/**
 * Configure statistics settings for this site.
 */
class addServerDayForm extends ConfigFormBase {

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
    return 'bookingAddServerDayForm';
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
  public function buildForm(array $form, FormStateInterface $form_state, $serverId=NULL) {
    $config = $this->config('booking.settings');
    $form['serverId'] = [
      '#type' => 'hidden',
      '#title' => t('serverId'),
      '#default_value' => $serverId,
      '#required' => "True",
    ];

    $form['quantity'] = [
      '#type' => 'textfield',
      '#title' => t('Quantity'),
      '#placeholder' => t('quantity'),
      '#default_value' => 1,
      '#description' => t('How many days to add.'),
      '#size' => 4,
      '#required' => "True",
    ];
    $options = [];
    foreach (functions::getServices() as $item) {
      $options[$item['id']] = $item['title'];
    }
    $form['serviceId'] = [
      '#type' => 'select',
      '#title' => $this->t('Service'),
      '#options' => $options,
    ];

    $form['startDate'] = [
      '#type' => 'textfield',
      '#title' => t('Start date'),
      '#placeholder' => t('Start date'),
      '#description' => t('Pick date to start from.'),
      '#size' => 10,
      '#required' => "True",
      '#id' => 'startDateDatepicker',
    ];

    $form['alowedBookNum'] = [
      '#type' => 'textfield',
      '#title' => t('Book number'),
      '#placeholder' => '1',
      '#default_value' => $config->get($serverId)['alowedBookNum'],
      '#description' => t('Alowed number of booking for each client.'),
      '#size' => 4,
      '#required' => "True",
    ];

    $form['period'] = [
      '#type' => 'textfield',
      '#title' => t('Period'),
      '#placeholder' => '60',
      '#default_value' => $config->get($serverId)['period'],
      '#description' => t('Period for each slot in minutes ex: 60 min.'),
      '#size' => 4,
      '#required' => "True",
    ];

    $form['dayStart'] = [
      '#type' => 'textfield',
      '#title' => t('Day start time'),
      '#placeholder' => '8:00',
      '#default_value' => $config->get($serverId)['dayStart'],
      '#description' => t('Time for starting working day ex 8:00.'),
      '#size' => 4,
      '#date_date_element' => 'none',
      '#date_time_element' => 'time',
      '#required' => "True",
    ];

    $form['dayEnd'] = [
      '#type' => 'textfield',
      '#title' => t('Day end time'),
      '#placeholder' => '17:00',
      '#default_value' => $config->get($serverId)['dayEnd'],
      '#description' => t('Time for end of working day ex 17:00.'),
      '#size' => 4,
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


    $form['daysOff'] = [
      '#title' => t(''),
      '#type' => 'checkboxes',
      '#description' => t(''),
      '#options' => $options,
      '#default_value' => $config->get($serverId)['daysOff'],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('booking.settings')
      ->set($form_state->getValue('serverId'),
        [
          'alowedBookNum' => $form_state->getValue('alowedBookNum'),
          'period' => $form_state->getValue('period'),
          'dayStart' => $form_state->getValue('dayStart'),
          'dayEnd' => $form_state->getValue('dayEnd'),
          'daysOff' => $form_state->getValue('daysOff'),
        ]
      )
      ->save();

      $startDate = strtotime(date($form_state->getValues()['startDate']));

      functions::addServerDay($form_state->getValues()['serverId'], $form_state->getValues()['quantity'], $startDate, $form_state->getValues()['serviceId']);




    parent::submitForm($form, $form_state);
  }

}
