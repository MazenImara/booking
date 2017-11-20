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
            'content' => ['serverId' => $id],
          ]
        ]
      ],
      '#theme'      => 'server',
      '#content'    => [
        'server' => functions::getServiceServers($id),
        'addServerDayForm' => \Drupal::formBuilder()
          ->getForm('Drupal\booking\Form\addServerDayForm',$id),
      ],
    ];
  }

}// end of class
