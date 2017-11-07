<?php

namespace Drupal\booking\Controller;

use Drupal\Core\Controller\ControllerBase;
use \Drupal\booking\Functions\functions;
use Symfony\Component\HttpFoundation\JsonResponse;

class bookingAjaxController extends ControllerBase {
/**
 * logIn()
 * ajax response
 */
  public function getDay() {
    $client = json_decode($_POST['client'], true);
    $data = [
      'date' => $_POST['date'],
      'client' => $client,
    ];
    return new JsonResponse(functions::getDayDate($data));
  }


}// end of class
