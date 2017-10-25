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
  static public function add($settings) {
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
      ->fields(['day', 'monthId', 'status', 'serviceId', 'timeStamp'])
      ->values([$day, $monthId, 1, $settings['serviceId'], $newDayTime])
      ->execute();
      $dayId = self::getLastId('booking_day');
      $workTime = ['start' => 8, 'end' => 17]; $serverCount = 1;
      $period = 60; $timeOff = [ 'start' => 12, 'end' => 1];
      $slotQuantity = ($workTime['end'] - $workTime['start']);
      $dayDate = '2017-' . 'W' . (date('W',REQUEST_TIME) + 1) . '-' . $day['day'];
      $startTime = strtotime($dayDate)+(8*60*60);
      foreach (self::getServiceServers($serviceId) as $server) {
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
  static public function deleteSlots($value='')
  {
    $query = \Drupal::database()->delete('booking_slot', [])->execute();
    $query = \Drupal::database()->delete('booking_day', [])->execute();
    $query = \Drupal::database()->delete('booking_month', [])->execute();
    $query = \Drupal::database()->delete('booking_year', [])->execute();
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
  static public function getServices($id) {
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
  // ajax
  static public function getTable($serviceId) {
    $data = ['years' => []];
    $nextWeek = date('W',REQUEST_TIME) + 1;
    $data = self::getServices($serviceId);
    return $data;
  }

  static public function getData() {
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
            $text = '<button id="5">Book</button> ' . $slot['server']['name'];
            array_push($data['years'][$y]['months'][$m]['days'][$d]['events'],[
              'startTime' => $startTime,
              'endTime' => $endTime,
              'mTime' => '>',
              'text' =>  $text,
            ]);
          }
          $d++;
        }
        $m++;
      }
      $y++;
    }
    return $data;
  }


} //end of class
