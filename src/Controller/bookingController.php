<?php

namespace Drupal\booking\Controller;

use Drupal\Core\Controller\ControllerBase;
use \Drupal\booking\Functions\functions;
use Symfony\Component\HttpFoundation\JsonResponse;

class bookingController extends ControllerBase {
  /**
   * Display the markup.
   *
   * @return array
   */
  public function booking() {
    //functions::deleteSlots();
    return [
      '#attached' => [
        'library' => [
          'booking/booking_lib',
        ],
        'drupalSettings' => [
          'booking' => [
            'content' => functions::test(),
          ]
        ]
      ],
      '#theme'      => 'booking',
      '#content'    => 'functions::getWeeks(1)',
    ];
  }
  public function admin() {
    $content = [
      'name' => 'mazen',
      'age' => 29,
    ];
    return [
      '#attached' => [
        'library' => [
          'booking/booking_lib',
        ],
        'drupalSettings' => [
          'booking' => [
            'content' => functions::test(),
          ]
        ]
      ],
      '#theme'      => 'admin',
      '#content'    => [
        'services' => functions::getServices(),
        'addServiceForm' => \Drupal::formBuilder()->getForm('Drupal\booking\Form\addServiceForm'),
      ],
    ];
  }
  public function service($id) {
    return [
      '#attached' => [
        'library' => [
          'booking/booking_lib',
        ],
        'drupalSettings' => [
          'booking' => [
            'content' => functions::test(),
          ]
        ]
      ],
      '#theme'      => 'service',
      '#content'    => [
        'addServerForm' => \Drupal::formBuilder()
          ->getForm('Drupal\booking\Form\addServerForm',$id),
        'addWorkDaysForm' => \Drupal::formBuilder()
          ->getForm('Drupal\booking\Form\addWorkDaysForm',$id),
        'service' => functions::getServices($id),
        'servers' => functions::getServiceServers($id),
        'last' => date("Y-m-d h:i:sa", strtotime("2017-W43-2")+(8 *60*60)) ,
      ],
    ];
  }
/**
 * getTable()
 * ajax response
 */
  public function getTable() {
    return new JsonResponse(functions::getData());
  }
/**
 * book()
 * ajax response
 */
  public function book() {
    $book = [
      'slotId' => $_POST['slotId'],
      'serviceId' => $_POST['serviceId'],
      'clientId' => 1,
    ];
    return new JsonResponse(functions::book($book));
  }
/**
 * cancel()
 * ajax response
 */
  public function cancel() {
    $book = [
      'slotId' => $_POST['slotId'],
      'serviceId' => $_POST['serviceId'],
      'clientId' => 1,
    ];
    return new JsonResponse(functions::cancel($book));
  }

}// end of class
