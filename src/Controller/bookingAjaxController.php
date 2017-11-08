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
    if ($client == NULL) {
      $client = $_POST['client'];
    }
    $data = [
      'date' => $_POST['date'],
      'client' => $client,
    ];
    if ($data['date'] == NULL && $data['client'] == NULL) {
      $request = json_decode(file_get_contents("php://input"));
      $data = [
        'date' => $request->date,
        'client' => json_decode(json_encode($request->client), True),
      ];
    }
    //print_r($data);
    //return new JsonResponse($data);
    return new JsonResponse(functions::getDayDate($data));
  }


}// end of class
