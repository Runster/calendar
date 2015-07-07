#About
This calendar leaves you lots of possibilities:
You can choose between different settings:
* Start weekday
* Show the last / first days of the month before / after?
* Show the days from 1 to 9 with a leading zero?
* and much more

You can give the calendar completely your own style!

Screenshots of the calendar
* [in English](http://i.imgur.com/E8VCMSU.png)
* [in German](http://i.imgur.com/2cYTXr2.png)

#Licence
This code is licensed under the terms of [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/)

#Simple integration
First, you have to include the required files:
```
require_once 'calendar/calendar.php';
```

Then, you have to create an instance of the calendar:
```
$calendar = new Calendar();
```

After that, you have to load the configuration and other required data:
```
$calendar->loadConfigFile("calendar/calendar_data_german.json");
```

In the end, you can display the calendar:
```
echo $calendar->output();
```

You also have the chance to display all errors:

```
echo $calendar->getErrors();
```

You can find an example configuration in *calendar/calendar_data_german.json* with in german translated weekdays.