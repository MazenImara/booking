<?php

namespace Drupal\booking\Controller;

use Drupal\Core\Controller\ControllerBase;
use \Drupal\booking\Functions\functions;
use Symfony\Component\HttpFoundation\JsonResponse;

class bookingAppController extends ControllerBase {
/**
 * logIn()
 * ajax response
 */
  public function test() {
    $data = ['name' => $_POST['name']];
    return new JsonResponse($data);
  }


}// end of class
