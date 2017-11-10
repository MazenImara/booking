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
    $client = json_decode($_POST['client'], true);
    if ($client == NULL) {
      $client = $_POST['client'];
    }
    $book = [
      'slotId' => $_POST['slotId'],
      'serviceId' => $_POST['serviceId'],
      'client' => $client,
    ];
    return new JsonResponse(functions::book($book));
  }
/**
 * cancel()
 * ajax response
 */
  public function cancel() {
    $client = json_decode($_POST['client'], true);
    if ($client == NULL) {
      $client = $_POST['client'];
    }
    $book = [
      'slotId' => $_POST['slotId'],
      'serviceId' => $_POST['serviceId'],
      'client' => $client,
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
