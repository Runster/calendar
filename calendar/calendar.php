<?php
class Calendar
{
    private $time;

    private $output = "";

    private $firstline;

    private $showOnlyDaysOfThisMonth = false;

    private $weekdays = array(
            "Mon" => "Monday",
            "Tue" => "Tuesday",
            "Wed" => "Wednesday",
            "Thu" => "Thursday",
            "Fri" => "Friday",
            "Sat" => "Saturday",
            "Sun" => "Sunday"
    );

    public function __construct ()
    {
        $this->time = time();
        $this->firstline = true;
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
            if (floor(($this->time - $currentDayTimestamp) / (3600*24)) == 0){
                // Current day is the real current day
                $this->addTableCell("<b>". $dayOfMonthOutput ."<b>", $newline);
            } elseif ($currectDay >= 0 and $currectDay < $daysMonth) {
                // Just another day in current month
                $this->addTableCell($dayOfMonthOutput, $newline);
            } else {
                // Day in another month
                if (! $this->showOnlyDaysOfThisMonth) {
                    $this->addTableCell($dayOfMonthOutput, $newline);
                } else {
                    $this->addTableCell("", $newline);
                }
            }
        }
        $this->createTableFooter();
    }

    private function createTableHeader ()
    {
        $this->output .= "<table style=\"text-align: center;\">\n";
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
        $this->output .= "\t</tbody>\n";
        $this->output .= "</table>";
    }

    private function addTableCell ($input, $newline)
    {
        if ($newline && ! $this->firstline) {
            $this->output .= "\t\t</tr>\n";
        }
        
        if ($newline) {
            $this->output .= "\t\t<tr>\n";
        }
        
        $this->output .= "\t\t\t<td>" . $input . "</td>\n";
        
        $this->firstline = false;
    }

    public function setWeekdays ($weekdaysArray)
    {
        if (count($weekdaysArray) == 7) {
            $this->weekdays = $weekdaysArray;
        }
    }

    public function output ()
    {
        return $this->output;
    }

    public function showOnlyDaysOfThisMonth ($boolean)
    {
        if (is_bool($boolean)) {
            $this->showOnlyDaysOfThisMonth = $boolean;
        } else {
            if (is_string($boolean)) {
                if (strcmp($boolean, "true") == 0) {
                    $this->showOnlyDaysOfThisMonth = true;
                } elseif (strcmp($boolean, "false") == 0) {
                    $this->showOnlyDaysOfThisMonth = false;
                }
            }
        }
    }
}