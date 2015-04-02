<?php 
//recive data from JSON with reserved days
//draw calendar and put there reserved days
//user can choose what dates are needed and send reservation request
//admin see reservation request and approve it or decline it


?>

<!DOCTYPE html>
<html>
<head>
   <title>Condo Reservation App</title>
   <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/knockout/3.3.0/knockout-min.js"></script>

   <script src="js/scripts.js"></script>
  
   <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
   <link rel="stylesheet" href="css/style.css" />
</head>
<body>
<div id="wrap">
   <header>
      <h1>Condo Reservation Calendar</h1>

      <span class='top-menu' data-bind='click: changeCondo' data-condo='1'>Condo 1</span>
      <span class='top-menu' data-bind='click: changeCondo' data-condo='2'>Condo 2</span>
      <span class='top-menu' data-bind='click: changeCondo' data-condo='3'>Condo 3</span>

   </header>
   <div id="calendar"></div>

   <div id="sidebar">
   <div id="price"></div>
	


	<div id="form">

	<div id="reservedDays" data-bind="foreach: reservedDays">
      <span class='reservedDate' data-bind='click: $root.removeDate'>
      	<i class="fa fa-times fa-lg red removeDate"></i>
      	<span data-bind='text: date'></span>
        <span data-bind='text: name'></span>
       </span>
   </div>

	<div id="total" data-bind='visible: reservedDays().length > 0'>Total: $
	 <span data-bind='text: subtotal'></span>
   	</div>

      <form name="reservationForm" acton="" method="post" />

         <input type='text' name='userFirstName' data-bind='value: userFirstName' placeholder='Your First Name'/>
         <input type='text' name='userLastName' data-bind='value: userLastName' placeholder='Your Last Name'/>
         <input type='email' name='userEmail' data-bind='value: userEmail' placeholder='Your Email' />
         <input type='text' name='userPhone' data-bind='value: userPhone' placeholder='Your Phone Number' />
         
         
         <div id="addGuest">
         	<span class='addGuest' data-bind='click: addGuest'><i class="fa fa-plus-circle fa-lg green"></i> Add a guest</span>
         </div>

         <table class='guestsList' data-bind="visible: guests().length > 0">
            <tr>
               <th>First Name</th>
               <th>Last Name</th>
            </tr>
            <tbody data-bind="foreach: guests">
               <tr>
                  <td><input type='text' data-bind='value: guestFirstName' placeholder='Guest First Name' /></td>
                  <td><input type='text' data-bind='value: guestLastName' placeholder='Guest Last Name' /></td>
                  <td><div><a href='#' data-bind='click: $root.removeGuest'><i class="fa fa-user-times red"></i></a></div></td>
               </tr>
            </tbody>
         </table>


         <button class='btn' data-bind='click: save, enable: guests().length > 0'>Add Reservation</button>
      </form>
	</div>
   </div>
</div>

<script>
 
var GuestsModel = function(condoId) {
    var self = this;

   	self.condoId = condoId || "1";

    self.userFirstName = ko.observable("");
    self.userLastName = ko.observable("");
    self.userEmail = ko.observable("");
    self.userPhone = ko.observable("");
    self.lastSavedJson = ko.observable("");

    self.weekDay = ko.observable();
    self.weekEndDay = ko.observable();
    self.hotSeason = ko.observable();

    self.guests = ko.observableArray();
    self.reservedDays = ko.observableArray();

	self.reservedWeekDays = ko.observable(0);
    self.reservedWeekEndDays = ko.observable(0);

    self.subtotal = ko.pureComputed(function() {
    	var total = 0;
    	if(self.reservedDays().length > 0) {
    		//calculate total tost for weekdays
    		if(self.reservedWeekDays() > 0) {
    			total += self.reservedWeekDays() * self.weekDay();
    		}
    		//calculate total cost for weekend days 
    		if(self.reservedWeekEndDays() > 0) {
    			total += self.reservedWeekEndDays() * self.weekEndDay();
    		}
    	}
        return total;
    });

   self.addGuest = function() {
        self.guests.push({
            guestFirstName: "",
            guestLastName: ""
        });
   };
 
   self.removeGuest = function(guest) {
        self.guests.remove(guest);
   };

   self.removeDate = function(date) {
        self.reservedDays.remove(date);

        //Remove active from Calendar
        $(".day[data-date='" + date.date + "'").removeClass('active');

        if(self.isWeekEnd(date.date)) {
        	self.reservedWeekEndDays(self.reservedWeekEndDays() - 1);
        } else {
        	self.reservedWeekDays(self.reservedWeekDays() - 1);
        }
   };

   self.removeDay = function(date) {

       for(var i=0, len=self.reservedDays().length; i < len; i++){
         if (self.reservedDays()[i].date === date) {
            self.reservedDays.remove(self.reservedDays()[i]);   
            return true;
         }
      }
      return false;
   };

   self.addDay = function(data, event) {
      var valueExist = false;
      var date;

      date = $(event.target).attr('data-date');


      for(var i=0, len=self.reservedDays().length; i < len; i++){
         if (self.reservedDays()[i].date === date) {
            valueExist = true;
            break;
         }
      }

      if(!valueExist) {
      		if(self.isWeekEnd(date)) {
    			self.reservedWeekEndDays(self.reservedWeekEndDays() + 1);
      		} else {
      			self.reservedWeekDays(self.reservedWeekDays() + 1);
      		}

         self.reservedDays.push({date: date, name: self.getDayOfWeek(date)});
      } else { 
         	if(self.isWeekEnd(date)) {
    			self.reservedWeekEndDays(self.reservedWeekEndDays() - 1);
      		} else {
      			self.reservedWeekDays(self.reservedWeekDays() - 1);
      		}       
         self.removeDay(date);
      }
   };

   self.getDayOfWeek = function(date) {
   	  var tmpDate;
   	  var tmpMonth;
   	  var weekDayNames = ['San', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

   	  tmpDate = date.split("-");
   	  tmpMonth = tmpDate[1] - 1;
   	  var tmpDay = new Date(tmpDate[0], tmpMonth, tmpDate[2]);
	  
	  return weekDayNames[tmpDay.getDay()];
   };

   self.isWeekEnd = function(date) {
   	  var tmpDate;
	  var tmpMonth;

   	  tmpDate = date.split("-");
   	  tmpMonth = tmpDate[1] - 1;

   	  var weekDay = new Date(tmpDate[0], tmpMonth, tmpDate[2]);
   	  if(weekDay.getDay() === 0 || weekDay.getDay() === 6) {
   	  	return true;
   	  }
   	  return false;
   };

   self.changeCondo = function(data, event) {

   	var condoId = $(event.target).attr('data-condo');

   	//Remove all data related to reserved Days
    self.reservedDays.removeAll();
	self.reservedWeekDays(0);
    self.reservedWeekEndDays(0);

    //Remove all active days from calendar
    $(".day").removeClass('active');

   	self.getCondoRates(condoId);
   };

   self.getCondoRates = function(condoId) {

   		switch(condoId) {
   			case "1":
   			    self.weekDay(50);
    			self.weekEndDay(70);
    			self.hotSeason(95);
    			break;
   			case "2":
   			    self.weekDay(56);
    			self.weekEndDay(78);
    			self.hotSeason(115);
    			break;
    	   	case "3":
   			    self.weekDay(68);
    			self.weekEndDay(94);
    			self.hotSeason(135);
    			break;		
   		}
   };

   self.save = function() {

      var guestsData = {guests: ko.toJS(self.guests)};
      var userFirstName = ko.toJS(self.userFirstName);
      var userLastName = ko.toJS(self.userLastName);
      var userEmail = ko.toJS(self.userEmail);
      var userPhone = ko.toJS(self.userPhone);
      var userData = {name: userName, email: userEmail};

      self.lastSavedJson(JSON.stringify([guestsData, userData], null, 2));
   };

   //Initial Loading
   self.getCondoRates(self.condoId);
};
 
</script>
</body>
</html>