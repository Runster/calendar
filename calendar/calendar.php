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

	public function __construct ()
	{
		$this->time = time();
		$this->firstline = true;
		$this->initWeekdays();
	}

	public function generateFullCalendar ()
	{
		$daysMonth = date("t", $this->time);
		$monthStartTimestamp = mktime(0, 0, 0, date("n", $this->time), 1, 
				date("Y", $this->time));
		$monthStartNumber = date("N", $monthStartTimestamp);
		$monthEndNumber = date("N", 
				mktime(0, 0, 0, date("n", $this->time), $daysMonth, 
						date("Y", $this->time)));
		$this->createTableHeader();
		for ($i = 1; $i <=
				 $daysMonth + ($monthStartNumber - 1) + (7 - $monthEndNumber); $i ++) {
			$newline = false;
			$currectDay = $i - $monthStartNumber;
			$currentDayTimestamp = strtotime($currectDay . " day", 
					$monthStartTimestamp);
			$dayOfMonthOutput = date("j", $currentDayTimestamp);
			if (date("N", $currentDayTimestamp) == 1) {
				$newline = true;
			}
			if (floor(($this->time - $currentDayTimestamp) / (3600 * 24)) == 0) {
				// Current day in the loop is the real current day
				$this->addTableCell($dayOfMonthOutput, $newline, "current_day");
			} elseif ($currectDay >= 0 && $currectDay < $daysMonth) {
				// Just another day in current month
				$this->addTableCell($dayOfMonthOutput, $newline);
			} else {
				// Day in another month
				if (! $this->showOnlyDaysOfThisMonth) {
					$this->addTableCell($dayOfMonthOutput, $newline, 
							"last_month_days");
				} else {
					$this->addTableCell("", $newline);
				}
			}
		}
		$this->createTableFooter();
	}

	private function initWeekdays ()
	{
		$timestamp = strtotime('next Monday');
		for ($i = 0; $i < 7; $i ++) {
			$this->weekdays[strftime('%a', $timestamp)] = strftime('%A', 
					$timestamp);
			$timestamp = strtotime('+1 day', $timestamp);
		}
	}

	private function createTableHeader ()
	{
		$this->output .= "<table" . $this->checkForStyleClass("table") . ">\n";
		$this->output .= "\t<caption>" . date("F Y", $this->time) .
				 "</caption>\n";
		$this->output .= "\t<thead>\n";
		$this->output .= "\t\t<tr>\n";
		foreach ($this->weekdays as $weekdaysShort => $weekdaysLong) {
			$this->output .= "\t\t\t<td>" . $weekdaysShort . "</td>\n";
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
		
		if ($class === null) {
			$this->output .= "\t\t\t<td>" . $input . "</td>\n";
		} else {
			$this->output .= "\t\t\t<td" . $this->checkForStyleClass($class) .
					 ">" . $input . "</td>\n";
		}
		
		$this->firstline = false;
	}

	public function setWeekdays ($weekdaysArray)
	{
		if (count($weekdaysArray) == 7) {
			$this->weekdays = $weekdaysArray;
		} else {
			$this->errorList["Wrong size array"] = "Your specified weekday-array hasn't the size of seven items.";
		}
	}

	public function output ()
	{
		return $this->output;
	}

	public function showOnlyDaysOfThisMonth ($boolean)
	{
		$this->showOnlyDaysOfThisMonth = $this->getBoolean($boolean, 
				$this->showOnlyDaysOfThisMonth);
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

	public function addStyleClasses ($styleClasses)
	{
		$this->styleClasses = $styleClasses;
	}
}