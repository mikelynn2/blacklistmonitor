#!/usr/bin/php
<?php
set_time_limit(0);
ini_set('memory_limit', '1024M');

//prevent non cli access
if(php_sapi_name()!=='cli') exit();

$dir = dirname(dirname(__FILE__));
class_exists('Setup', false) or include($dir.'/classes/Setup.class.php');
class_exists('Utilities', false) or include($dir.'/classes/Utilities.class.php');
class_exists('Twitter', false) or include($dir.'/classes/Twitter.class.php');
class_exists('_MySQL', false) or include($dir.'/classes/_MySQL.class.php');
class_exists('_Logging', false) or include($dir.'/classes/_Logging.class.php');
class_exists('_MeasurePerformance', false) or include($dir.'/classes/_MeasurePerformance.class.php');
class_exists('PHPMailer', false) or include($dir.'/classes/class.phpmailer.php');
class_exists('SMTP', false) or include($dir.'/classes/class.smtp.php');

$options = getopt("i:");
$parentProcessId = isset($options['i']) ? (int)$options['i'] : 0;

if($parentProcessId == 0){
	_Logging::appLog("userJob called without all params");
	exit();
}

$m = new _MeasurePerformance();

$mysql = new _MySQL();
$mysql->connect(Setup::$connectionArray);

// get the user data
$user = Utilities::getAccount();

_Logging::appLog("user job started");

// get the accounts blacklists
Utilities::setBlockLists();

if( (empty(Utilities::$domainBlacklists)===true) && (empty(Utilities::$ipBlacklists)===true) ){
	_Logging::appLog("no blacklists configured");
	// mark this one as ran
	$mysql->runQuery("update users set beenChecked = 1, lastChecked = '".date('Y-m-d H:i:s')."'");
	exit();
}

//anything to monitor?
$monitorCount = Utilities::getHostCount($mysql);
if($monitorCount==0){
	_Logging::appLog("nothing to monitor");
	exit();
}

// reset checks
$mysql->runQuery("update monitors set beenChecked = 0");

// wait for results
while(true){
	if(!Utilities::is_process_running($parentProcessId)){
		_Logging::appLog("parent died - userJob exited");
		exit();
	}
	$rs = $mysql->runQuery("select ipDomain from monitors where beenChecked = 0 AND isActive = '1' limit 1;");
	if($row = mysqli_fetch_array($rs)){
		sleep(4);//wait 4 seconds for them to finish
	}else{
		break;
	}
}

$m->endWork();

$lastRunTime = (int)$m->runTime;

// mark this one as ran
$mysql->runQuery("update users set beenChecked = 1, lastChecked = '".date('Y-m-d H:i:s')."', lastRunTime = $lastRunTime");

// basic stats
$hostsChanged = Utilities::getHostChangeCount($mysql);
$errorHosts = Utilities::getHostErrorCount($mysql);
$newErrorHosts = Utilities::getHostErrorCount($mysql, 0, true);
$newCleanHosts = Utilities::getHostCleanCount($mysql, 0, true);


if($hostsChanged > 0 && $user['disableEmailNotices']==0){
	$table = "";
	$summary = "";
	$summaryText = "";
	$noticeMessage = "";
	$url = Setup::$settings['base_url'];

	$summary .= "<div><strong>";
	$summary .= "Total: ".number_format($monitorCount)."<br/>";
	$summary .= "Clean: ".number_format(($monitorCount-$errorHosts))."<br/>";
	$summary .= "Blocked: ".number_format($errorHosts)."<br/>";
	$summary .= "New Blocked: ".number_format($newErrorHosts)."<br/>";
	$summary .= "New Clean: ".number_format($newCleanHosts)."<br/>";
	$summary .= '</a>';
	$summary .= "</strong></div>";

	$summary .= "<br><div><a href='$url/m.php'>Monitor Groups</a></div><br>";

	if( (isset(Setup::$settings['email_report_detailed_host_changes']))
		&& (Setup::$settings['email_report_detailed_host_changes']==true) ){
			$table .= '<hr>';
			$table .= '<strong>New Blocks</strong><br>';
			$rs = $mysql->runQuery("select m.ipDomain, mg.groupName FROM monitors m inner join monitorGroup mg on mg.id = m.monitorGroupId where m.isBlocked = 1 and m.lastStatusChanged = 1 and m.isActive = '1' order by m.isDomain desc, m.ipDomain");
			$table .= '<table width="100%">';
			while($row = mysqli_fetch_array($rs)){
				$table .= '<tr>';
				$table .= '<td><a href="'.$url.'"/hostHistory.php?host='.urlencode($row['ipDomain']).'">'.$row['ipDomain'].'</a></td><td>'.$row['groupName'].'</td>';
				$table .= '</tr>';
			}
			$table .= '</table>';
			$table .= '<br><br>';
			$table .= '<hr>';
			$table .= '<strong>New Clean</strong><br>';
			$rs = $mysql->runQuery("select m.ipDomain, mg.groupName FROM monitors m inner join monitorGroup mg on mg.id = m.monitorGroupId where m.isBlocked = 0 and m.lastStatusChanged = 1 and m.isActive = '1' order by m.isDomain desc, m.ipDomain");
			$table .= '<table width="100%">';
			while($row = mysqli_fetch_array($rs)){
				$table .= '<tr>';
				$table .= '<td><a href="'.$url.'"/hostHistory.php?host='.urlencode($row['ipDomain']).'">'.$row['ipDomain'].'</a></td><td>'.$row['groupName'].'</td>';
				$table .= '</tr>';
			}
			$table .= '</table>';
	}

	$footer = "<br/><div><a href='$url/account.php'>Manage your account</a></div>";

	$summaryText .= "Total: ".number_format($monitorCount)."\n";
	$summaryText .= "Clean: ".number_format(($monitorCount-$errorHosts))."\n";
	$summaryText .= "Blocked: ".number_format($errorHosts)."\n";
	$summaryText .= "New Blocked: ".number_format($newErrorHosts)."\n";
	$summaryText .= "New Clean: ".number_format($newCleanHosts)."\n";

	$e = explode("\n",$user['noticeEmailAddresses']);
	if( (count($e) > 0) && (Setup::$settings['smtp_server']!='') ){
		// regular email
		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->Host = Setup::$settings['smtp_server'];
		$mail->From = Setup::$settings['from_email'];
		$mail->FromName = Setup::$settings['from_name'];
		foreach($e as $a){
			if(trim($a)!=''){
				$mail->AddAddress($a);
			}
		}
		$mail->Subject = Setup::$settings['alert_subject'];
		$mail->isHtml(true);
		$mail->Body = "$noticeMessage $summary $table $footer";
		$mail->Send();
	}
	
	// text message
	$e = explode("\n",$user['textMessageEmails']);
	if( (count($e) > 0) && (Setup::$settings['smtp_server']!='') ){
		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->Host = Setup::$settings['smtp_server'];
		$mail->From = Setup::$settings['from_email'];
		$mail->FromName = Setup::$settings['from_name'];
		foreach($e as $a){
			if(trim($a)!=''){
				$mail->AddAddress($a);
			}
		}
		$mail->Subject = Setup::$settings['alert_subject_sms'];
		$mail->isHtml(false);
		$mail->Body = "$url/m.php $summaryText";
		$mail->Send();
	}
	
	if($user['twitterHandle']!=''){
		$t = new Twitter();
		$t->message($user['twitterHandle'], $summaryText);
	}

	_Logging::appLog("user alert sent");

}

_Logging::appLog("user job ended");




