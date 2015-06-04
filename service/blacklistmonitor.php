#!/bin/php
<?php

//prevent non cli access
if(php_sapi_name()!=='cli') exit();

$dir = dirname(dirname(__FILE__));
class_exists('Setup', false) or include($dir.'/classes/Setup.class.php');
class_exists('Utilities', false) or include($dir.'/classes/Utilities.class.php');
class_exists('_MySQL', false) or include($dir.'/classes/_MySQL.class.php');
class_exists('_MeasurePerformance', false) or include($dir.'/classes/_MeasurePerformance.class.php');

$options = getopt("p:i:");
$processType = isset($options['p']) ? $options['p'] : 'main';
$parentProcessId = isset($options['i']) ? (int)$options['i'] : 0;


switch ($processType) {
	case 'main';
		main();
		break;
	case 'monitorProcessWatch';
		monitorProcessWatch($parentProcessId);
		break;
	default:
		_Logging::appLog("Invalid process requested in main script");
		exit();
		break;
}


function main(){
	// start process watch
	$parentProcessId = getmypid();
	if($parentProcessId == false || $parentProcessId == 0) {
		_Logging::appLog("Parent process couldnt get pid");
	}

	$userProcessId = 0;
	$monitorProcessesId = 0;

	$mysql = new _MySQL();

	// control
	while (true) {
		try{
			$mysql->connect(Setup::$connectionArray);
			if(!Utilities::is_process_running($userProcessId)){
				$userCheck = $mysql->runQueryReturnVar("select username from users where beenChecked = 0");
				if($userCheck!==false){
					$cmd = 'php '.dirname(__FILE__).'/userJob.php -i '.$parentProcessId;
					$userProcessId = Utilities::run_in_background($cmd);
				}
			}
		} catch (Exception $e) {
			_Logging::appLog($e->getMessage());
		}
		if(!Utilities::is_process_running($monitorProcessesId)){
			$cmd = 'php '.dirname(__FILE__).'/blacklistmonitor.php -p monitorProcessWatch -i '.$parentProcessId;
			$monitorProcessesId = Utilities::run_in_background($cmd);
		}
		sleep(15);//15 seconds
	}

}

function monitorProcessWatch($parentProcessId){

	$m = new _MeasurePerformance();
	$mysql = new _MySQL();
	$mysql->connect(Setup::$connectionArray);

	$parallelProcessesMonitors = Setup::$settings['max_monitor_processes'];

	$monitorProcesses = array();
	$processCountMonitors = 0;

	$ipDomain = false;

	while (true) {
		// are we still running?
		if(!Utilities::is_process_running($parentProcessId)){
				_Logging::appLog("Parent Stopped - monitorStartWatch exited");
				exit();
		}

		$processCountMonitors = count($monitorProcesses);

		if ($processCountMonitors < $parallelProcessesMonitors){
				$ipDomain = Utilities::getNextMonitor($mysql);
				if ($ipDomain!==false) {
						// start it
						$cmd = 'php '.dirname(__FILE__).'/monitorJob.php -h '.escapeshellarg($ipDomain);
						$pid = Utilities::run_in_background($cmd);
						$m->work(1);
						$monitorProcesses[] = $pid;
				}
		}

		// was there any work?
		if($ipDomain===false){
				sleep(10);//10 seconds
		}else{
				usleep(10000);//ideal time 10ms
		}

		// delete finished processes
		for ($x = 0; $x < $processCountMonitors; $x++) {
				if(isset($monitorProcesses[$x])){
						if(!Utilities::is_process_running($monitorProcesses[$x])){
								unset($monitorProcesses[$x]);
						}
				}
		}

		// fix array index
		$monitorProcesses = array_values($monitorProcesses);

		$processCountMonitors = count($monitorProcesses);

		//randomly reset counter every now and then
		if(mt_rand(1,5000)==1){
			$m->endWork();
			_Logging::appLog("App Avg Hosts/sec: {$m->avgPerformance}\tMonitor Threads: $processCountMonitors/$parallelProcessesMonitors");
			$m = new _MeasurePerformance();
		}

	}

}
