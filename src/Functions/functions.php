<?php

namespace Drupal\booking\Functions;

use  \Drupal\user\Entity\User;

class functions {

  static public function addService($title)
  {
    \Drupal::database()->insert('booking_service')
      ->fields(['title'])
      ->values([$title])
      ->execute();
  }
  static public function addServer($server)
  {
    \Drupal::database()->insert('booking_server')
      ->fields(['title', 'name', 'email', 'phone', 'password', 'status', 'serviceId'])
      ->values([
        $server['title'],
        $server['name'],
        $server['email'],
        $server['phone'],
        $server['password'],
        $server['status'],
        $server['serviceId'],
      ])
      ->execute();
  }
  static public function addClient($client)
  {
    \Drupal::database()->insert('booking_client')
      ->fields(['name', 'email', 'phone', 'password'])
      ->values([
        $client['name'],
        $client['email'],
        $client['phone'],
        $client['password'],
      ])
      ->execute();
    return self::getLastId('booking_client');
  }
  static public function addWorkDays($settings) {

    for ($i=0; $i < $settings['quantity']; $i++) {
      $lastDay = self::getLastDay();
      if ($lastDay != NULL) {
        $lastTime = $lastDay['timeStamp'];
      }
      else{
        $lastTime = REQUEST_TIME;
      }
      $date = date("Y-m-d", $lastTime);
      $newDayTime = strtotime($date. " +1 days") ;
      self::addServiceDays($newDayTime, $settings);
      drupal_set_message('time: '. date("Y-m-d", $newDayTime));
    }
  }

  static public function config($serverId=NULL)
  {
    $config = [];
    $conf = \Drupal::config('booking.settings');
    if ($serverId) {
      $config['alowedBookNum'] = $conf->get($serverId)['alowedBookNum'];
      $config['period'] = $conf->get($serverId)['period'];
      $config['daysOff'] = $conf->get($serverId)['daysOff'];
      $config['dayStart'] = [
        'h' => explode(':',$conf->get($serverId)['dayStart'])[0],
        'm' => explode(':',$conf->get($serverId)['dayStart'])[1],
      ];
      $config['dayEnd'] = [
        'h' => explode(':',$conf->get($serverId)['dayEnd'])[0],
        'm' => explode(':',$conf->get($serverId)['dayEnd'])[1],
      ];
      return $config;
    }
    else {
      $config['alowedBookNum'] = $conf->get('alowedBookNum');
      $config['period'] = $conf->get('period');
      $config['daysOff'] = $conf->get('daysOff');
      $config['dayStart'] = [
        'h' => explode(':',$conf->get('dayStart'))[0],
        'm' => explode(':',$conf->get('dayStart'))[1],
      ];
      $config['dayEnd'] = [
        'h' => explode(':',$conf->get('dayEnd'))[0],
        'm' => explode(':',$conf->get('dayEnd'))[1],
      ];
      return $config;
    }
  }




  static public function addServerDay($serverId, $daysQuantity, $startDate)
  {
    $config = self::config($serverId);
    $server = self::getServer($serverId);
    for ($i=0; $i < $daysQuantity; $i++) {
      $startDate = strtotime(date('Y-m-d',$startDate)) ;
      $day = self::getDay($server['id'], $startDate);
      if (!$day) {
        self::addDay($startDate, $config, $server['serviceId'], $serverId);
        $dayId = self::getLastId('booking_day');
        self::addSlots($config, $serverId, $dayId, $startDate);
        drupal_set_message($dayId.' no');
      }
      else{
        if (!self::getSlots($day['id'])) {
          self::addSlots($config, $serverId, $day['id'], $day['timeStamp']);
        }
        else{
          drupal_set_message(t('This day already have slots, you can delete them all and try again or add one by one'));
        }
      }
      $startDate = strtotime(date('Y-m-d',$startDate). " +1 days") ;
    }
  }

  static public function addServiceDays($newDayTime, $settings) {
    $day = date("d", $newDayTime);
    $conf = self::config();
    foreach (self::getServiceServers($settings['serviceId']) as $server) {
      self::addDay($newDayTime, $conf, $settings['serviceId'], $server['id']);
      $dayId = self::getLastId('booking_day');
      self::addSlots($conf, $server['id'], $dayId, $newDayTime);
    }
  }

  static public function addDay($newDayTime, $serviceId, $serverId)
  {
    /*
    if($config['daysOff'] != NULL and $config['daysOff'][date('N', $newDayTime)] != '0')
      $status = 0;
    else
      $status = 1;*/
    $status = 1;
    \Drupal::database()->insert('booking_day')
      ->fields(['status', 'serviceId', 'date', 'timeStamp', 'serverId'])
      ->values([$status, $serviceId, date("d-m-Y", $newDayTime), $newDayTime, $serverId])
      ->execute();
  }

  static public function addSlots($conf, $serverId, $dayId, $dayTimeStamp) {
    $period = $conf['period'];
    $slotQuantity = ($conf['dayEnd']['h'] - $conf['dayStart']['h']) / ($period/60);
    $diffMin = $conf['dayEnd']['m'] - $conf['dayStart']['m'];
    $startTime = $dayTimeStamp + ($conf['dayStart']['h']*60*60) + ($conf['dayStart']['m']*60);
    for ($i=0; $i < $slotQuantity; $i++) {
      if (($i + 1)==$slotQuantity)
        $period = $period + $diffMin;
      $slot = ['dayId' => $dayId, 'period' => $period, 'status' => 1, 'startTime' => $startTime, 'serverId' => $serverId];
      $startTime = $startTime + ($period * 60);
      $slot['endTime'] = $startTime;
    \Drupal::database()->insert('booking_slot')
      ->fields(['period', 'dayId', 'status', 'serverId', 'startTime', 'endTime'])
      ->values([$slot['period'], $slot['dayId'], $slot['status'], $slot['serverId'], $slot['startTime'], $slot['endTime']])
      ->execute();
    }
  }

  static public function addSlot($slot) {
    $day = self::getDayByDateServerIdServiceId($slot['dayDate'], $slot['serverId'], $slot['serviceId']);
    if ($day == Null) {
      $dayTime = strtotime($slot['dayDate']);
      self::addDay($dayTime,$slot['serviceId'], $slot['serverId']);
      $day = self::getDayById(self::getLastId('booking_day'));
    }
    $startTime =$day['timeStamp'] + self::timeToStamp($slot['startTime']);
    $endTime = $day['timeStamp'] + self::timeToStamp($slot['endTime']);
    \Drupal::database()->insert('booking_slot')
      ->fields(['period', 'dayId', 'status', 'serverId', 'startTime', 'endTime'])
      ->values(['60', $day['id'], 1, $slot['serverId'], $startTime, $endTime])
      ->execute();
    return date('Y-m-d H:i', $endTime);
  }
  static public function timeToStamp($time) {
    $h = explode(':', $time)[0];
    $m = explode(':', $time)[1];
    return ($h * 60 * 60) + ($m * 60);
  }

  static public function deleteSlots()
  {
    $query = \Drupal::database()->delete('booking_slot', [])->execute();
    $query = \Drupal::database()->delete('booking_day', [])->execute();
    drupal_set_message('all data deleted');
  }

  static public function addBook($book)
  {
    \Drupal::database()->insert('booking_book')
      ->fields(['slotId', 'serviceId', 'clientId'])
      ->values([$book['slotId'], $book['serviceId'], $book['client']['id']])
      ->execute();
    self::changeSlotStatus($book['slotId'], 0);
  }

  static public function deleteBook($slotId)
  {
    \Drupal::database()->delete('booking_book', [])
      ->condition('slotId', $slotId)
      ->execute();
    self::changeSlotStatus($slotId, 1);
  }

  static public function changeSlotStatus($id, $status) {
    \Drupal::database()->update('booking_slot')
      ->condition('id', $id)
      ->fields(['status' => $status ])
      ->execute();
  }




  // get functions
  static function getLastId($table)
  {
    $id = NULL;
    $result = \Drupal::database()->select($table, 'q')
      ->fields('q', ['id'])
      ->orderBy('id', 'DESC')
      ->range(0,1)
      ->execute();
      while ($row = $result->fetchAssoc()) {
        $id = $row['id'];
      }
    return $id;
  }
  static public function getServices($id=NULL) {
    $services = NULL;
    $query = \Drupal::database()->select('booking_service', 'q')
      ->fields('q', ['id', 'title']);
    if ($id) {
      $result = $query->condition('id', [$id])->execute();
      while ($row = $result->fetchAssoc()) {
        $services = [
          'id' => $row['id'],
          'title' => $row['title'],
        ];
      }
    }
    else {
      $result = $query->execute();
      $services = [];
      while ($row = $result->fetchAssoc()) {
        array_push($services, [
            'id' => $row['id'],
            'title' => $row['title'],
          ]);
      }
    }
    return $services;
  }
  static public function getServiceServers($serviceId) {
    $servers = [];
    $result = \Drupal::database()->select('booking_server', 'q')
      ->fields('q', ['id', 'title', 'name', 'email', 'phone', 'password', 'status'])
      ->condition('serviceId', $serviceId)
      ->execute();
      while ($row = $result->fetchAssoc()) {
        array_push($servers, [
            'id' => $row['id'],
            'title' => $row['title'],
            'name' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'password' => $row['password'],
            'status' => $row['status'],
            'serviceId' => $serviceId,
        ]);
      }
    return $servers;
  }

  static public function getLastDay() {
    $day = NULL;
    $result = \Drupal::database()->select('booking_day', 'q')
      ->fields('q', ['id', 'status', 'serviceId', 'timeStamp'])
      ->orderBy('timeStamp', 'DESC')
      ->range(0,1)
      ->execute();
      while ($row = $result->fetchAssoc()) {
        $day = [
          'id' => $row['id'],
          'status' => $row['status'],
          'serviceId' => $row['serviceId'],
          'timeStamp' => $row['timeStamp'],
        ];
      }
    return $day;
  }

  static public function getDay($serverId, $timeStamp) {
    $day = NULL;
    $result = \Drupal::database()->select('booking_day', 'q')
      ->fields('q', ['id', 'status', 'serviceId', 'timeStamp'])
      ->condition('serverId', $serverId)
      ->condition('timeStamp', $timeStamp)
      ->execute();
      while ($row = $result->fetchAssoc()) {
        $day = [
          'id' => $row['id'],
          'status' => $row['status'],
          'serviceId' => $row['serviceId'],
          'timeStamp' => $row['timeStamp'],
        ];
      }
    return $day;
  }

  static public function getDayById($dayId) {
    $day = NULL;
    $result = \Drupal::database()->select('booking_day', 'q')
      ->fields('q', ['id', 'status', 'serviceId', 'timeStamp'])
      ->condition('id', $dayId)
      ->execute();
      while ($row = $result->fetchAssoc()) {
        $day = [
          'id' => $row['id'],
          'status' => $row['status'],
          'serviceId' => $row['serviceId'],
          'timeStamp' => $row['timeStamp'],
        ];
      }
    return $day;
  }

  static public function getDayByDateServerIdServiceId($date,$serverId, $serviceId) {
    $timeStamp = strtotime(date($date));
    $day = NULL;
    $result = \Drupal::database()->select('booking_day', 'q')
      ->fields('q', ['id', 'status', 'serviceId', 'timeStamp'])
      ->condition('timeStamp', $timeStamp)
      ->condition('serverId', $serverId)
      ->condition('serviceId', $serviceId)
      ->execute();
      while ($row = $result->fetchAssoc()) {
        $day = [
          'id' => $row['id'],
          'status' => $row['status'],
          'serviceId' => $row['serviceId'],
          'timeStamp' => $row['timeStamp'],
        ];
      }
    return $day;
  }

  static public function getSlots($dayId)
  {
    $slots = [];
    $result = \Drupal::database()->select('booking_slot', 'q')
      ->fields('q', ['id', 'period', 'dayId', 'status', 'serverId', 'startTime', 'endTime'])
      ->condition('dayId', [$dayId])
      ->execute();
      while ($row = $result->fetchAssoc()) {
        array_push($slots, [
            'id' => $row['id'],
            'period' => $row['period'],
            'dayId' => $row['dayId'],
            'status' => $row['status'],
            'startTime' => $row['startTime'],
            'endTime' => $row['endTime'],
            'server' => self::getServer($row['serverId']),
          ]);
      }

    return $slots;
  }

  static public function getSlotsByServer($serverId)
  {
    $slots = [];
    $result = \Drupal::database()->select('booking_slot', 'q')
      ->fields('q', ['id', 'period', 'dayId', 'status', 'serverId', 'startTime'])
      ->condition('serverId', $serverId)
      ->execute();
      while ($row = $result->fetchAssoc()) {
        array_push($slots, [
            'id' => $row['id'],
            'period' => $row['period'],
            'dayId' => $row['dayId'],
            'status' => $row['status'],
            'book' =>self::getBookBySlotId($row['id']),
            'startTimeStamp' => $row['startTime'],
            'startTime' => date("H:i",$row['startTime']),
            'endTime' => date("H:i",$row['endTime']),
            'year' => date('Y', $row['startTime']),
            'month' => date('m', $row['startTime']),
            'day' => date('d', $row['startTime']),
            'serverId' => $row['serverId'],
          ]);
      }
    return $slots;
  }

  static public function getSlotsByDayServer($dayId, $serverId) {
    $slots = [];
    $result = \Drupal::database()->select('booking_slot', 'q')
      ->fields('q', ['id', 'period', 'dayId', 'status', 'serverId', 'startTime', 'endTime'])
      ->condition('serverId', $serverId)
      ->condition('dayId', $dayId)
      ->execute();
      while ($row = $result->fetchAssoc()) {
        array_push($slots, [
            'id' => $row['id'],
            'period' => $row['period'],
            'dayId' => $row['dayId'],
            'status' => $row['status'],
            'book' =>self::getBookBySlotId($row['id']),
            'startTimeStamp' => $row['startTime'],
            'startTime' => date("H:i",$row['startTime']),
            'endTime' => date("H:i",$row['endTime']),
            'year' => date('Y', $row['startTime']),
            'month' => date('m', $row['startTime']),
            'day' => date('d', $row['startTime']),
            'serverId' => $row['serverId'],
          ]);
      }

    return $slots;
  }

  static public function getSlotsById($id) {
    $slot = NULL;
    $result = \Drupal::database()->select('booking_slot', 'q')
      ->fields('q', ['id', 'period', 'dayId', 'status', 'serverId', 'startTime', 'endTime'])
      ->condition('id', $id)
      ->execute();
      while ($row = $result->fetchAssoc()) {
        $slot = [
            'id' => $row['id'],
            'period' => $row['period'],
            'dayId' => $row['dayId'],
            'status' => $row['status'],
            'startTimeStamp' => $row['startTime'],
            'endTimeStamp' => $row['endTime'],
            'startTime' => date("H:i",$row['startTime']),
            'endTime' => date("H:i",$row['endTime']),
            'server' => self::getServer($row['serverId']),
            'date' => date("D d M Y",$row['startTime']),
          ];
      }

    return $slot;
  }
  static public function getServer($id) {
    $server = NULL;
    /*
    $result = \Drupal::database()->select('booking_server', 'q')
      ->fields('q', ['id', 'title', 'name', 'email', 'phone', 'password', 'status', 'serviceId'])
      ->condition('id', [$id])
      ->execute();
      while ($row = $result->fetchAssoc()) {
        $server = [
          'id' => $row['id'],
          'title' => $row['title'],
          'name' => $row['name'],
          'email' => $row['email'],
          'phone' => $row['phone'],
          'password' => $row['password'],
          'status' => $row['status'],
          'serviceId' => $row['serviceId'],
        ];
      }
      */
      $user = \Drupal\user\Entity\User::load($id);
      $server = [
        'id' => $id,
        'title'=> 'test',
        'name' => $user->getUsername(),
        'email'=>'',
        'phone'=> ''
      ];

    return $server;
  }
  // is function


  static public function isSlotBooked($book) {
    $bookedSlot = NULL;
    $result = \Drupal::database()->select('booking_book', 'q')
      ->fields('q', ['id', 'slotId', 'serviceId', 'clientId'])
      ->condition('slotId', $book['slotId'])
      ->condition('serviceId', $book['serviceId'])
      ->execute();
    while ($row = $result->fetchAssoc()) {
      $bookedSlot =  [
        'id' => $row['id'],
        'slotId' => $row['slotId'],
        'serviceId' => $row['serviceId'],
        'clientId' => $row['clientId'],
      ];
    }

    return $bookedSlot;
  }

  static public function isEmailExist($email, $table) {
    $id = NULL;
    $result = \Drupal::database()->select($table, 'q')
      ->fields('q', ['id'])
      ->condition('email', $email)
      ->execute();
    while ($row = $result->fetchAssoc()) {
      $id =  [
        'id' => $row['id'],
        'email' => $email,
      ];
    }
    return $id;
  }

  static public function getBookBySlotId($slotId) {
    $book = NULL;
    $result = \Drupal::database()->select('booking_book', 'q')
      ->fields('q', ['id', 'serviceId', 'clientId'])
      ->condition('slotId', $slotId)
      ->execute();
    while ($row = $result->fetchAssoc()) {
      $book =  [
        'id' => $row['id'],
        'slotId' => $slotId,
        'serviceId' => $row['serviceId'],
        'client' => self::getClient($row['clientId']),
      ];
    }
    return $book;
  }

  static public function getClient($id) {
    $client = NULL;
    $result = \Drupal::database()->select('booking_client', 'q')
      ->fields('q', ['id', 'name', 'email', 'phone', 'password'])
      ->condition('id', $id)
      ->execute();
    while ($row = $result->fetchAssoc()) {
      $client =  [
        'id' => $row['id'],
        'name' => $row['name'],
        'email' => $row['email'],
        'phone' => $row['phone'],
        'password' => $row['password'],
      ];
    }
    return $client;
  }

  static public function getExtraFields() {
    $fields = [];
    $conf = \Drupal::config('booking.settings');
    $fields = [
      'serverFields' => $conf->get('serverFields'),
      'clientFields' => $conf->get('clientFields'),
    ];
    return $fields;
  }

  static public function getExtraFiels($objType, $objId)
  {
    $fields = [];
    $result = \Drupal::database()->select('booking_extra_detail', 'q')
      ->fields('q', ['id', 'objId', 'objType', 'title', 'value', 'type'])
      ->condition('objType', $objType)
      ->condition('objId', $objId)
      ->execute();
      while ($row = $result->fetchAssoc()) {
        array_push($fields, [
            'id' => $row['id'],
            'objId' => $row['objId'],
            'objType' => $row['objType'],
            'title' => $row['title'],
            'value' => $row['value'],
            'type' => $row['type'],

          ]);
      }

    return $fields;
  }

// get functions
  // ajax




  static public function book($book) {
    if (self::isSlotBooked($book) == NULL && self::isEmailExist($book['client']['email'], 'booking_client') != NULL) {
      $a = ['book' => 'You have booked'];
      self::addBook($book);

    }
    else{
      $a = $book['client'];
    }

    return $a;
  }

  static public function cancel($book) {
    if (self::isSlotBooked($book) != NULL && $book['client']['id'] == self::getBookBySlotId($book['slotId'])['client']['id']) {
      $a = ['book' => 'You have canceled'];
      self::deleteBook($book['slotId']);

    }
    else{
      $a = ['book' => 'You can not cancel'];
    }

    return $a;
  }

  static public function clientSignUp($client) {
    if (self::isEmailExist($client['email'], 'booking_client') == NULL) {
      $id = self::addClient($client);
      $client['id'] = $id;
      self::addClientExtraFields($client);
    }
    else{
      $client = NULL;
    }
    return $client;
  }
  static public function addClientExtraFields($client)
  {
    $extraFields = self::getExtraFields()['clientFields'];
    foreach ($extraFields as $field) {
      \Drupal::database()->insert('booking_extra_detail')
        ->fields(['objId', 'objType', 'title', 'value', 'type'])
        ->values([$client['id'], 'client', $field, $client[$field], 'text'])
        ->execute();
    }
  }
  static public function clientLogIn($log) {
    $client = NULL;
    $result = \Drupal::database()->select('booking_client', 'q')
      ->fields('q', ['id', 'name', 'email', 'phone', 'password'])
      ->condition('email', $log['email'])
      ->condition('password', $log['password'])
      ->execute();
      while ($row = $result->fetchAssoc()) {
        $client = [
          'id' => $row['id'],
          'name' => $row['name'],
          'email' => $row['email'],
          'phone' => $row['phone'],
          'password' => $row['password'],
        ];
      }
    return $client;
  }

  static public function getDayDataServer($data) {
    $days = [];
    $result = \Drupal::database()->select('booking_day', 'q')
      ->fields('q', ['id', 'status', 'serviceId', 'timeStamp', "date"])
      ->condition('date', $data['date'])
      ->condition('serverId', $data['serverId'])
      ->execute();
    while ($row = $result->fetchAssoc()) {
      array_push($days, [
        'id' => $row['id'],
        'serviceId' => $row['serviceId'],
        'status' => $row['status'],
        'timeStamp' => $row['timeStamp'],
        'date' => $row['date'],
        'slots' => self::getSlotsByDayServer($row['id'], $data['serverId']),
      ]);
    }
    return $days;

  }

  static public function getDayDate($data) {
    $allSlots =[];
    $days = [];
    $result = \Drupal::database()->select('booking_day', 'q')
      ->fields('q', ['id', 'status', 'serviceId', 'timeStamp', "date"])
      ->condition('date', $data['date'])
      ->condition('serviceId', $data['serviceId'])
      ->execute();
    while ($row = $result->fetchAssoc()) {
      array_push($days ,[
        'id' => $row['id'],
        'serviceId' => $row['serviceId'],
        'status' => $row['status'],
        'timeStamp' => $row['timeStamp'],
        'date' => $row['date'],
        'slots' => [],
      ]);
    }
    foreach ($days as $day) {
      if ($day['status'] != '0' and (float)strtotime($data['date']) >=  (float)strtotime(date('d-m-Y', REQUEST_TIME))){
        $slots = self::getSlots($day['id']);
        foreach ($slots as $slot) {
          $slot['start'] =  date("H:i",$slot['startTime']);
          $slot['end'] = date("H:i",$slot['endTime']);
          $slot['startTimeStamp'] = $slot['startTime'];
          if ($slot['status'] == 1) {
            array_push($allSlots, $slot);
          }
          else{
            if ($data['client']['id'] == self::getBookBySlotId($slot['id'])['client']['id']) {
              array_push($allSlots, $slot);
            }
          }
        }
        $days[0]['slots'] = $allSlots;
      }
    }
    return $days[0];
  }

  static public function getClientBook($client) {
    $books = [];
    $result = \Drupal::database()->select('booking_book', 'q')
      ->fields('q', ['id', 'slotId', 'serviceId', 'clientId'])
      ->condition('clientId', $client['id'])
      ->execute();
    while ($row = $result->fetchAssoc()) {
      $book = [
        'id' => $row['id'],
        'slot' => self::getSlotsById($row['slotId']),
        'serviceId' => $row['serviceId'],
        'clientId' => $row['clientId'],
      ];
      if ((float)$book['slot']['startTimeStamp'] >=  (float)strtotime(date('d-m-Y', REQUEST_TIME))) {
        array_push($books,$book);
      }
    }
    return ['books' => $books , 'status' => 'ok'];
  }

  static public function deleteSlot($slotId) {
    self::deleteBook($slotId);
    \Drupal::database()->delete('booking_slot', [])
      ->condition('id', $slotId)
      ->execute();
  }


  static public function editSlotTime($slotId, $startTime, $endTime) {
    $slot = self::getSlotsById($slotId);
    $start = explode(':', $startTime);
    $end = explode(':', $endTime);
    $startTimeS = strtotime(date('Y-m-d',$slot['startTimeStamp']))+ ($start[0] * 60 * 60) + ($start['1'] * 60);
    $endTimeS = strtotime(date('Y-m-d',$slot['startTimeStamp']))+ ($end[0] * 60 * 60) + ($end['1'] * 60);
    \Drupal::database()->update('booking_slot')
      ->condition('id', $slotId)
      ->fields(['startTime' => $startTimeS,  'endTime' => $endTimeS])
      ->execute();
    return [];
  }


  // test function
  static public function try($name,$value) {
    \Drupal::database()->insert('booking_test')
      ->fields(['value', 'name'])
      ->values([$value, $name])
      ->execute();
  }

  static public function deleteExtraField($field)
  {
    $config_factory = \Drupal::configFactory();
    $config = $config_factory->get('booking.settings');
    $fields = $config->get($field['objType']);
    foreach ($fields as $key => $value) {
      if ($value == $field['title']) {
        array_splice($fields, $key);
      }
    }
    $config_factory->getEditable('booking.settings')->set($field['objType'], $fields)->save();
  }

  static public function getDrupalUsers() {
    $users = [];
    $ids = \Drupal::entityQuery('user')
    ->condition('status', 1)
    ->condition('roles', 'rosenserien')
    ->execute();
    foreach ($ids as $id) {
      $user = \Drupal\user\Entity\User::load($id);
      array_push($users, [
        'id' => $id,
        'name' => $user->get('name')->value,
      ]);
    }
    return $users;
  }

} //end of class
