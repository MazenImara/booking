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

        $('#reload').click(function(event) {
          loadTable();
        });

        loadTable();
        function loadTable() {
          $('div').remove('.calendar');
          $('div').remove('.events');
          $.post("/get_table",{} ,
            function(data, status){
              bookingCalendar(data);
            }
          );
        }




        book = function (id){
          span = $('#'+id);
          $('#book-popup-windo').css('display', 'block');
          span.css('color', 'red');
          value = $.parseJSON(span.find('input').val());
          console.log(value);

          $('#book-form').submit(function(e){
            e.preventDefault();
            e.stopPropagation();
            $('#book-popup-windo').css('display', 'none');
            $.post("/booking/book",value ,
              function(data, status){
                loadTable();
              }
            );
          });
          $("#book-cancel").click(function(){
            $('#book-popup-windo').css('display', 'none');
          });
        }
        cancel = function (id){
          span = $('#'+id);
          $('#cancel-popup-windo').css('display', 'block');
          span.css('color', 'red');
          value = $.parseJSON(span.find('input').val());
          console.log(value);

          $('#cancel-form').submit(function(e){
            e.preventDefault();
            e.stopPropagation();
            $('#cancel-popup-windo').css('display', 'none');
            $.post("/booking/cancel",value ,
              function(data, status){
                loadTable();
              }
            );
          });
          $("#cancel-cancel").click(function(){
            $('#cancel-popup-windo').css('display', 'none');
          });
        }




      });//end of once
    }
  };
})(jQuery, Drupal, drupalSettings);

