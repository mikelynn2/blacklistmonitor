<?php
class _Logging {

	public static $logFileLocation = '';

	public static function writeLogLine($logLine, $logFile) {
		if (!($handle = fopen($logFile, 'a'))) {
			throw new Exception('Failed to open/create log file: ' . $logFile);
		}
		if (fwrite($handle, $logLine . "\n") === false) {
			fclose($handle);
			throw new Exception('Failed to write to log file: ' . $logFile);
		} else {
			fclose($handle);
		}
	}

	public static function appLog($logLine){
		$logLine = date("Y-m-d H:i:s")."\t".$logLine;
		if(self::$logFileLocation != ''){
			self::writeLogLine($logLine, self::$logFileLocation);
		}else{
			echo($logLine."\n");
		}
	}

	public static function out($logLine, $outType = STDOUT, $includeDateTime = true){
		if($includeDateTime){
			fprintf($outType, "%s\t%s\n",date("Y-m-d H:i:s"), $logLine);
		}else{
			fprintf($outType, "%s\n", $logLine);
		}
	}

}