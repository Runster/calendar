<?php

/**
 * Creates a calendar that can be easily included in the website
 * @author Runster
 * @license https://creativecommons.org/licenses/by-sa/4.0/ CC BY-SA 4.0
 */
class Calendar
{

	private $time;

	private $output = "";

	private $firstline = true;

	private $showOnlyDaysOfThisMonth = false;
	
	private $showCalendarWeek = false;

	private $weekdays = array();

	private $errorList = array();

	private $styleClasses = array();

	private $setStartWeekdayRequired = false;

	private $startWeekday = "Saturday";

	private $dayFormat = 'j';

	private $jsonFile = null;

	private $headline = null;

	private $eventList = array();
	
	private $shortcutCW = "CW";

	public function __construct ()
	{
		$this->time = time();
		$this->initWeekdays();
	}

	/**
	 * Generates the calendar.
	 * Calculates the number of rows and the number of days
	 * before and after the current month.
	 */
	private function generateFullCalendar ()
	{
		if (! $this->setStartWeekdayRequired) {
			$this->createTableHeader();
			
			$firstDayInMonth = mktime(0, 0, 0, date("m", $this->time), 1, date("Y", $this->time));
			$totalDaysInMonth = date("t", $firstDayInMonth);
			$daysFromLastMonth = date("N", $firstDayInMonth) - date("N", strtotime("next " . $this->startWeekday));
			if ($daysFromLastMonth < 0) {
				$daysFromLastMonth = $daysFromLastMonth + 7;
			}
			for ($i = 0; $i < ceil(($totalDaysInMonth + $daysFromLastMonth) / 7) * 7; $i ++) {
				$newline = false;
				if (($i % 7) == 0) {
					$newline = true;
				}
				if ($i < $daysFromLastMonth) {
					$lastMonth = mktime(0, 0, 0, date("m", $this->time) - 1, 1, date("Y", $this->time));
					$lastMonthDays = date("t", $lastMonth);
					$this->addTableCell(mktime(0, 0, 0, date("m", $this->time) - 1, $lastMonthDays - $daysFromLastMonth + $i + 1, date("Y", $this->time)), $newline, "last_month_days");
				} elseif ($i - $daysFromLastMonth >= $totalDaysInMonth) {
					$this->addTableCell(mktime(0, 0, 0, date("m", $this->time) + 1, ($i - $totalDaysInMonth - $daysFromLastMonth + 1), date("Y", $this->time)), $newline, "last_month_days");
				} else {
					$currentDayTimeStamp = mktime(0, 0, 0, date("m", $this->time), ($i - $daysFromLastMonth + 1), date("Y", $this->time));
					if (date("dmY", $currentDayTimeStamp) == date("dmY", time())) {
						$this->addTableCell($currentDayTimeStamp, $newline, "current_day");
					} else {
						$this->addTableCell($currentDayTimeStamp, $newline);
					}
				}
			}
			
			$this->createTableFooter();
		} else {
			$this->errorList["Missing field"] = "You must specify a weekday to start.";
		}
	}

	/**
	 * Generates a list of all weekdays
	 */
	private function initWeekdays ()
	{
		if (empty($this->weekdays) === true) {
			$timestamp = strtotime('next ' . $this->startWeekday);
			for ($i = 0; $i < 7; $i ++) {
				$this->weekdays[strftime('%A', $timestamp)] = array(
						"short" => strftime('%a', $timestamp),
						"long" => strftime('%A', $timestamp)
				);
				$timestamp = strtotime('+1 day', $timestamp);
			}
		} else {
			$timestamp = strtotime('next ' . $this->startWeekday);
			$temp = array();
			for ($i = 0; $i < 7; $i ++) {
				$temp[strftime('%A', $timestamp)] = array(
						"short" => $this->weekdays[strftime('%A', $timestamp)]["short"],
						"long" => $this->weekdays[strftime('%A', $timestamp)]["long"]
				);
				$timestamp = strtotime('+1 day', $timestamp);
			}
			$this->weekdays = $temp;
		}
	}

	/**
	 * Creates the table header
	 */
	private function createTableHeader ()
	{
		$this->output .= "<table" . $this->checkForStyleClass("table") . ">\n";
		if ($this->headline != null) {
			$captionOutput = str_replace(array(
					"[month]",
					"[year]"
			), array(
					date("F", $this->time),
					date("Y", $this->time)
			), $this->headline);
			$this->output .= "\t<caption>" . $captionOutput . "</caption>\n";
		}
		$this->output .= "\t<thead>\n";
		$this->output .= "\t\t<tr>\n";
		if($this->showCalendarWeek)
		{
			$this->output .= "\t\t\t<td>". $this->shortcutCW ."</td>\n";
		}
		foreach ($this->weekdays as $weekdayData) {
			$this->output .= "\t\t\t<td>" . $weekdayData["short"] . "</td>\n";
		}
		$this->output .= "\t\t</tr>\n";
		$this->output .= "\t</thead>\n";
		$this->output .= "\t<tbody>\n";
	}

	/**
	 * Creates the table footer, that closes any opened HTML tags
	 */
	private function createTableFooter ()
	{
		$this->output .= "\t\t</tr>\n";
		$this->output .= "\t</tbody>\n";
		$this->output .= "</table>";
	}

	/**
	 * Creates one cell in the table.
	 * It also checks, which style is combined with this day
	 *
	 * @param int $input
	 *        	The day as a timestamp
	 * @param boolean $newline
	 *        	Is this day shown in a new row
	 * @param string $class
	 *        	Which class is combined to this cell
	 */
	private function addTableCell ($input, $newline, $class = null)
	{
		if ($newline && ! $this->firstline) {
			$this->output .= "\t\t</tr>\n";
		}
		
		if ($newline) {
			$this->output .= "\t\t<tr>\n";
			if($this->showCalendarWeek)
			{
				if(date("W", $input) == date("W", $input + 604799))
				{
					$this->output .= "\t\t\t<td" . $this->checkForStyleClass("calendarweek_column") . ">". date("W", $input) ."</td>\n";
				}
				else
				{
					$this->output .= "\t\t\t<td" . $this->checkForStyleClass("calendarweek_column") . ">". date("W", $input) ." / ". date("W", $input + 604799) ."</td>\n";
				}
			}
		}
		
		$event = "";
		
		foreach ($this->eventList as $eventName => $eventData) {
			if(!isset($eventData["days"]) || $eventData["days"] == "0")
			{
				$eventData["days"] = "1";
			}
			$tempEventStartDate = $eventData["date"];
			for($i = 0; $i < $eventData["days"]; $i++)
			{
				$eventData["date"] = $tempEventStartDate;
				if (strlen($eventData["date"]) < 3 && is_numeric($eventData["date"])) {
					$eventData["date"] = $eventData["date"] + $i;
					if ($eventData["date"] == date("d", $input) || $eventData["date"] == date("j", $input)) {
						$event .= "<div" . $this->checkForStyleClass("event") . ">". $eventName . "</div>";
					}
				}
				else 
				{
					$eventTimeTimestamp = strtotime($eventData["date"]) + ($i * 86400);
					if(date("d.m.Y", $eventTimeTimestamp) == date("d.m.Y", $input))
					{
						$event .= "<div" . $this->checkForStyleClass("event") . ">". $eventName . "</div>";
					}
				}
			}
		}
		if (date("mY", time()) != date("mY", $input) && $this->showOnlyDaysOfThisMonth) {
			$input = "";
		} else {
			$input = date($this->dayFormat, $input);
		}
		
		if (array_key_exists("day_of_month", $this->styleClasses)) {
			$input = "<div" . $this->checkForStyleClass("day_of_month") . ">" . $input . "</div>";
		}
		
		if ($class === null) {
			$this->output .= "\t\t\t<td>" . $input . " " . $event . "</td>\n";
		} else {
			$this->output .= "\t\t\t<td" . $this->checkForStyleClass($class) . ">" . $input . " " . $event . "</td>\n";
		}
		
		$this->firstline = false;
	}

	/**
	 * Calls the generateFullCalendar() method to generate the calendar
	 *
	 * @return string The calendar as HTML
	 */
	public function output ()
	{
		$this->generateFullCalendar();
		return $this->output;
	}

	/**
	 * Checks, if the passed parameter is a valid boolean
	 *
	 * @param boolean|string $booleanToCheck
	 *        	Boolean to check
	 * @param boolean $default
	 *        	Set the default output
	 * @return boolean If the passed parameter is a valid boolean, it returns
	 *         the boolean, otherwise it will return the default value
	 */
	private function getBoolean ($booleanToCheck, $default)
	{
		if (is_bool($booleanToCheck)) {
			return $booleanToCheck;
		} elseif (strcasecmp($booleanToCheck, "true") == 0) {
			return true;
		} elseif (strcasecmp($booleanToCheck, "false") == 0) {
			return false;
		} else {
			$this->errorList["Parse error"] = "At least one of your parameters isn't a valid boolean.";
		}
		
		return $default;
	}

	/**
	 * Creates a list of all occured errors
	 *
	 * @return string The list of all occured errors as HTML code
	 */
	public function getErrors ()
	{
		$error_output = "";
		foreach ($this->errorList as $error) {
			$error_output .= "<p>" . $error . "</p>";
		}
		return $error_output;
	}

	/**
	 * Checks if there is a style tag for this tag
	 *
	 * @param string $tagName
	 *        	The internal name of the style
	 * @return string The user specified class name for this tag
	 */
	private function checkForStyleClass ($tagName)
	{
		if (array_key_exists($tagName, $this->styleClasses)) {
			return " class=\"" . $this->styleClasses[$tagName] . "\"";
		}
	}

	/**
	 * Loads the json file and calls all configuration methods
	 *
	 * @param string $filepath
	 *        	Path to the json file
	 */
	public function loadConfigFile ($filepath)
	{
		if(file_exists($filepath))
		{
			$this->jsonFile = json_decode(file_get_contents($filepath), true);
			
			$this->getShowDaysWithLeadingZeros();
			$this->getShowOnlyDaysOfThisMonth();
			$this->getShowCalendarWeek();
			$this->getStyleClasses();
			$this->getWeekdays();
			$this->getStartWeekday();
			$this->getHeadline();
			$this->getEvents();
			$this->getTranslations();
		}
		else
		{ 
			$this->errorList["File not found"] = "The configuration file was not found. Please check the file path.";
		}
	}

	/**
	 * Reads from the config file the headline for the calendar
	 */
	private function getHeadline ()
	{
		if (isset($this->jsonFile["Headline"])) {
			$this->headline = $this->jsonFile["Headline"];
		}
	}
	
	/**
	 * Reads from the config file the translation of used words
	 */
	private function getTranslations ()
	{
		if (isset($this->jsonFile["translations"]["CW"])) {
			$this->shortcutCW = $this->jsonFile["translations"]["CW"];
		}
	}

	/**
	 * Reads from the config file the list of all events
	 */
	private function getEvents ()
	{
		if (isset($this->jsonFile["Events"])) {
			$this->eventList = $this->jsonFile["Events"];
		}
	}
	
	/**
	 * Reads from the config file the option to show the calendar week or not
	 */
	private function getShowCalendarWeek()
	{
		if (isset($this->jsonFile["ShowCalendarWeek"])) {
			$this->showCalendarWeek = $this->getBoolean($this->jsonFile["ShowCalendarWeek"], $this->showCalendarWeek);
		}
	}

	/**
	 * Reads from the config file the value, if the leading zeros are shown or
	 * not
	 */
	private function getShowDaysWithLeadingZeros ()
	{
		if (isset($this->jsonFile["ShowDaysWithLeadingZeros"])) {
			$showZeros = $this->getBoolean($this->jsonFile["ShowDaysWithLeadingZeros"], "false");
			if ($showZeros) {
				$this->dayFormat = 'd';
			} else {
				$this->dayFormat = 'j';
			}
		}
	}

	/**
	 * Reads from the config file the style classes combined to the internal
	 * names
	 */
	private function getStyleClasses ()
	{
		if (isset($this->jsonFile["StyleClasses"])) {
			if (is_array($this->jsonFile["StyleClasses"])) {
				$this->styleClasses = $this->jsonFile["StyleClasses"];
			} else {
				$this->errorList["Array error"] = "At least one of your parameters isn't an array.";
			}
		}
	}

	/**
	 * Reads from the config file the weekday the calendar starts with
	 */
	private function getStartWeekday ()
	{
		if (isset($this->jsonFile["StartWeekday"])) {
			$startWeekday = $this->jsonFile["StartWeekday"];
			if (strlen($startWeekday) > 3) {
				if (array_key_exists($startWeekday, $this->weekdays)) {
					$this->startWeekday = $startWeekday;
					$this->initWeekdays();
					$this->setStartWeekdayRequired = false;
				} else {
					$found = false;
					foreach ($this->weekdays as $weekday) {
						if ($weekday["long"] == $startWeekday) {
							$this->startWeekday = key($weekday);
							$found = true;
							$this->initWeekdays();
							$this->setStartWeekdayRequired = false;
						}
					}
					if (! $found) {
						$this->errorList["Startweekday not found"] = "The weekday you specified is not in the weekdays-array.";
					}
				}
			} else {
				$this->errorList["Startweekday not found"] = "The weekday you specified mustn't be a shortcut.";
			}
		}
	}

	/**
	 * Reads from the config file the list with names of all weekdays
	 */
	private function getWeekdays ()
	{
		if (isset($this->jsonFile["Weekdays"])) {
			if (count($this->jsonFile["Weekdays"]) == 7) {
				$this->weekdays = $this->jsonFile["Weekdays"];
				$this->setStartWeekdayRequired = true;
			} else {
				$this->errorList["Wrong size array"] = "Your specified weekday-array hasn't the size of seven items.";
			}
		}
	}

	/**
	 * Reads from the config file the value, if only the days from the current
	 * month are shown or not
	 */
	private function getShowOnlyDaysOfThisMonth ()
	{
		if (isset($this->jsonFile["ShowOnlyDaysOfThisMonth"])) {
			$this->showOnlyDaysOfThisMonth = $this->getBoolean($this->jsonFile["ShowOnlyDaysOfThisMonth"], $this->showOnlyDaysOfThisMonth);
		}
	}
}
