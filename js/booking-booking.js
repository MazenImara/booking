(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.bookingBehavior = {
    attach: function (context, settings) {
      $('body', context).once('booking').each(function () {
//start
        $(function() {
          $('#popupDatepicker').datepick({onSelect: showDate});
          //$('#inlineDatepicker').datepick({onSelect: showDate});
        });
        var selectedDay = new Date();
        function showDate(date) {
          selectedDay = date;
          client = getBookingCookie();
          angular.element($(bookingCtrl)).scope().dayData(formatDate(date), client);
          angular.element($(bookingCtrl)).scope().clientBook(client);
        }
        function loadDay() {
          client = getBookingCookie();
          angular.element($(bookingCtrl)).scope().dayData(formatDate(selectedDay), client);
          angular.element($(bookingCtrl)).scope().clientBook(client);
        }
        var myApp = angular.module('bookingClient', []).config(function($interpolateProvider){
          $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
        });
        myApp.controller('bookingCtrl', function ($scope, $http, $log, $filter) {




          $scope.dayData = function (date, client) {
            $http.post('/bookingAjax/getDay', {date: date, client: client}).then(function (response) {
              $scope.content = response.data;
            }, function (response) {
                      // this function handles error
            });
          }
          $scope.book = function (id) {
            book(id);
          }

          $scope.cancel = function (id) {
            cancel(id);
          }

          $scope.next = function () {
            d = new Date(selectedDay);
            d.setDate(d.getDate()+1);
            $scope.date = formatDate(d);
            selectedDay = d;
            $scope.dayData(formatDate(selectedDay),getBookingCookie());
          }

          $scope.previous = function () {
            d = new Date(selectedDay);
            d.setDate(d.getDate()-1);
            $scope.date = formatDate(d);
            selectedDay = d;
            $scope.dayData(formatDate(selectedDay),getBookingCookie());
          }

          $scope.toDay = function () {
            d = new Date();
            $scope.date = formatDate(d);
            selectedDay = d;
            $scope.dayData(formatDate(selectedDay),getBookingCookie());
          }

          $scope.clientBook = function (client) {
            $http.post('/bookingAjax/clientBook', {client: client}).then(function (response) {
              $scope.clientBooks = response.data;
            }, function (response) {
                      // this function handles error
            });
          }

          $scope.clientBook(getBookingCookie());
          $scope.dayData(formatDate(selectedDay),getBookingCookie());

        });//end of ctrl


        $('#reload').click(function(event) {
          loadDay();
        });
        book = function (id){
          $('.book-form').show();
          Id = id;
          $('#book-popup-windo').css('display', 'block');
          $('#book-form').submit(function(event){
            event.preventDefault();
            event.stopImmediatePropagation();
            span = $('#'+Id);
            if (span.attr('id') == Id) {
              value = $.parseJSON(span.find('input').val());
              value.client = getBookingCookie();
              console.log(value);
              $('#book-popup-windo').css('display', 'none');
              $.post("/booking/book",value ,
                function(data, status){

                  loadDay();
                }
              );
            }
          });
          $(".book-cancel").click(function(){
            $('#book-popup-windo').css('display', 'none');
          });
        }

        cancel = function (id){
          Id = id;
          $('#cancel-popup-windo').css('display', 'block');
          $('#cancel-form').submit(function(event){
            event.preventDefault();
            event.stopImmediatePropagation();
            span = $('#'+Id);
            if (span.attr('id') == Id) {
              span.css('color', 'red');
              value = $.parseJSON(span.find('input').val());
              value.client = getBookingCookie();
              console.log(value);
              $('#cancel-popup-windo').css('display', 'none');
              $.post("/booking/cancel",value ,
                function(data, status){

                  loadDay();
                }
              );
            }
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
          client = getBookingCookie();
          if (client) {
            $('.booking-logedIn').hide();
            $('.booking-logedOut').show();
            $('#book-confirm').removeAttr('disabled');
            $('#booking-logOut').show();
            $('.client-name').text(client.name);
          }
          else{
            $('.booking-logedIn').show();
            $('.booking-logedOut').hide();
            $('#book-confirm').attr('disabled','disabled');
            $('#booking-logOut').hide();
          }
        }

        $('.booking-logOut').click(function(event) {
          $.removeCookie("booking_kookie");
          isLogIn();

          loadDay();
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

                loadDay();
              }
              else{
                $('p').append('<p style="color:red">* Wrong password <a href="">Resend password to this email</a></p>');
              }
            }
          );
        });

        $('#logIn').click(function(event) {
          $('#book-popup-windo').css('display', 'block');
          $('.book-form').hide();
          $('.booking-windo-close').show();
        });
        $(".booking-close").click(function(){
          $('#book-popup-windo').css('display', 'none');
          $('.booking-windo-close').hide();
        });
      // start angularjs

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













      });//end of once
    }
  };
})(jQuery, Drupal, drupalSettings);

