<?php

namespace Drupal\booking\Controller;

use Drupal\Core\Controller\ControllerBase;
use \Drupal\booking\Functions\functions;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class bookingController extends ControllerBase {
  /**
   * Display the markup.
   *
   * @return array
   */
  public function booking($serviceId) {
    //functions::deleteSlots();
    return [
      '#attached' => [
        'library' => [
          'booking/booking_booking',
        ],
        'drupalSettings' => [
          'booking' => [
            'content' => ['serviceId' => $serviceId],
          ]
        ]
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

  public function bookingServices() {
    $services = functions::getServices();
    if (count($services) <= 1) {
      $response = new RedirectResponse('/booking/'.$services[0]['id']);
      $response->send();
    }
    return [
      '#attached' => [
        'library' => [
          'booking/booking_lib',
        ],
      ],
      '#theme'      => 'booking_services',
      '#content'    => [
        'services' => $services,
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
            'content' => [
              'serverId' => $id,
              'serviceId'=> functions::getServer($id)['serviceId'],
            ],
          ]
        ]
      ],
      '#theme'      => 'server',
      '#content'    => [
        'server' => functions::getServer($id),
        'addServerDayForm' => \Drupal::formBuilder()
          ->getForm('Drupal\booking\Form\addServerDayForm',$id),
      ],
    ];
  }

  public function client($id) {
    return [
      '#attached' => [
        'library' => [
          'booking/booking_server',
        ],
        'drupalSettings' => [
          'booking' => [
            'content' => [
              'serverId' => '$id',
              'serviceId'=> '',
            ],
          ]
        ]
      ],
      '#theme'      => 'client',
      '#content'    => [
        'client' => functions::getClient($id),
      ],
    ];
  }

}// end of class
