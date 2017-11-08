(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.bookingBehavior = {
    attach: function (context, settings) {
      $('body', context).once('server').each(function () {
//start

        console.log(content);
        var myApp = angular.module('bookingServer', []).config(function($interpolateProvider){
            $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
        });
        myApp.controller('bookingServerCtrl', function ($scope, $http, $log, $filter) {

          $scope.content = drupalSettings.booking.content;
          date = new Date();
          date2 = new Date();
          $scope.today = date.getDate();
          $scope.thisMonth = date.getMonth()+1;
          $scope.thisYear = date.getFullYear();


          $scope.clear = function () {
            $scope.date.status = '';
            $scope.date.year = '';
            $scope.date.month = '';
            $scope.date.day = '';
          }
          $scope.todayFun = function () {
            $scope.date.day = date.getDate();
            $scope.date.month = date.getMonth()+1;
            $scope.date.year = date.getFullYear();
          }
          $scope.previous = function () {
            date2.setFullYear($scope.date.year, $scope.date.month-1, $scope.date.day - 1);
            $scope.date.day = date2.getDate();
            $scope.date.month = date2.getMonth()+1;
            $scope.date.year = date2.getFullYear();
          }
          $scope.next = function () {
            date2.setFullYear($scope.date.year, $scope.date.month-1, $scope.date.day + 1);
            $scope.date.day = date2.getDate();
            $scope.date.month = date2.getMonth()+1;
            $scope.date.year = date2.getFullYear();
          }








        });// end of ctr



      });//end of once
    }
  };
})(jQuery, Drupal, drupalSettings);

