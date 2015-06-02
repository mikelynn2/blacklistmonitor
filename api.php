<?php
class_exists('Setup', false) or include('classes/Setup.class.php');
class_exists('Utilities', false) or include('classes/Utilities.class.php');
class_exists('_MySQL', false) or include('/var/www/html/_globalclasses/_MySQL.class.php');
class_exists('_FileCache', false) or include('/var/www/html/_globalclasses/_FileCache.class.php');

$emailAddress = array_key_exists('emailAddress', $_POST) ? trim($_POST['emailAddress']) : '';
$passwd = array_key_exists('passwd', $_POST) ? trim($_POST['passwd']) : '';
$apiKey = array_key_exists('apiKey', $_POST) ? trim($_POST['apiKey']) : '';
$type = array_key_exists('type', $_POST) ? trim($_POST['type']) : '';
$data = array_key_exists('data', $_POST) ? trim($_POST['data']) : '';

$result = array(
	'status'=>'',
	'result'=>array(),
);

$id = Utilities::validateLogin($emailAddress, $passwd, true, $apiKey);
if($id == 0) {
	$result['status'] = 'invalid login';
	output();
}

switch($type){
	case 'updateDomains':
		Utilities::updateDomains($id, $data);
		$result['status'] = 'success';
		break;

	case 'updateIPs':
		Utilities::updateIPs($id, $data);
		$result['status'] = 'success';
		break;

	case 'checkHostStatus':
		$result['status'] = 'success';
		Utilities::setBlockLists($id);
		$result['result'] = Utilities::checkBlacklists($data);
		break;

	case 'blacklistStatus':
		$localCache = new _FileCache('freeblacklistmonitor-api', 90);
		$cacheKey = md5("$emailAddress|$passwd|$apiKey|$type|$data");
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
			select ipDomain,isBlocked,rDNS,status,lastStatusChangeTime, lastUpdate
			from monitors
			where userId = $id $searchSQL");
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