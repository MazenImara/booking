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
          'booking/booking_booking',
        ],
      ],
      '#theme'      => 'booking',
      '#content'    => 'functions::getWeeks(1)',
    ];
  }
  public function admin() {
    return [
      '#attached' => [
        'library' => [
          'booking/booking_lib',
        ],
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

  public function server($id) {
    return [
      '#attached' => [
        'library' => [
          'booking/booking_server',
        ],
        'drupalSettings' => [
          'booking' => [
            'content' => functions::getDataServer($id),
          ]
        ]
      ],
      '#theme'      => 'server',
      '#content'    => [
        'server' => functions::getserver($id),
        'data' => functions::getDataServer($id),
      ],
    ];
  }
/**
 * getTable()
 * ajax response
 */
  public function getDataClient() {
    return new JsonResponse(functions::getDataClient($_POST['cookieClient']));
  }
/**
 * book()
 * ajax response
 */
  public function book() {
    $book = [
      'slotId' => $_POST['slotId'],
      'serviceId' => $_POST['serviceId'],
      'client' => $_POST['client'],
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
/**
 * isExist()
 * ajax response
 */
  public function isEmailExist() {
    return new JsonResponse(functions::isEmailExist($_POST['isEmailExist']['email'],$_POST['isEmailExist']['table']));
  }

/**
 * signUp()
 * ajax response
 */
  public function signUp() {
    $client = [
      'name' => $_POST['name'],
      'phone' => $_POST['phone'],
      'email' => $_POST['email'],
      'password' => $_POST['password'],
    ];

    return new JsonResponse(functions::clientSignUp($client));
  }

/**
 * logIn()
 * ajax response
 */
  public function logIn() {
    $log = [
      'email' => $_POST['email'],
      'password' => $_POST['password'],
    ];

    return new JsonResponse(functions::clientLogIn($log));
  }


}// end of class
