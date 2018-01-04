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
      '#content'    => [
        'clientExtraField' => functions::getExtraFields()['clientFields'],
      ],
    ];
  }
  public function admin() {
    $user = \Drupal::currentUser();
    //$userE =  \Drupal\user\Entity\User::load($user->id());
    if (in_array('administrator', $user->getRoles())) {
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
          'user' => $user->getRoles()[1],
        ],
      ];
    }
    else{
      if(in_array('booking', $user->getRoles())){
        $response = new RedirectResponse('/booking/server/'.$user->id());
        $response->send();
      }
      else {
        return new JsonResponse(['status'=>$user->getRoles()]);
      }
    }
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
        'drupalServers' => functions::getDrupalUsers(),
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
    $user =  \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
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
        'user' => $user->get('uid')->value,
        'services' => functions::getServices(),
      ],
    ];
  }

  public function clients($slotId) {
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
      '#theme'      => 'clients',
      '#content'    => [
        'books' => functions::getBooksBySlotId($slotId),
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
        'extraFields' => functions::getExtraFiels('client', $id),
      ],
    ];
  }

  public function setting() {
    $fields = functions::getExtraFields();
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
      '#theme'      => 'setting',
      '#content'    => [
        'serverFields' => $fields['serverFields'],
        'clientFields' => $fields['clientFields'],
        'addExtraFieldForm' => \Drupal::formBuilder()
          ->getForm('Drupal\booking\Form\addExtraFieldForm'),
        'settingsForm' => \Drupal::formBuilder()
          ->getForm('Drupal\booking\Form\SettingsForm'),
      ],
    ];
  }

  public function deleteExtraField() {
    $field = [
      'title' => $_POST['title'],
      'objType' => $_POST['objType'],
    ];
    functions::deleteExtraField($field);
    $response = new RedirectResponse('/booking/setting');
    $response->send();
  }

}// end of class
