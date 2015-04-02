$(document).ready(function(){

  var getData = function (condoId){
      var id = condoId || 1;
      var condoObj;

      $.getJSON("http://localhost/ReservationCalendar/api/", function(json) {

        for(var i = 0, len = json.length; i < len; i++) {
          if(json[i].id == id) {
            condoObj = json[i];
            break;
          }
        }

        printCalendar(condoObj);
        printPrice();
        addUserHandlers();

        //Initial App loading
        $('.top-menu[data-condo |= "1"]').addClass('menu-active');
        ko.applyBindings(new GuestsModel("1"));

      });
  };

  var printCalendar = function (condoObj) {
       var today = getCurrentDate();
       var reservedDays = condoObj.booked || "";
       var reservedDaysArray;
       var condoTitle = condoObj.title || "";
       var condoDescr = condoObj.description || "";
       var numWeeks = 0;
       var numCells = 0;
       var dayOfMonth = 0;
       var firstOfMonth;
       var lastOfMonth;
       var currentDay;
       var calendar = $('#calendar');
       var year = $('<div>').addClass('year');
       var month;
       var monthTitle;
       var daysTitle;
       var weekDayTitle;
       var week;
       var day;
  
       //convert Array of strings (reservedDays) to Array of dates to make it easier to search later
       for(var i = 0, len = reservedDays.length; i < len; i++ ) {
          if(!reservedDaysArray || reservedDaysArray.length === 0) {
            reservedDaysArray = reservedDays[i].split(",");
          } else {
            reservedDaysArray = reservedDaysArray.concat(reservedDays[i].split(","));
          }
        }  

       for(i = 1; i < 13; i++) {

          //Remove all past monthes
          if(i < today.mm) {
            continue;
          }

          month = $('<div>').addClass('month').attr('data-month', i);

          dayOfMonth = 0;

          numWeeks = getNumberOfWeeks(today.yyyy, i);
          numCells = 1;  

          firstOfMonth = new Date(today.yyyy, i-1, 1);
          lastOfMonth = new Date(today.yyyy, i, 0); //0 current is a last of previous

          monthTitle = $("<div class='monthName'>" + getMonthName(i-1) + "</div>");

          daysTitle = $("<div class='daysTitle'></div>");
          for(var d =0; d < 7; d++) {
            weekDayTitle = "<div class='weekDayTitle'>" + getWeekDayName(d) + "</div>";
            daysTitle.append(weekDayTitle);
          }

          month.append(monthTitle);
          month.append(daysTitle);

           for(var j = 1; j < numWeeks + 1; j++) {
            week = $('<div>').addClass('week');

            for(d = 1; d < 8; d++) {
              day = $('<div>').addClass('cell');

              if(numCells == (firstOfMonth.getDay() + 1)) { //find the first day of month
                dayOfMonth = 1;
                day.text(dayOfMonth);
                day.addClass('day');

                day.attr('data-date', today.yyyy + "-" + i + "-" + dayOfMonth);
    
                currentDay = new Date(today.yyyy, i-1, dayOfMonth);
                if(isWeekEnd(currentDay)) {
                  day.addClass('weekend');
                }

                //knockout JS
                day.attr('data-bind', 'click: addDay'); 

                dayOfMonth++;

              } else if(dayOfMonth && dayOfMonth <= lastOfMonth.getDate()) {
                day.text(dayOfMonth);
                day.addClass('day');

                day.attr('data-date', today.yyyy + "-" + i + "-" + dayOfMonth);


                currentDay = new Date(today.yyyy, i-1, dayOfMonth);
                if(isWeekEnd(currentDay)) {
                  day.addClass('weekend');
                }

                //knockout JS
                day.attr('data-bind', 'click: addDay'); 

                dayOfMonth++;

              } else {
                day.text("");
              }

              numCells ++;

              week.append(day);
            }
            month.append(week);
          }
          year.append(month);
       }
      calendar.append(year);
  };

  var printPrice = function() {
    var weekDay = 0,
        weekEndDay =0,
        hotSeasonWeekDay = 0,
        hotSeasonWeekEndDay = 0,
        html = "";

    html += "<div id='rateTitle'>Rates</div>";
    html += "<div id='weekDay'>Weekday Rate: $ <span data-bind='text: weekDay'></span></div>";
    html += "<div id='weekEndDay'>Weekend Rate: $ <span data-bind='text: weekEndDay'></span></div>";
    html += "<div id='hotSeason'>Hot Season Rate: $ <span data-bind='text: hotSeason'></span></div>";    

    $('#price').html(html);
  };

  var isWeekEnd = function(currentDay) {
    if(currentDay.getDay() === 0 || currentDay.getDay() === 6) {
      return true;
    }
    return false;
  };
   
  var getCurrentDate = function() {
       var today = new Date();
       var dd = today.getDate();
       var mm = today.getMonth()+1; 
       var yyyy = today.getFullYear();

        if(dd<10) {
            dd='0'+dd;
        } 

        if(mm<10) {
            mm='0'+mm;
        } 

        today = {dd:dd, mm:mm, yyyy:yyyy};
        return today;
  };
   
  var getNumberOfWeeks = function(year, month) {
      
        var firstOfMonth = new Date(year, month-1, 1);
        var lastOfMonth = new Date(year, month, 0); 

        var used = firstOfMonth.getDay() + lastOfMonth.getDate();
        
        return Math.ceil( used / 7);
  };  

  var getMonthName = function(monthId) {
    var month = [];
    month[0] = "January";
    month[1] = "February";
    month[2] = "March";
    month[3] = "April";
    month[4] = "May";
    month[5] = "June";
    month[6] = "July";
    month[7] = "August";
    month[8] = "September";
    month[9] = "October";
    month[10] = "November";
    month[11] = "December";

    return month[monthId];
  };

  var getWeekDayName = function(weekDayId) {
    var weekDays = [];
    weekDays[0] = "Sun";
    weekDays[1] = "Mon";
    weekDays[2] = "Tue";
    weekDays[3] = "Wed";
    weekDays[4] = "Thu";
    weekDays[5] = "Fri";
    weekDays[6] = "Sat";

    return weekDays[weekDayId];
  };

  var addUserHandlers = function() {

    $('.day').on('click', function() {
      var day = $(this);
      day.toggleClass('active');
    });

    $('.top-menu').on('click', function() {
      $('.top-menu').removeClass('menu-active');
      $(this).addClass('menu-active');
    });
  };

   
//Launch App
getData();

});
