<?php
class_exists('Setup', false) or include('classes/Setup.class.php');
class_exists('Utilities', false) or include('classes/Utilities.class.php');
class_exists('_MySQL', false) or include('classes/_MySQL.class.php');
class_exists('_FileCache', false) or include('classes/_FileCache.class.php');

$username = array_key_exists('username', $_POST) ? trim($_POST['username']) : '';
$passwd = array_key_exists('passwd', $_POST) ? trim($_POST['passwd']) : '';
$apiKey = array_key_exists('apiKey', $_POST) ? trim($_POST['apiKey']) : '';
$type = array_key_exists('type', $_POST) ? trim($_POST['type']) : '';
$data = array_key_exists('data', $_POST) ? trim($_POST['data']) : '';
$groupName = array_key_exists('groupName', $_POST) ? trim($_POST['groupName']) : '';


$result = array(
	'status'=>'',
	'result'=>array(),
);

$id = Utilities::validateLogin($username, $passwd, true, $apiKey);
if($id == 0) {
	$result['status'] = 'invalid login';
	output();
}

switch($type){
	case 'updateDomains':
		if($groupName=='') {
			$result['status'] = 'groupName is required';
			break;
		}
		$id = Utilities::ensureGroupExists($groupName);
		Utilities::updateDomains($data, $id);
		$result['status'] = 'success';
		break;

	case 'updateIPs':
		if($groupName=='') {
			$result['status'] = 'groupName is required';
			break;
		}
		$id = Utilities::ensureGroupExists($groupName);
		Utilities::updateIPs($data, $id);
		$result['status'] = 'success';
		break;

	case 'checkHostStatus':
		$result['status'] = 'success';
		Utilities::setBlockLists();
		$result['result'] = Utilities::checkBlacklists($data);
		break;

	case 'clearAllHostAndGroupData':
		$mysql = new _MySQL();
		$mysql->connect(Setup::$connectionArray);
		$mysql->runQuery("truncate table monitors");
		$mysql->runQuery("truncate table monitorGroup");
		$result['status'] = 'success';
		break;

	case 'blacklistStatus':
		$localCache = new _FileCache('blacklistmonitor-api', 90);
		$cacheKey = md5("$username|$passwd|$apiKey|$type|$data");
		$cacheData = $localCache->get($cacheKey);
		if ($cacheData !== false) {
			output($cacheData);
		}
		$mysql = new _MySQL();
		$mysql->connect(Setup::$connectionArray);
		$searchSQL = '';
		switch($data){
			case 'changed':
				$searchSQL .= " and lastStatusChanged = 1 ";
				break;
			case 'blocked':
				$searchSQL .= " and isBlocked = 1 ";
				break;
			case 'clean': $searchSQL .= " and isBlocked = 0 ";
				break;
			case 'all':
			default:
		}

		$rs = $mysql->runQuery("
			select ipDomain,isBlocked,rDNS,status,lastStatusChangeTime,lastUpdate
			from monitors
			where 1=1 $searchSQL");
		$result['status'] = 'success';
		$result['result'] = array();
		while($row = mysqli_fetch_array($rs, MYSQL_ASSOC)){
			$result['result'][] = array(
				'host'=>$row['ipDomain'],
				'isBlocked'=>$row['isBlocked'],
				'dns'=>$row['rDNS'],
				'status'=>unserialize($row['status']),
				'lastChanged'=>$row['lastStatusChangeTime'],
				'lastChecked'=>$row['lastUpdate'],
				);
		}
		$mysql->close();
		$localCache->set($cacheKey, $result);
		break;

	default:
		$result['status'] = 'no such method';
}

output();

function output($data = false){
	global $result;
	if($data!==false){
		echo(json_encode($data));
	}else{
		echo(json_encode($result));
	}
	exit();
}