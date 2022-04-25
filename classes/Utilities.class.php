<?php
class_exists('_IpAddresses', false) or include('_IpAddresses.class.php');
class_exists('_FileCache', false) or include('_FileCache.class.php');
class_exists('Setup', false) or include('Setup.class.php');
class_exists('_MySQL', false) or include('_MySQL.class.php');

class Utilities {

	public static $domainBlacklists = array();
	public static $ipBlacklists = array();
	public static $hostNotCheckedMessage = '1ST CHECK SCHEDULED';
	public static $frequencyCheckOptions = array('1hour', '2hour', '8hour', 'daily', 'weekly');
	public static $mysql = false;

	public static function setBlockLists(){
		$localCache = new _FileCache('blacklistmonitor-Utilities-BlockLists', 60);
		$cacheKey = 'bl';
		$cacheData = $localCache->get($cacheKey);
		if ($cacheData !== false) {
			if(isset($cacheData['domains']) && isset($cacheData['ips']) ) {
				self::$domainBlacklists = $cacheData['domains'];
				self::$ipBlacklists = $cacheData['ips'];
				return true;
			}
		}
		$mysql = new _MySQL();
		$mysql->connect(Setup::$connectionArray);
		$sql = "select host,monitorType from blockLists where isActive = '1';";
		$rs = $mysql->runQuery($sql);
		$cacheData['domains'] = array();
		$cacheData['ips'] = array();
		while($row = mysqli_fetch_array($rs)){
			if($row['monitorType']=='ip'){
				$cacheData['ips'][] = $row['host'];
			}else{
				$cacheData['domains'][] = $row['host'];
			}
		}
		$mysql->close();
		$localCache->set($cacheKey, $cacheData);
		self::$domainBlacklists = $cacheData['domains'];
		self::$ipBlacklists = $cacheData['ips'];
		return false;
	}

	public static function randomDNSServer(){
		return Setup::$settings['dns_servers'][mt_rand(0,(count(Setup::$settings['dns_servers'])-1))];
	}

	public static $isBlocked = 0;
	
	public static function checkBlacklists($domainOrIp, $reportClean=false){
		self::$isBlocked = 0;
		$return = array();
		if(_IpAddresses::isIPAddress($domainOrIp)){
			foreach(self::$ipBlacklists as $server){
				$r = self::ipCheck($domainOrIp, $server);
				if($r!='') {
					self::$isBlocked = 1;
					self::logBlockListStats($server, 'ip', true);
				}else{
					self::logBlockListStats($server, 'ip', false);
				}
				if($r!='' || $reportClean==true) {
					$return[] = array(trim($server),$r);
				}
			}
		}else{
			foreach(self::$domainBlacklists as $server){
				$r = self::domainCheck($domainOrIp, $server);
				if($r!='') {
					self::$isBlocked = 1;
					self::logBlockListStats($server, 'domain', true);
				}else{
					self::logBlockListStats($server, 'domain', false);
				}
				if($r!='' || $reportClean==true) {
					$return[] = array(trim($server),$r);
				}
			}
		}
		return $return;
	}

	public static function domainCheck($domain, $server){
		$server = trim($server);
		$host = escapeshellarg("$domain.$server");
		$t = "dig @".self::randomDNSServer()." +time=".Setup::$settings['dns_request_timeout']." $host";
//		echo("$t</br>");
		$text = shell_exec($t);
		$test = Utilities::parseBetweenText(
			$text, 
			";; ANSWER SECTION:\n", 
			"\n\n", 
			false, 
			false,
			true);
		$testArray = explode("\t", $test);
		$test = end($testArray);

		if(trim($test)!=''){
			if(Setup::$settings['rbl_txt_extended_status']){
				$t = "dig @".self::randomDNSServer()." +time=".Setup::$settings['dns_request_timeout']." $host txt";
		//		echo("$t</br>");
				$text = shell_exec($t);
				$test = Utilities::parseBetweenText(
					$text,
					";; ANSWER SECTION:\n", 
					"\n\n",
					false,
					false,
					true);
				$testArray = explode("\t", $test);
				$test = end($testArray);
				$test = str_replace(array('\'','"'),'',$test);
			}else{
				$test = 'blocked';
			}
		}
		if(strripos($test,'not found')!==false) return '';
		if(strripos($test,'SERVFAIL')!==false) return '';
		return trim($test);
	}

	public static function ipCheck($ip, $server){
		if(_IpAddresses::isIPAddress($ip)===false) return '';
		$server = trim($server);

		$parts = explode('.', $ip);
		$reverseIp = implode('.', array_reverse($parts));
		$text = "";
		$host = escapeshellarg("$reverseIp.$server");
		$t = "dig @".self::randomDNSServer()." +time=".Setup::$settings['dns_request_timeout']." $host";
//		echo("$t</br>");
		$text = shell_exec($t);
		$test = Utilities::parseBetweenText(
			$text,
			";; ANSWER SECTION:\n",
			"\n\n",
			false,
			false,
			true);
		$testArray = preg_split("/IN\s+A\s+/i", $test);
		$test = trim(end($testArray));
		//		echo "<pre>$test</pre>\n";
		if(trim($test)!=''){
			if(Setup::$settings['rbl_txt_extended_status']){
				$t = "dig @".self::randomDNSServer()." +time=".Setup::$settings['dns_request_timeout']." $host txt";
		//		echo("$t</br>");
				$text = shell_exec($t);
				$test2 = Utilities::parseBetweenText(
					$text,
					";; ANSWER SECTION:\n",
					"\n\n",
					false,
					false,
					true);
				$testArray = preg_split("/IN\s+TXT\s+/i", $test2);
				$test2 = trim(end($testArray));
				$test2 = str_replace(array('\'','"'),'',$test2);
				switch($server){
					case 'bl.mailspike.net':
						$a = explode("|",$test2);
						$test = (isset($a[1])) ? 'Listed ' . $a[1] : $test2;
					break;
				}
				if($test2!='') $test = $test2;
			}else{
				$test = 'blocked';
			}
		}
		if(strripos($test,'not found')!==false) return '';
		if(strripos($test,'SERVFAIL')!==false) return '';
		return trim($test);
	}

	public static function logBlockListStats($server, $monitorType, $isBlocked){
		if(Setup::$settings['log_rbl_stats']==0) return true;
		$mysql = new _MySQL();
		$mysql->connect(Setup::$connectionArray);
		if($isBlocked){
			$sql = "update blockLists set blocksToday=(blocksToday+1), lastBlockReport=now() where host = '".$mysql->escape($server)."' and monitorType = '$monitorType';";
		}else{
			$sql = "update blockLists set cleanToday=(cleanToday+1) where host = '".$mysql->escape($server)."' and monitorType = '$monitorType';";
		}
		$mysql->runQuery($sql);
		$mysql->close();
	}

	public static function ensureGroupExists($groupName){
		$mysql = new _MySQL();
		$mysql->connect(Setup::$connectionArray);
		$id = $mysql->runQueryReturnVar("select id from monitorGroup where groupName = '".$mysql->escape($groupName)."'");
		if($id===false){
			$mysql->runQuery("insert into monitorGroup set groupName = '".$mysql->escape($groupName)."'");
			$id = $mysql->identity;
		}
		$mysql->close();
		return $id;
	}

	public static function updateDomains($domains, $monitorGroupId){
		$domains = trim($domains);
		$monitorGroupId = (int)$monitorGroupId;
		if($monitorGroupId===0) return false;
		$mysql = new _MySQL();
		$mysql->connect(Setup::$connectionArray);
		$mysql->runQuery("update monitors set keepOnUpdate = 0 where isDomain = 1 and monitorGroupId = $monitorGroupId");
		$mysql->runQuery("update users set lastUpdate = '".$mysql->escape(date('Y-m-d H:i:s'))."'");
		$mysql->runQuery("update monitorGroup set domains = '".$mysql->escape($domains)."' where id = $monitorGroupId");
		$domainArray = preg_split('/\s+/', $domains);
		foreach($domainArray as $d){
			$d = trim($d);
			$d = str_ireplace('http://', '', $d);
			$d = str_ireplace('https://', '', $d);
			$d = str_ireplace('/', '', $d);
			$d = preg_replace('/[[:^print:]]/', '', $d);
			if($d != ''){
				$mysql->runQuery("
					update monitors set
						keepOnUpdate = 1
					where
						monitorGroupId = $monitorGroupId
						and ipDomain = '".$mysql->escape($d)."'
						and isDomain = 1
				");
				if($mysql->affectedRows == 0){
					$mysql->runQuery("insert ignore into monitors set
					monitorGroupId = $monitorGroupId,
					ipDomain = '".$mysql->escape($d)."',
					isDomain = 1,
					keepOnUpdate = 1
					");
				}
			}
		}
		$mysql->runQuery("delete from monitors where keepOnUpdate = 0 and isDomain = 1 and monitorGroupId = $monitorGroupId");
		$mysql->close();
	}

	public static function updateIPs($ips, $monitorGroupId){
		$ips = trim($ips);
		$monitorGroupId = (int)$monitorGroupId;
		if($monitorGroupId===0) return false;
		$mysql = new _MySQL();
		$mysql->connect(Setup::$connectionArray);
		$mysql->runQuery("update monitors set keepOnUpdate = 0 where isDomain = 0 and monitorGroupId = $monitorGroupId");
		$mysql->runQuery("update users set lastUpdate = '".$mysql->escape(date('Y-m-d H:i:s'))."'");
		$mysql->runQuery("update monitorGroup set ips = '".$mysql->escape($ips)."' where id = $monitorGroupId");
		$ipsArray  = preg_split('/\s+/', $ips);
		foreach($ipsArray as $i){
			// ip checks
			if(_IpAddresses::isIPAddress($i)){
				$mysql->runQuery("
					update monitors set
					keepOnUpdate = 1
					where
						monitorGroupId = $monitorGroupId
						and ipDomain = '".$mysql->escape($i)."'
						and isDomain = 0
					");
				if($mysql->affectedRows == 0){
					$mysql->runQuery("insert ignore into monitors set
						monitorGroupId = $monitorGroupId,
						ipDomain = '".$mysql->escape($i)."', 
						isDomain = 0,
						keepOnUpdate = 1
						");
				}
			}else{
				//cidr /24's max...
				if(trim($i)!=''){
					if(strpos($i, ' ')!==false) continue;
					if(strpos($i, ':')!==false) continue;
					$range = _IpAddresses::cidrToRange($i);
					if($range===false) continue;
					$start = explode('.', $range[0]);
					$end = explode('.', $range[1]);
					if($range[0]==0) continue;// starts with 0
					for($i = $start[3]; $i <= $end[3]; $i++){
						$host = "{$start[0]}.{$start[1]}.{$start[2]}.$i";
						if(_IpAddresses::isIPAddress($host)){
							$mysql->runQuery("
								update monitors set
									keepOnUpdate = 1
									where
										monitorGroupId = $monitorGroupId
										and ipDomain = '".$mysql->escape($host)."'
										and isDomain = 0
								");
							if($mysql->affectedRows == 0){
								$mysql->runQuery("insert ignore into monitors set
									monitorGroupId = $monitorGroupId,
									ipDomain = '".$mysql->escape($host)."',
									isDomain = 0,
									keepOnUpdate = 1
									");
							}
						}
					}
				}
			}
		}
		$mysql->runQuery("delete from monitors where keepOnUpdate = 0 and isDomain = 0 and monitorGroupId = $monitorGroupId");
		$mysql->close();
	}

	public static function isLoggedIn(){
		if(isset($_SESSION['id']) && (int)$_SESSION['id'] > 0){
			return $_SESSION['id'];
		}else{
			return false;
		}
	}

	public static function getAccount(){
		$mysql = new _MySQL();
		$mysql->connect(Setup::$connectionArray);
		$ret = false;
		$rs = $mysql->runQuery("
			select
				username,
				passwd,
				apiKey,
				beenChecked,
				disableEmailNotices,
				noticeEmailAddresses,
				textMessageEmails,
				twitterHandle,
				apiCallbackURL,
				checkFrequency
			from users limit 1");
		while($row = mysqli_fetch_array($rs)){
			$ret = $row;
		}
		$mysql->close();

		if(!$ret){
			//account
			_Logging::appLog("no user account");
			exit();
		}

		return $ret;
	}

	public static function validateLogin($userName, $passwd, $api = false, $apiKey = ''){
		$mysql = new _MySQL();
		$mysql->connect(Setup::$connectionArray);
		$sql = "
		select username
		from users
		where ";
		if(trim($apiKey) != ''){
			$sql .= " apiKey = '".$mysql->escape($apiKey)."'";
		}else{
			$sql .= " passwd = '".$mysql->escape(md5($passwd))."' 
			and username = '".$mysql->escape($userName)."'";
		}
		$rs = $mysql->runQuery($sql);
		$id = 0;
		while($row = mysqli_fetch_array($rs)){
			$id = 1;
		}
		$mysql->close();
		return $id;
	}

	public static function lookupHostDNS($host){
		if(_IpAddresses::isIPAddress($host)){
			return _IpAddresses::getHostByIp($host);
		}else{
			$host = escapeshellarg($host);
			exec('host -t a -W 2 '.$host, $output, $return);
			if ($return !== 0) {
				return '';
			}else{
				$output = implode($output);
				$ips = _IpAddresses::getAllIPsFromString($output, true);
				$ir = "";
				foreach($ips as $ip){
					$ir .= "$ip,";
				}
				return trim($ir,',');
			}

			/*
			if(strripos($host,'not found')!==false) return false;
			if(strripos($host,'SERVFAIL')!==false) return false;
			$phost = trim(end(explode(' ', $host)));
			if(strripos($phost,'reached')!==false) return false;
			return $phost;
			*/
		}
	}

	public static function testAPICallback($url){
		return self::makeAPICallback($url,
			'samplehosttest.com', 
			true, 
			'reverse-dns-sample.samplehosttest.com',
			'a:2:{i:0;a:2:{i:0;s:12:"l2.apews.org";i:1;s:87:"Listed at APEWS-L2 - visit http://www.apews.org/?page=test&C=131&E=1402188&ip=127.0.0.1";}i:1;a:2:{i:0;s:22:"b.barracudacentral.org";i:1;s:9:"127.0.0.2";}}'
			);
	}

	public static function makeAPICallback($url, $host, $isBlocked, $rDNS, $status){
		if(substr($url,0,4)!='http') return false;

		$vars = json_encode(
			array(
				'host'=>$host,
				'isBlocked'=>(boolean)$isBlocked,
				'rDNS'=>$rDNS,
				'blocks'=>unserialize($status)
			)
		);
		$err = true;
		try{
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_POST,true);
			curl_setopt($ch,CURLOPT_FAILONERROR,true);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
			curl_setopt($ch,CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
			curl_exec($ch);
			if (curl_errno($ch)) $err = false;
		} catch (Exception $e) {
			$err = false;
		}
		return $err;
	}

	public static function isValidEmail($emailAddress){
		if (filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
			return true;
		}
		return false;
	}

	 public static function parseBetweenText(
		$text,
		$beginText,
		$endText,
		$removeSpace=true,
		$removeHtmlTags=true,
		$firstResultOnlyNoArray=false) {
		$results = array();
		$endPos = 0;
		while(true) {
				$beginPos = stripos($text, $beginText, $endPos);
				if($beginPos===false) break;
				$beginPos = $beginPos+strlen($beginText);
				$endPos = stripos($text, $endText, $beginPos);
				if($endPos===false) break;
				$result = substr($text, $beginPos, $endPos-$beginPos);
				if($removeSpace){
						$result = str_replace("\t","",$result);
						$result = str_replace("\n","",$result);
						$result = preg_replace("/  /"," ",$result);
						$result = preg_replace("~[\s]{2}?[\t]?~i"," ",$result);
						$result = str_replace("  "," ",$result);
						$result = trim($result);
				}
				if($removeHtmlTags){
						$result = strip_tags($result);
				}
				if($firstResultOnlyNoArray) return $result;
				if($result != '') $results[] = $result;
		}
		return ($firstResultOnlyNoArray && empty($results) ? '' : $results);
	 }

	public static function getNextMonitor($mysql){
		$ipDomain = $mysql->runQueryReturnVar("select ipDomain from monitors where beenChecked = 0 AND isActive = '1'");
		$mysql->runQuery("update monitors set beenChecked = 1 where ipDomain = '".$mysql->escape($ipDomain)."'");
		return $ipDomain;
	}

	public static function getHostChangeCount($mysql, $monitorGroupId = 0) {
		$sql = '';
		$monitorGroupId = (int)$monitorGroupId;
		if($monitorGroupId > 0) $sql = " and monitorGroupId = $monitorGroupId";
		return $mysql->runQueryReturnVar("select COALESCE(count(ipDomain),0) as cnt from monitors where lastStatusChanged = 1 and isActive = '1' $sql");
	}

	public static function getHostErrorCount($mysql, $monitorGroupId = 0, $onlyNew = false) {
		$sql = '';
		$monitorGroupId = (int)$monitorGroupId;
		if($monitorGroupId > 0) $sql = " and monitorGroupId = $monitorGroupId";
		if($onlyNew) $sql .= " and lastStatusChanged = 1 ";
		return $mysql->runQueryReturnVar("select COALESCE(count(ipDomain),0) as cnt from monitors where isBlocked = 1 and isActive = '1' $sql");
	}

	public static function getHostCleanCount($mysql, $monitorGroupId = 0, $onlyNew = false) {
		$sql = '';
		$monitorGroupId = (int)$monitorGroupId;
		if($monitorGroupId > 0) $sql = " and monitorGroupId = $monitorGroupId";
		if($onlyNew) $sql .= " and lastStatusChanged = 1 ";
		return $mysql->runQueryReturnVar("select COALESCE(count(ipDomain),0) as cnt from monitors where isBlocked = 0 and isActive = '1' $sql");
	}

	public static function getHostCount($mysql, $monitorGroupId = 0) {
		$sql = '';
		$monitorGroupId = (int)$monitorGroupId;
		if($monitorGroupId > 0) $sql = " where monitorGroupId = $monitorGroupId";
		return $mysql->runQueryReturnVar("select COALESCE(count(ipDomain),0) as cnt from monitors $sql");
	}

	//CREDIT: http://braincrafted.com/php-background-processes/
	public static function is_process_running($pid){
		$pid = (int)$pid;
		if($pid == 0) return false;
		if(file_exists('/proc/'.$pid)){
			return true;
		}else{
			return false;
		}
	}

	public static function run_in_background($command, $priority = 0) {
		$log = Setup::$settings['log_path'];
		if($priority !=0){
			$pid = shell_exec("nohup nice -n $priority $command >> $log 2>&1 & echo $!");
		}else{
			$pid = shell_exec("nohup $command >> $log 2>&1 & echo $!");
		}
		return($pid);
	}

}





