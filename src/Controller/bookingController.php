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
  public function main() {
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
      '#theme'      => 'main',
      '#content'    => 'from booking contr',
    ];
  }
}
