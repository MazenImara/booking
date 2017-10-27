(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.bookingBehavior = {
    attach: function (context, settings) {
      $('body', context).once('server').each(function () {
//start

        var content = drupalSettings.booking.content;
        var myApp = angular.module('bookingServer', []).config(function($interpolateProvider){
            $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
        });
        myApp.controller('bookingServerCtrl', function ($scope, $http, $log) {



          $http.post('/get_data', {name: 'mazen'}).then(function (response) {
            $scope.content = response.data;
          }, function (response) {
          // this function handles error
          });

        });// end of ctr



      });//end of once
    }
  };
})(jQuery, Drupal, drupalSettings);

