<?php
class_exists('Setup', false) or include('classes/Setup.class.php');
class_exists('Utilities', false) or include('classes/Utilities.class.php');
class_exists('Twitter', false) or include('classes/Twitter.class.php');
class_exists('_MySQL', false) or include('classes/_MySQL.class.php');
class_exists('PHPMailer', false) or include('classes/class.phpmailer.php');

if(Utilities::isLoggedIn()===false){
	header('Location: login.php');
	exit();
}

$titlePreFix = "account";
$message = array();

$username = array_key_exists('username', $_POST) ? substr(trim($_POST['username']),0,100) : '';
$noticeEmailAddresses = array_key_exists('noticeEmailAddresses', $_POST) ? substr($_POST['noticeEmailAddresses'],0,8000) : '';
$textMessageEmails = array_key_exists('textMessageEmails', $_POST) ? substr($_POST['textMessageEmails'],0,8000) : '';
$passwd = array_key_exists('passwd', $_POST) ? substr($_POST['passwd'],0,32) : '';
$passwdOld = array_key_exists('passwdOld', $_POST) ? substr($_POST['passwdOld'],0,32) : '';
$apiKey = array_key_exists('apiKey', $_POST) ? substr($_POST['apiKey'],0,32) : '';
$domains = array_key_exists('domains', $_POST) ? trim(strtolower($_POST['domains'])) : '';
$ips = array_key_exists('ips', $_POST) ? trim($_POST['ips']) : '';
$disableEmailNotices = array_key_exists('disableEmailNotices', $_POST) ? (int)$_POST['disableEmailNotices'] : 0;
$beenChecked = array_key_exists('beenChecked', $_POST) ? (int)$_POST['beenChecked'] : 0;
$twitterHandle = array_key_exists('twitterHandle', $_POST) ? substr(trim($_POST['twitterHandle']),0,15) : '';
$twitterHandle = str_replace('@','',$twitterHandle);
$apiCallbackURL = array_key_exists('apiCallbackURL', $_POST) ? substr(trim($_POST['apiCallbackURL']),0,2000) : '';
$testUrl = array_key_exists('testUrl', $_GET) ? $_GET['testUrl'] : '';

$mysql = new _MySQL();
$mysql->connect(Setup::$connectionArray);

// audit check frequency
$checkFrequency = array_key_exists('checkFrequency', $_POST) ? $_POST['checkFrequency'] : '';

if($testUrl!=''){
	if(Utilities::testAPICallback($testUrl)){
		echo('true');
	}else{
		echo('false');
	}
	exit();
}

if (isset($_POST["submit"])) {
	if($passwd=='') $message[] = 'You must select a password.';

	if($passwdOld != $passwd){
		$passwdOld = md5($passwd);
	}

	$ta = explode("\n",$noticeEmailAddresses);
	$noticeEmailAddresses = "";
	foreach($ta as $e){
		$e = trim($e);
		if(Utilities::isValidEmail($e)){
			$noticeEmailAddresses .= "$e\n";
		}
	}
	$ta = explode("\n",$textMessageEmails);
	$textMessageEmails = "";
	foreach($ta as $e){
		$e = trim($e);
		if(Utilities::isValidEmail($e)){
			$textMessageEmails .= "$e\n";
		}
	}
	//TODO: make sure blacklists are domains with an ip address on them
	if(count($message) == 0){
		//update
		$mysql->runQuery("
			update users set username = '".$mysql->escape($username)."',
			passwd = '".$mysql->escape($passwdOld)."',
			apiKey = '".$mysql->escape($apiKey)."',
			twitterHandle = '".$mysql->escape($twitterHandle)."',
			twitterHandle = '".$mysql->escape($twitterHandle)."',
			lastUpdate = '".date('Y-m-d H:i:s')."',
			twitterHandle = '".$mysql->escape($twitterHandle)."',
			noticeEmailAddresses = '".$mysql->escape(trim($noticeEmailAddresses))."',
			textMessageEmails = '".$mysql->escape(trim($textMessageEmails))."',
			apiCallbackURL = '".$mysql->escape($apiCallbackURL)."',
			checkFrequency = '".$mysql->escape($checkFrequency)."',
			disableEmailNotices = $disableEmailNotices
			");
		if($beenChecked==1){
			$mysql->runQuery("update users set beenChecked = 0");
				$message[] = "Check scheduled.";
		}
		Utilities::updateDomains($domains);
		Utilities::updateIPs($ips);
		if($twitterHandle!=''){
			$t = new Twitter();
			$t->follow($twitterHandle);
		}
		$message[] = "Account updated.";
	}
}
$user = Utilities::getAccount();
if(!$user){
	//invalid account
	echo("<script>window.location='login.php?logout=1';</script>");
	exit();
}
?>
<?php include('header.inc.php'); ?>

<?php include('accountSubnav.inc.php'); ?>

<div class="row">
	<div class="col-md-3">
	<ul class="nav nav-pills nav-stacked">
		<li>Hosts Total <span class="badge pull-right"><?php echo(number_format(Utilities::getHostCount($mysql)));?></span></li>
		<li>Hosts Blocked <span class="badge pull-right"><?php echo(number_format(Utilities::getHostErrorCount($mysql)));?></span></li>
	</ul>
	</div>
</div>

<script>
$(document).ready(function() {
	$('#apiCallBackAlert').hide();
	$('[data-toggle="popover"]').popover({
		trigger: 'hover',
		'placement': 'auto'
	});
});

function testAPIUrl(){
	var url = $("#apiCallbackURL").val();
	$.get("account.php", { testUrl: url}, function(data) {
		if(data=='true'){
			$("#apiCallBackAlert").html("Callback was successful!");
		}else{
			$("#apiCallBackAlert").html("Error connecting to "+url);
		}
		$('#apiCallBackAlert').show();
	});
	
}
</script>

<?php
foreach($message as $m){
	echo("<div class=\"alert alert-info alert-dismissable\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>$m</div>");
}
?>
    
<form id="accountForm" class="form-horizontal" role="form" action="account.php" method="post">
	<div class="form-group">
		<label class="col-sm-3 control-label" for="username">Login Username</label>
		<div class="col-sm-6">
			<input class="form-control" type="text" id="username" name="username" value="<?php echo($user['username']);?>" placeholder="username">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="passwd">Password</label>
		<div class="col-sm-6">
			<input class="form-control" type="password" name="passwd" id="passwd" value="<?php echo($user['passwd']);?>" class="form-control" placeholder="Password">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="apiKey">API Key</label>
		<div class="col-sm-6">
			<input class="form-control" maxlength="32" type="text" id="apiKey" name="apiKey" value="<?php echo($user['apiKey']);?>" placeholder="api key">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="checkFrequency">Check Frequency</label>
		<div class="col-sm-6">
			<select id="checkFrequency" name="checkFrequency" class="form-control">
				<option value="weekly"<?php if($user['checkFrequency']=='weekly') echo ' selected'?>>Weekly</option>
				<option value="daily"<?php if($user['checkFrequency']=='daily') echo ' selected'?>>Daily</option>
				<option value="8hour"<?php if($user['checkFrequency']=='8hour') echo ' selected'?>>Every 8 Hours</option>
				<option value="2hour"<?php if($user['checkFrequency']=='2hour') echo ' selected'?>>Every 2 Hours</option>
				<option value="1hour"<?php if($user['checkFrequency']=='1hour') echo ' selected'?>>Hourly</option>
			</select>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="noticeEmailAddresses">Notify Emails<br/>(one email per line)
		</label>
		<div class="col-sm-6">
			<textarea class="form-control" name="noticeEmailAddresses" id="noticeEmailAddresses" rows="3"><?php echo(trim($user['noticeEmailAddresses']))?></textarea>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="textMessageEmails">Text Message Emails<br/>(one email per line) <small><a target="_blank" href="http://www.emailtextmessages.com/">example</a>: 10digitphonenumber@txt.att.net</small></label>
		<div class="col-sm-6">
			<textarea class="form-control" name="textMessageEmails" id="textMessageEmails" rows="3"><?php echo(trim($user['textMessageEmails']))?></textarea>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="twitterHandle">Twitter Handle <a class="glyphicon glyphicon-info-sign" href="#" data-toggle="popover" data-content="Enables direct message alerts for twitter.  You must follow us on twitter for us to be able to message you."></a></label>
		<div class="col-sm-6">
			<div class="input-group">
				<span class="input-group-addon">@</span>
				<input class="form-control" type="text" id="twitterHandle" name="twitterHandle" value="<?php echo($user['twitterHandle']);?>" placeholder="twitter username">
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="domains">Domains<br/>(one domain per line)<a class="glyphicon glyphicon-info-sign" href="#" data-toggle="popover" data-content="Enter root domains only. Subdomains don't matter.  For example put gmail.com - not mail.gmail.com."></a></label>
		<div class="col-sm-6">
			<textarea class="form-control" name="domains" id="domains" rows="5"><?php echo(trim($user['domains']))?></textarea>
		</div>
	</div>
	<div class="form-group">  
		<label class="col-sm-3 control-label" for="ips">IPs<br/>(IPv4 Only, one ip or <a target="_new" href="http://en.wikipedia.org/wiki/Classless_Inter-Domain_Routing">cidr</a> <br/> one per line, max size per line /24)</label>
		<div class="col-sm-6">
			<textarea class="form-control" name="ips" id="ips" rows="5"><?php echo(trim($user['ips']))?></textarea>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="apiCallbackURL">API Callback URL
			<a class="glyphicon glyphicon-info-sign" href="apiDocumentation.php#callBack" target="_blank"></a>
		</label>
		<div class="col-sm-6">
			<div class="input-group">
				<input class="form-control" type="text" id="apiCallbackURL" name="apiCallbackURL" value="<?php echo($user['apiCallbackURL']);?>" placeholder="api callback url">
				<span class="input-group-btn">
					<button class="btn btn-default" type="button" onclick="testAPIUrl()">Test Callback</button>
				</span>
			</div>
		<div id="apiCallBackAlert" class="alert alert-info alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>error</div>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="disableEmailNotices"><input type="checkbox" id="disableEmailNotices" name="disableEmailNotices" value="1" class="input-block-level" <?php if($user['disableEmailNotices']==1) echo(' checked');?>></label>
		<div class="col-sm-6">
			Disable Notices<br/><small>You can pause receiving email/text alerts when your status changes.  Useful if you have a frequently changing network.  This does not pause api call backs.</small>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="beenChecked"><input type="checkbox" id="beenChecked" name="beenChecked" value="1" class="input-block-level"></label>
		<div class="col-sm-6">
			Request Immediate Check<br/><small>Selecting this will request your hosts be checked as soon as possible.</small>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-6">
			<button type="submit" name="submit" value="submit" class="btn btn-primary">Save changes</button>
		</div>
	</div>
	<input type="hidden" value="<?php echo($user['passwd']);?>" name="passwdOld"/>
</form>

<br/><br/><br/><br/>

<?php include('footer.inc.php'); ?>
