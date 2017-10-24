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
  static public function addWeek($setting)
  {
    $week = date('W',REQUEST_TIME) + 1; $year = 2017;
    drupal_set_message($week);
    \Drupal::database()->insert('booking_week')
      ->fields(['week', 'serviceId', 'year'])
      ->values([$week, $setting['serviceId'], $year])
      ->execute();
    $weekId = self::getLastId('booking_week');
    for ($i=0; $i < 7; $i++) {
      if ($i == 0 || $i == 6) {
        $status = 0;
      }
      else{
        $status = 1;
      }
      $day = ['day' => $i, 'weekId' => $weekId, 'status' => $status];
      self::addDay($day, $setting['serviceId']);
    }


  }

  static public function addDay($serviceId) {
    $day = REQUEST_TIME;
    \Drupal::database()->insert('booking_day')
      ->fields(['day', 'weekId', 'status', 'serviceId'])
      ->values([$day, 22, 1, $serviceId])
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
    $query = \Drupal::database()->delete('booking_week', [])->execute();
    $query = \Drupal::database()->delete('booking_day', [])->execute();
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
          'weeks' => self::getWeeks($row['id']),
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
            'weeks' => self::getWeeks($row['id']),
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

  static public function getWeeks($serviceId)
  {
    $weeks = [];
    $result = \Drupal::database()->select('booking_week', 'q')
      ->fields('q', ['id', 'week', 'serviceId', 'year'])
      ->condition('serviceId', [$serviceId])
      ->execute();
      while ($row = $result->fetchAssoc()) {
        array_push($weeks, [
            'id' => $row['id'],
            'week' => $row['week'],
            'serviceId' => $row['serviceId'],
            'year' => $row['year'],
            'days' => self::getDays($row['id']),
          ]);
      }

    return $weeks;
  }
  static public function getDays($weekId)
  {
    $days = [];
    $result = \Drupal::database()->select('booking_day', 'q')
      ->fields('q', ['id', 'day', 'weekId', 'status'])
      ->condition('weekId', [$weekId])
      ->execute();
      while ($row = $result->fetchAssoc()) {
        array_push($days, [
            'id' => $row['id'],
            'day' => $row['day'],
            'weekId' => $row['weekId'],
            'status' => $row['status'],
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
            'serverId' => $row['serverId'],
          ]);
      }

    return $slots;
  }
  static public function getTable($serviceId) {
    $data = [];
    $nextWeek = date('W',REQUEST_TIME) + 1;
    $data = self::getServices($serviceId);
    return $data;
  }
} //end of class
