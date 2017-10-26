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
          loadData();
        });

        loadData();
        function loadData() {
          $('div').remove('.calendar');
          $('div').remove('.events');
          value = {cookieClient: getBookingCookie()};
          $.post("/get_data",value ,
            function(data, status){
              bookingCalendar(data);
            }
          );
        }

        book = function (id){
          Id = id;
          $('#book-popup-windo').css('display', 'block');
          $('#book-form').submit(function(event){
            event.preventDefault();
            event.stopImmediatePropagation();
            span = $('#'+Id);
            if (span.attr('id') == Id) {
              value = $.parseJSON(span.find('input').val());
              console.log(value);
              $('#book-popup-windo').css('display', 'none');
              $.post("/booking/book",value ,
                function(data, status){
                  loadData();
                }
              );
            }
          });
          $("#book-cancel").click(function(){
            $('#book-popup-windo').css('display', 'none');
          });
        }
        cancel = function (id){
          $('#cancel-popup-windo').css('display', 'block');
          $('#cancel-form').submit(function(event){
            event.preventDefault();
            event.stopImmediatePropagation();
            span = $('#'+id);
            span.css('color', 'red');
            value = $.parseJSON(span.find('input').val());
            console.log(value);
            $('#cancel-popup-windo').css('display', 'none');
            $.post("/booking/cancel",value ,
              function(data, status){
                loadData();
              }
            );
          });
          $("#cancel-cancel").click(function(){
            $('#cancel-popup-windo').css('display', 'none');
          });
        }




        $('#booking-signUp-email').change(function(event) {
          field = $(this);
          value = {isEmailExist: {email : field.val(), table: 'booking_client'}};
          $.post("/booking/isemailexist", value ,
            function(data, status){
              if (!jQuery.isEmptyObject(data)) {
                $('#booking-signUp-submit').attr('disabled','disabled');
                $('.booking-signUp-warning').show();
              }
              else{
                $('#booking-signUp-submit').removeAttr('disabled');
                $('.booking-signUp-warning').hide();
              }
            }
          );
        });
        $('#booking-logIn-email').change(function(event) {
          field = $(this);
          value = {isEmailExist: {email : field.val(), table: 'booking_client'}};
          $.post("/booking/isemailexist", value ,
            function(data, status){
              if (jQuery.isEmptyObject(data)) {
                $('#booking-logIn-submit').attr('disabled','disabled');
                $('.booking-logIn-warning').show();
              }
              else{
                $('#booking-logIn-submit').removeAttr('disabled');
                $('.booking-logIn-warning').hide();
              }
            }
          );
        });
        $('#booking-signUp').submit(function(event) {
          event.preventDefault();
          event.stopImmediatePropagation();
          $.post("/booking/signup", $(this).serialize() ,
            function(data, status){
              if (!jQuery.isEmptyObject(data)) {
                setBookingCookie(data);
                isLogIn();
              }
            }
          );
        });

        function setBookingCookie(client) {
          if (!$.cookie("booking_kookie")) {
            client = JSON.stringify(client);
            $.cookie("booking_kookie",client, { expires : 365 });
          }
        }
        function getBookingCookie() {
          if ($.cookie("booking_kookie")) {
            return $.parseJSON($.cookie("booking_kookie"));
          }
          else{
            return null;
          }
        }
        isLogIn();
        function isLogIn() {
          if (getBookingCookie()) {
            $('.booking-logedIn').hide();
            $('#book-confirm').removeAttr('disabled');
            $('#booking-logOut').show();
          }
          else{
            $('.booking-logedIn').show();
            $('#book-confirm').attr('disabled','disabled');
            $('#booking-logOut').hide();
          }
        }

        $('#booking-logOut').click(function(event) {
          $.removeCookie("booking_kookie");
          isLogIn();
        });

        $('#booking-logIn').submit(function(event) {
          event.preventDefault();
          event.stopImmediatePropagation();
          $.post("/booking/login", $(this).serialize() ,
            function(data, status){
              if (!jQuery.isEmptyObject(data)) {
                setBookingCookie(data);
                isLogIn();
                $('.booking-popup-text').text('Hello '+ data.name);
                alert('Hello '+ data.name)
              }
              else{
                $('p').append('<p style="color:red">* Wrong password <a href="">Resend password to this email</a></p>');
              }
            }
          );
        });

      });//end of once
    }
  };
})(jQuery, Drupal, drupalSettings);

