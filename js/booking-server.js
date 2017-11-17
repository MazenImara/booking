(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.bookingBehavior = {
    attach: function (context, settings) {
      $('body', context).once('server').each(function () {
//start
      var selectedDay = new Date();
      $(function() {
          $('#popupDatepicker').datepick({onSelect: showDate});
          //$('#inlineDatepicker').datepick({onSelect: showDate});
        });
        function showDate(date) {
          selectedDay = date;
          angular.element($('#bookingServerCtrl')).scope().dayData(formatDate(date));
        }







        var myApp = angular.module('bookingServer', []).config(function($interpolateProvider){
            $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
        });
        myApp.controller('bookingServerCtrl', function ($scope, $http, $log, $filter) {
          serverId =  drupalSettings.booking.content.serverId;
          $scope.dayData = function (date) {
            $http.post('/bookingAjax/getServerDay', {date: date, serverId: serverId}).then(function (response) {
              $scope.content = response.data;
            }, function (response) {
                      // this function handles error
            });
          }

          $scope.dayData(formatDate(selectedDay));
          $scope.select = formatDate(selectedDay);


          $scope.next = function () {
            d = new Date(selectedDay);
            d.setDate(d.getDate()+1);
            selectedDay = d;
            $scope.select = formatDate(selectedDay);
            $scope.dayData(formatDate(selectedDay));
          }

          $scope.previous = function () {
            d = new Date(selectedDay);
            d.setDate(d.getDate()-1);
            selectedDay = d;
            $scope.select = formatDate(selectedDay);
            $scope.dayData(formatDate(selectedDay));
          }

          $scope.toDay = function () {
            d = new Date();
            selectedDay = d;
            $scope.select = formatDate(selectedDay);
            $scope.dayData(formatDate(selectedDay));
          }

          $scope.clear = function () {
            $scope.date.status = '';
          }


          $scope.style= function (status) {
            if (status == '0')
              return 'booked';
            else
              return '';
          }

          $scope.editSlot = function (slotId,start, end) {
            $scope.startTime = start;
            $scope.endTime = end;
            $scope.slotId = slotId;
            $('#server-edit-slot').show();
          }

          $scope.deleteSlot = function (slotId) {
            $http.post('/booking/deleteSlot', {slotId: slotId}).then(function (response) {
              $('#server-edit-slot').hide();
              $scope.dayData(formatDate(selectedDay));
              $('.delete-btn').hide();
            }, function (response) {
                      // this function handles error
            });
          }

          $scope.cancelBook = function (slotId) {
            $http.post('/bookingAjax/adminCancelBook', {slotId: slotId}).then(function (response) {
              $('#server-edit-slot').hide();
              $scope.dayData(formatDate(selectedDay));
              $('.toHide').hide();
            }, function (response) {
                      // this function handles error
            });
          }

          $scope.saveSlot = function (slotId, startTime, endTime) {
            $http.post('/bookingAjax/editSlotTime', {slotId: slotId, startTime: startTime, endTime: endTime}).then(function (response) {
              $('#server-edit-slot').hide();
              $scope.dayData(formatDate(selectedDay));
              $('.toHide').hide();
            }, function (response) {
                      // this function handles error
            });
          }






        });// end of ctrl

        function formatDate(strDate) {
          d = new Date(strDate);
          day = d.getDate();
          month = d.getMonth()+1;
          year = d.getFullYear();
          if (day < 10)
            date = '0' + day + '-';
          else
            date = day + '-';
          if (month < 10)
            date = date + '0' + month + '-';
          else
            date = date + month + '-';
          date = date + year;

          return date;
        }

        $('.edit-cancel').click(function(event) {
          $('#server-edit-slot').hide();
          $('.toHide').hide();
        });

        $('.delete-text').click(function(event) {
          $('.delete-btn').show();
        });

        $('.cancel-text').click(function(event) {
          $('.cancel-book-btn').show();
        });










      });//end of once
    }
  };
})(jQuery, Drupal, drupalSettings);

