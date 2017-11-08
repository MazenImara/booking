(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.bookingBehavior = {
    attach: function (context, settings) {
      $('body', context).once('booking').each(function () {
//start
        function toDay() {
          d = new Date();
          day = d.getDate();
          month = d.getMonth()+1;
          year = d.getFullYear();
          if (day < 10)
            formatedDate = '0' + day + '-' + month + '-' + year;
          else
            formatedDate = day + '-' + month + '-' + year;
          return formatedDate;
        }

        $(function() {
          $('#popupDatepicker').datepick({onSelect: showDate});
          //$('#inlineDatepicker').datepick({onSelect: showDate});
        });
        var selectedDay = toDay();
        function showDate(date) {
          d = new Date(date);
          day = d.getDate();
          month = d.getMonth()+1;
          year = d.getFullYear();
          if (day < 10)
            formatedDate = '0' + day + '-' + month + '-' + year;
          else
            formatedDate = day + '-' + month + '-' + year;
          selectedDay = formatedDate;
          angular.element($(bookingCtrl)).scope().dayData(formatedDate, getBookingCookie());
        }
        function loadDay() {
          angular.element($(bookingCtrl)).scope().dayData(selectedDay, getBookingCookie());
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
          $scope.dayData(toDay(),getBookingCookie());
          $scope.book = function (id) {
            book(id);
          }

          $scope.cancel = function (id) {
            cancel(id);
          }
        });//end of ctrl


        var dayId = 'id' ;
        $(document).on("click",".day", function(){
          dayId = $(this).attr('id');
        });
        $('#client').click(function(event) {
          alert(dayId);
        });

        $('#reload').click(function(event) {
          loadData();
        });

        loadData();
        function loadData() {
          $('div').remove('.calendar');
          $('div').remove('.events');
          value = {cookieClient: getBookingCookie()};
          $.post("/getdataclient",value ,
            function(data, status){
              bookingCalendar(data);
              $('#'+dayId).trigger('click');
            }
          );
        }

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
                  loadData();
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
                  loadData();
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
          if (getBookingCookie()) {
            $('.booking-logedIn').hide();
            $('.booking-logedOut').show();
            $('#book-confirm').removeAttr('disabled');
            $('#booking-logOut').show();
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
          loadData();
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
                loadData();
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













      });//end of once
    }
  };
})(jQuery, Drupal, drupalSettings);

