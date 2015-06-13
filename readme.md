#About
This project let you implement a calendar in your website. This calendar is very flexible: You can choose between different settings and you are also able to give this calendar your own style!

#Licence
This code is licensed under the terms of [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/)

#Simple integration
First, you have to include the required files:
```
require_once 'calendar/calendar.php';
```

Then, you have to create an instance of the calender:
```
$calendar = new Calendar();
```

After that, you have to load the configuration and other required data:
```
$calendar->loadInputFile("calendar/calendar_data_german.json");
```

In the end, you can display the calendar:
```
echo $calendar->output();
```

You can find an example configuration in calendar/calendar_data_german.json with in german translated weekdays.