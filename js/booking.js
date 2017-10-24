(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.bookingBehavior = {
    attach: function (context, settings) {
      $('body', context).once('booking').each(function () {
//start
        var content = drupalSettings.booking.content;
        var myApp = angular.module('myModule', []).config(function($interpolateProvider){
            $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
        });
        myApp.controller('myController', function ($scope, $http, $log) {

        });// end of ctr


        $.post("/get_table",{} ,
          function(data, status){
            bookingCalendar(data);
          }
        );


      });//end of once
    }
  };
})(jQuery, Drupal, drupalSettings);

