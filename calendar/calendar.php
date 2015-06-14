<?php

class Calendar
{

	private $time;

	private $output = "";

	private $firstline;

	private $showOnlyDaysOfThisMonth = false;

	private $weekdays = array();

	private $errorList = array();

	private $styleClasses = array();

	private $setStartWeekdayRequired = false;

	private $startWeekday = "Saturday";

	private $dayFormat = 'j';

	private $jsonFile = null;
	
	private $headline = null;

	public function __construct ()
	{
		$this->time = time();
		$this->firstline = true;
		$this->initWeekdays();
	}

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

	private function createTableHeader ()
	{
		$this->output .= "<table" . $this->checkForStyleClass("table") . ">\n";
		if($this->headline != null)
		{
			$captionOutput = str_replace(array("[month]", "[year]"), array(date("F", $this->time), date("Y", $this->time)), $this->headline);
			$this->output .= "\t<caption>" . $captionOutput . "</caption>\n";
		}
		$this->output .= "\t<thead>\n";
		$this->output .= "\t\t<tr>\n";
		foreach ($this->weekdays as $weekdayData) {
			$this->output .= "\t\t\t<td>" . $weekdayData["short"] . "</td>\n";
		}
		$this->output .= "\t\t</tr>\n";
		$this->output .= "\t</thead>\n";
		$this->output .= "\t<tbody>\n";
	}

	private function createTableFooter ()
	{
		$this->output .= "\t\t</tr>\n";
		$this->output .= "\t</tbody>\n";
		$this->output .= "</table>";
	}

	private function addTableCell ($input, $newline, $class = null)
	{
		if ($newline && ! $this->firstline) {
			$this->output .= "\t\t</tr>\n";
		}
		
		if ($newline) {
			$this->output .= "\t\t<tr>\n";
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
			$this->output .= "\t\t\t<td>" . $input . "</td>\n";
		} else {
			$this->output .= "\t\t\t<td" . $this->checkForStyleClass($class) . ">" . $input . "</td>\n";
		}
		
		$this->firstline = false;
	}

	public function output ()
	{
		$this->generateFullCalendar();
		return $this->output;
	}

	private function getBoolean ($booleanToCheck, $default)
	{
		if (is_bool($booleanToCheck)) {
			return $booleanToCheck;
		} elseif (strcasecmp($booleanToCheck, "true") == 0) {
			return true;
		} elseif (strcasecmp($booleanToCheck, "false") == 0) {
			return false;
		} else {
			$this->errorList["Parse error"] = "One of your parameters isn't a valid boolean.";
		}
		
		return $default;
	}

	public function getErrors ()
	{
		$error_output = "";
		foreach ($this->errorList as $error) {
			$error_output .= "<p>" . $error . "</p>";
		}
		return $error_output;
	}

	private function checkForStyleClass ($tagName)
	{
		if (array_key_exists($tagName, $this->styleClasses)) {
			return " class=\"" . $this->styleClasses[$tagName] . "\"";
		}
	}

	public function loadInputFile ($filepath)
	{
		$this->jsonFile = json_decode(file_get_contents($filepath), true);
		
		$this->getShowDaysWithLeadingZeros();
		$this->getShowOnlyDaysOfThisMonth();
		$this->getStyleClasses();
		$this->getWeekdays();
		$this->getStartWeekday();
		$this->getHeadline();
	}
	
	private function getHeadline()
	{
		if (isset($this->jsonFile["Headline"])) {
			$this->headline = $this->jsonFile["Headline"];
		}
	}

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

	private function getStyleClasses ()
	{
		if (isset($this->jsonFile["StyleClasses"])) {
			if (is_array($this->jsonFile["StyleClasses"])) {
				$this->styleClasses = $this->jsonFile["StyleClasses"];
			} else {
				$this->errorList["Array error"] = "One of your parameters isn't an array.";
			}
		}
	}

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

	private function getShowOnlyDaysOfThisMonth ()
	{
		if (isset($this->jsonFile["ShowOnlyDaysOfThisMonth"])) {
			$this->showOnlyDaysOfThisMonth = $this->getBoolean($this->jsonFile["ShowOnlyDaysOfThisMonth"], $this->showOnlyDaysOfThisMonth);
		}
	}
}
