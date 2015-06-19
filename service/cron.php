#!/usr/bin/php
<?php
set_time_limit(0);

//prevent non cli access
if(php_sapi_name()!=='cli') exit();

$dir = dirname(dirname(__FILE__));
class_exists('Setup', false) or include($dir.'/classes/Setup.class.php');
class_exists('Utilities', false) or include($dir.'/classes/Utilities.class.php');
class_exists('_MySQL', false) or include($dir.'/classes/_MySQL.class.php');
class_exists('_Logging', false) or include($dir.'/classes/_Logging.class.php');

$options = getopt("r:");
$options['r'] = isset($options['r']) ? $options['r'] : '';

$mysql = new _MySQL();
$mysql->connect(Setup::$connectionArray);

if($options['r']=='blockListStats'){
	$mysql->runQuery("update blockLists set blocksYesterday = blocksToday, cleanYesterday = cleanToday; ");
	$mysql->runQuery("update blockLists set blocksToday = 0, cleanToday = 0; ");
	_Logging::appLog("block list stats updated");
}
if($options['r']=='weekly'){
	$mysql->runQuery("update users set beenChecked = 0 where checkFrequency = 'weekly';");
	_Logging::appLog("weekly reset");
}
if($options['r']=='daily'){
	$mysql->runQuery("update users set beenChecked = 0 where checkFrequency = 'daily';");
	_Logging::appLog("daily reset");
}
if($options['r']=='8hour'){
	$mysql->runQuery("update users set beenChecked = 0 where checkFrequency = '8hour';");
	_Logging::appLog("8 hour reset");
}
if($options['r']=='2hour'){
	$mysql->runQuery("update users set beenChecked = 0 where checkFrequency = '2hour';");
	_Logging::appLog("2 hour reset");
}
if($options['r']=='1hour'){
	$mysql->runQuery("update users set beenChecked = 0 where checkFrequency = '1hour';");
	_Logging::appLog("1 hour reset");
}
if($options['r']=='deleteOld'){
	//clear orphan status
	$mysql->runQuery("
		delete mh
		from monitorHistory mh
		left join monitors m on m.ipDomain = mh.ipDomain
		where m.ipDomain IS NULL
		");

	$days = isset(Setup::$settings['history_keep_days']) ? (int)Setup::$settings['history_keep_days'] : 0;

	if($days > 0){
		$mysql->runQuery("
			delete from monitorHistory
			where monitorTime <= DATE_SUB(NOW(), INTERVAL $days day)
			");

		_Logging::appLog("old data deleted");
	}

}




