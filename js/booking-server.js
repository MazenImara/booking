(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.bookingBehavior = {
    attach: function (context, settings) {
      $('body', context).once('server').each(function () {
//start

        var selectedDay = new Date();
        $(function() {
          $('#popupDatepicker').datepick({onSelect: showDate});
          $('#startDateDatepicker').datepick();
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
            $http.post('/bookingAjax/deleteSlot', {slotId: slotId}).then(function (response) {
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
            if (isTime(startTime) && isTime(endTime)){
              $http.post('/bookingAjax/editSlotTime', {slotId: slotId, startTime: startTime, endTime: endTime}).then(function (response) {
                $('#server-edit-slot').hide();
                $scope.dayData(formatDate(selectedDay));
                $('.toHide').hide();
              }, function (response) {
                        // this function handles error
              });
            }
            else{
              //alert('invalid time');
            }
          }

          $scope.addSlot = function (startTime, endTime) {
            if (isTime(startTime) && isTime(endTime)) {
              $http.post('/bookingAjax/addSlot', {
                dayId: $scope.content.id,
                serverId: serverId,
                startTime: startTime,
                endTime: endTime,
                serviceId : drupalSettings.booking.content.serviceId,
                dayDate: formatDate(selectedDay),
              }).then(function (response) {

                $scope.dayData(formatDate(selectedDay));

              }, function (response) {
                        // this function handles error
              });
            }
            else{
              alert('invalid time');
            }
          }






        });// end of ctrl

        function isTime(input) {
          boolean = false;
          if (input != null) {
            if (input.indexOf(":") >= 0){
              inputArr = input.split(':');
              if (!inputArr[0].match(/[^0-9]/g) && !inputArr[1].match(/[^0-9]/g)) {
                if(inputArr[0] < 24 && inputArr[0] != ''){
                  if (inputArr[1] < 60 && inputArr[1] != '') {
                    boolean = true;
                  }
                  else{
                    alert('Minutes should be between 0 - 59')
                  }
                }
                else{
                  alert('Hours should be between 0 - 23')
                }
              }
              else{
                alert('only numbers')
              }

            }
            else{
              alert('Input should be ex 17:50');
            }
          }
          return boolean
        }
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

