<?php

namespace Drupal\booking\Functions;

class functions {
  static public function test() {
    return 'from test fun';
  }
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
      ->fields(['title', 'name', 'email', 'phone', 'password', 'status'])
      ->values([
        $server['title'],
        $server['name'],
        $server['email'],
        $server['phone'],
        $server['password'],
        $server['status'],
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

      $year = date('Y',$newDayTime);
      $yearId = self::isYear($year,$settings['serviceId']);
      if ($yearId == NULL) {
        self::addYear($year, $settings);
        $yearId = self::getLastId('booking_year');
      }
      $month = date('m',$newDayTime);
      $monthId = self::isMonth($month, $yearId);
      if ($monthId == NULL) {
        self::addMonth($month, $yearId);
        $monthId = self::getLastId('booking_month');
      }

      self::addDay($newDayTime, $monthId, $settings);


      drupal_set_message('time: '. date("Y-m-d", $newDayTime));
    }
  }

  static public function addYear($year, $settings) {
    \Drupal::database()->insert('booking_year')
      ->fields(['year', 'serviceId'])
      ->values([2017, $settings['serviceId']])
      ->execute();
  }
  static public function addMonth($month, $yearId)
  {
    \Drupal::database()->insert('booking_month')
      ->fields(['month', 'yearId'])
      ->values([$month, $yearId])
      ->execute();
  }
  static public function addDay($newDayTime, $monthId, $settings) {
    $day = date("d", $newDayTime);
    \Drupal::database()->insert('booking_day')
      ->fields(['day', 'monthId', 'status', 'serviceId', 'date', 'timeStamp'])
      ->values([$day, $monthId, 1, $settings['serviceId'], date("d-m-Y", $newDayTime), $newDayTime])
      ->execute();
      $dayId = self::getLastId('booking_day');
      $workTime = ['start' => 8, 'end' => 17]; $serverCount = 1;
      $period = 60; $timeOff = [ 'start' => 12, 'end' => 1];
      $slotQuantity = ($workTime['end'] - $workTime['start']);
      $startTime = strtotime(date("Y/m/d",$newDayTime)) + (8*60*60);
      foreach (self::getServiceServers($settings['serviceId']) as $server) {
        for ($i=0; $i < $slotQuantity; $i++) {
          $slot = ['dayId' => $dayId, 'period' => $period, 'status' => 1, 'startTime' => $startTime, 'serverId' => $server['id']];
          self::addSlot($slot);
          $startTime = $startTime + ($period * 60);
        }
      }
  }

  static public function addSlot($slot) {
    \Drupal::database()->insert('booking_slot')
      ->fields(['period', 'dayId', 'status', 'startTime', 'serverId'])
      ->values([$slot['period'], $slot['dayId'], $slot['status'], $slot['startTime'], $slot['serverId']])
      ->execute();
  }
  static public function deleteSlots()
  {
    $query = \Drupal::database()->delete('booking_slot', [])->execute();
    $query = \Drupal::database()->delete('booking_day', [])->execute();
    $query = \Drupal::database()->delete('booking_month', [])->execute();
    $query = \Drupal::database()->delete('booking_year', [])->execute();
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

  static public function deleteBook($book)
  {
    \Drupal::database()->delete('booking_book', [])
      ->condition('slotId', $book['slotId'])
      ->execute();
    self::changeSlotStatus($book['slotId'], 1);
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
          'years' => self::getYears($row['id']),
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
          ]);
      }

    return $servers;
  }

  static public function getLastDay() {
    $day = NULL;
    $result = \Drupal::database()->select('booking_day', 'q')
      ->fields('q', ['id', 'day', 'monthId', 'status', 'serviceId', 'timeStamp'])
      ->orderBy('id', 'DESC')
      ->range(0,1)
      ->execute();
      while ($row = $result->fetchAssoc()) {
        $day = [
          'id' => $row['id'],
          'day' => $row['day'],
          'monthId' => $row['monthId'],
          'status' => $row['status'],
          'serviceId' => $row['serviceId'],
          'timeStamp' => $row['timeStamp'],
        ];
      }
    return $day;
  }
  static public function getYears($serviceId) {
    $years = [];
    $result = \Drupal::database()->select('booking_year', 'q')
      ->fields('q', ['id', 'year','serviceId'])
      ->condition('serviceId', [$serviceId])
      ->execute();
      while ($row = $result->fetchAssoc()) {
        array_push($years, [
            'id' => $row['id'],
            'year' => $row['year'],
            'serviceId' => $row['serviceId'],
            'months' => self::getMonths($row['id']),
          ]);
      }

    return $years;
  }

  static public function getMonths($yearId) {
    $months = [];
    $result = \Drupal::database()->select('booking_month', 'q')
      ->fields('q', ['id', 'month','yearId'])
      ->condition('yearId', [$yearId])
      ->execute();
    while ($row = $result->fetchAssoc()) {
      array_push($months, [
        'id' => $row['id'],
        'month' => $row['month'],
        'yearId' => $row['yearId'],
        'days' => self::getDays($row['id']),
      ]);
    }

    return $months;
  }
  static public function getDays($monthId) {
    $days = [];
    $result = \Drupal::database()->select('booking_day', 'q')
      ->fields('q', ['id', 'day', 'monthId', 'status', 'serviceId', 'timeStamp'])
      ->condition('monthId', [$monthId])
      ->execute();
    while ($row = $result->fetchAssoc()) {
      array_push($days, [
        'id' => $row['id'],
        'day' => $row['day'],
        'monthId' => $row['monthId'],
        'serviceId' => $row['serviceId'],
        'status' => $row['status'],
        'timeStamp' => $row['timeStamp'],
        'slots' => self::getSlots($row['id']),
      ]);
    }

    return $days;
  }

  static public function getSlots($dayId)
  {
    $slots = [];
    $result = \Drupal::database()->select('booking_slot', 'q')
      ->fields('q', ['id', 'period', 'dayId', 'status', 'serverId', 'startTime'])
      ->condition('dayId', [$dayId])
      ->execute();
      while ($row = $result->fetchAssoc()) {
        array_push($slots, [
            'id' => $row['id'],
            'period' => $row['period'],
            'dayId' => $row['dayId'],
            'status' => $row['status'],
            'startTime' => $row['startTime'],
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
            'endTime' => date("H:i",$row['startTime'] + ($row['period'] * 60)),
            'year' => date('Y', $row['startTime']),
            'month' => date('m', $row['startTime']),
            'day' => date('d', $row['startTime']),
            'serverId' => $row['serverId'],
          ]);
      }

    return $slots;
  }
  static public function getServer($id) {
    $server = NULL;
    $result = \Drupal::database()->select('booking_server', 'q')
      ->fields('q', ['id', 'title', 'name', 'email', 'phone', 'password', 'status'])
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
        ];
      }

    return $server;
  }
  // is function
  static public function isYear($year, $serviceId)
  {
    $yearId = NULL;
    $result = \Drupal::database()->select('booking_year', 'q')
      ->fields('q', ['id'])
      ->condition('year', [$year])
      ->condition('serviceId', [$serviceId])
      ->execute();
    while ($row = $result->fetchAssoc()) {
      $yearId =  $row['id'];
    }

    return $yearId;
  }
  static public function isMonth($month, $yearId)
  {
    $monthId = NULL;
    $result = \Drupal::database()->select('booking_month', 'q')
      ->fields('q', ['id'])
      ->condition('yearId', [$yearId])
      ->condition('month', [$month])
      ->execute();
    while ($row = $result->fetchAssoc()) {
      $monthId =  $row['id'];
    }

    return $monthId;
  }
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



  // ajax
  static public function getTable($serviceId) {
    $data = ['years' => []];
    $nextWeek = date('W',REQUEST_TIME) + 1;
    $data = self::getServices($serviceId);
    return $data;
  }

  static public function getDataClient($client) {
    $service = self::getServices(1);
    $data =['years' => []];
    $y = 0;
    foreach ($service['years'] as $year) {
      array_push($data['years'], ['int' => $year['year'], 'months' =>[]]);
      $m = 0;
      foreach ($year['months'] as $month) {
        array_push($data['years'][$y]['months'], ['int' => $month['month'], 'days' => []]);
        $d = 0;
        foreach ($month['days'] as $day) {
          array_push($data['years'][$y]['months'][$m]['days'], ['int' => $day['day'], 'events' => []]);
          foreach ($day['slots'] as $slot) {
            $startTime = date("H:i",$slot['startTime']);
            $endTime = date("H:i",$slot['startTime'] + ($slot['period'] * 60));
            $bookText = '<button class="book-slot" id="'.$slot['id'].'" onclick="book('.$slot['id'].')"><input type="hidden" value=\'{"slotId":' .
             $slot['id'] .','.'"serviceId":'. $day['serviceId']
             .'}\'> Book</button> ' . $slot['server']['name'];
             $cancelText = '<button class="book-slot" id="'.$slot['id'].'" onclick="cancel('.$slot['id'].')"><input type="hidden" value=\'{"slotId":' .
             $slot['id'] .','.'"serviceId":'. $day['serviceId']
             .'}\'>Cancel</button> ' . $slot['server']['name'];
            if ($slot['status']) {
              array_push($data['years'][$y]['months'][$m]['days'][$d]['events'],[
                'startTime' => $startTime,
                'endTime' => $endTime,
                'mTime' => '>',
                'text' =>  $bookText,
              ]);
            }
            else{
              if ($client['id'] == self::getBookBySlotId($slot['id'])['client']['id'] ) {
                array_push($data['years'][$y]['months'][$m]['days'][$d]['events'],[
                  'startTime' => $startTime,
                  'endTime' => $endTime,
                  'mTime' => '>',
                  'text' =>  $cancelText,
                ]);
              }
            }
          }
          $d++;
        }
        $m++;
      }
      $y++;
    }
    return $data;
  }

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
      self::deleteBook($book);

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
    }
    else{
      $client = NULL;
    }
    return $client;
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

  static public function getDataServer($id) {
    $data = NULL;
    $data = self::getSlotsByServer($id);
    return $data;
  }

  static public function getDayDate($data) {
    $day = null;
    $result = \Drupal::database()->select('booking_day', 'q')
      ->fields('q', ['id', 'day', 'monthId', 'status', 'serviceId', 'timeStamp', "date"])
      ->condition('date', $data['date'])
      ->execute();
    while ($row = $result->fetchAssoc()) {
      $day = [
        'id' => $row['id'],
        'day' => $row['day'],
        'monthId' => $row['monthId'],
        'serviceId' => $row['serviceId'],
        'status' => $row['status'],
        'timeStamp' => $row['timeStamp'],
        'date' => $row['date'],
        'slots' => [],
      ];
    }
    $slots = self::getSlots($day['id']);
    foreach ($slots as $slot) {
      $slot['start'] =  date("H:i",$slot['startTime']);
      $slot['end'] = date("H:i",$slot['startTime'] + ($slot['period'] * 60));
      $bookButton = '<button class="book-slot" id="'.$slot['id'].'" onclick="book('.$slot['id'].')"><input type="hidden" value=\'{"slotId":' .
                   $slot['id'] .','.'"serviceId":'. $day['serviceId']
                   .'}\'> Book</button> ';
      $cancelButton = '<button class="book-slot" id="'.$slot['id'].'" onclick="cancel('.$slot['id'].')"><input type="hidden" value=\'{"slotId":' .
                   $slot['id'] .','.'"serviceId":'. $day['serviceId']
                   .'}\'>Cancel</button> ';
      if ($slot['status'] == 1) {
        $slot['button'] = $bookButton;
        array_push($day['slots'], $slot);
      }
      else{

        self::try($data['client']['id'], self::getBookBySlotId($slot['id'])['client']['id']);
        if ($data['client']['id'] == self::getBookBySlotId($slot['id'])['client']['id']) {
          $slot['button'] = $cancelButton;
          array_push($day['slots'], $slot);
        }
      }
    }

    return $day;
  }

  // test function
  static public function try($name,$value) {
    \Drupal::database()->insert('booking_test')
      ->fields(['value', 'name'])
      ->values([$value, $name])
      ->execute();
  }

} //end of class
