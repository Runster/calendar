<?php

/**
 * Error handling class for the calendar
 * @author Runster
 * @license https://creativecommons.org/licenses/by-sa/4.0/ CC BY-SA 4.0
 */
class CalendarError
{

	private $errorList = array();

	private $fileName;

	private $createFileLogs;

	private $logFile;

	public function __construct ($fileName, $createFileLogs)
	{
		$this->fileName = $fileName;
		$this->createFileLogs = $createFileLogs;
	}

	public function __destruct ()
	{
		if ($this->logFile) {
			fclose($this->logFile);
		}
	}

	/**
	 * Writes the error in the error array and logs the error, if it's specified
	 * F
	 * in the settings
	 *
	 * @param string $title        	
	 * @param string $description        	
	 */
	public function addError ($title, $description)
	{
		$this->errorList[$title] = $description;
		
		if ($this->createFileLogs) {
			if (! $this->logFile) {
				$this->logFile = fopen($this->fileName, "a");
			}
			fwrite($this->logFile, "[" . date("Y-m-d H:i:s", time()) . "] " . $title . ": " . $description . " \n");
		}
	}

	public function getErrorList ()
	{
		return $this->errorList;
	}
}
