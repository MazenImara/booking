<?php

namespace Drupal\booking\Controller;

use Drupal\Core\Controller\ControllerBase;
use \Drupal\booking\Functions\functions;

class bookingController extends ControllerBase {
  /**
   * Display the markup.
   *
   * @return array
   */
  public function booking() {
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
      '#theme'      => 'booking',
      '#content'    => 'from booking contr',
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
        'addServerForm' => \Drupal::formBuilder()->getForm('Drupal\booking\Form\addServerForm'),
      ],
    ];
  }
/**
 * getTable()
 * ajax response
 */
  public function getTable($serviceId) {
    $table = functions::getTable();
    return new JsonResponse($table);
  }
}
