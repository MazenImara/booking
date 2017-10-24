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
        'addWeekForm' => \Drupal::formBuilder()
          ->getForm('Drupal\booking\Form\addWeekForm',$id),
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
    $request = json_decode(file_get_contents("php://input"));
    $table = functions::getTable($request->serviceId);
    return new JsonResponse(functions::getData());
  }
}
