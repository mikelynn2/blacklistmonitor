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

$titlePreFix = "Edit Monitor Group";

$params = array_merge($_GET, $_POST);
$id = array_key_exists('id', $params) ? (int)$params['id'] : 0;
$groupName = array_key_exists('groupName', $params) ? substr(trim($params['groupName']),0,100) : '';
$domains = array_key_exists('domains', $params) ? trim(strtolower($params['domains'])) : '';
$ips = array_key_exists('ips', $params) ? trim($params['ips']) : '';
$deleteGroup = array_key_exists('deleteGroup', $params) ? trim($params['deleteGroup']) : '';

 
$mysql = new _MySQL();
$mysql->connect(Setup::$connectionArray);


if($deleteGroup!=''){
	$mysql->runQuery("delete from monitorGroup where id = $id");
	$mysql->runQuery("delete from monitors where monitorGroupId = $id");
	echo("<script>window.location='monitorGroup.php';</script>");
	exit();
}

if (isset($_POST["submit"])) {

	//TODO: make sure blacklists are domains with an ip address on them
	if($id !== 0){
		//update
		$mysql->runQuery("
			update monitorGroup set groupName = '".$mysql->escape($groupName)."',
				ips = '".$mysql->escape($ips)."',
				domains = '".$mysql->escape($domains)."'
			where id = $id
			");
	}else{
		$mysql->runQuery("
			insert into monitorGroup set groupName = '".$mysql->escape($groupName)."',
				ips = '".$mysql->escape($ips)."',
				domains = '".$mysql->escape($domains)."'
			");
		$id = $mysql->identity;
	}
	Utilities::updateDomains($domains, $id);
	Utilities::updateIPs($ips, $id);
	echo("<script>window.location='monitorGroup.php';</script>");
	exit();
}

$group = array(
	'groupName'=>'',
	'ips'=>'',
	'domains'=>'',
);
$rs = $mysql->runQuery("select * from monitorGroup where id = $id");
while($row = mysqli_fetch_array($rs)){
	$group = $row;
}

?>
<?php include('header.inc.php'); ?>

<?php include('accountSubnav.inc.php'); ?>

<script>
function clickDelete(){
	var r = confirm("Are you sure you want to delete this group?");
	if (r == true) {
		return true;
	} else {
		return false;
	}
}
</script>

<form id="accountForm" class="form-horizontal" role="form" action="" method="post">
	<div class="form-group">
		<label class="col-sm-3 control-label" for="username">Group Name</label>
		<div class="col-sm-6">
			<input class="form-control" required="true" type="text" id="groupName" name="groupName" value="<?php echo($group['groupName']);?>" placeholder="group name">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="domains">Domains<br/>(one domain per line)<a class="glyphicon glyphicon-info-sign" href="#" data-toggle="popover" data-content="Enter root domains only. Subdomains don't matter.  For example put gmail.com - not mail.gmail.com."></a></label>
		<div class="col-sm-6">
			<textarea class="form-control" name="domains" id="domains" rows="5"><?php echo(trim($group['domains']))?></textarea>
		</div>
	</div>
	<div class="form-group">  
		<label class="col-sm-3 control-label" for="ips">IPs<br/>(IPv4 Only, one ip or <a target="_new" href="http://en.wikipedia.org/wiki/Classless_Inter-Domain_Routing">cidr</a> <br/> one per line, max size per line /24)</label>
		<div class="col-sm-6">
			<textarea class="form-control" name="ips" id="ips" rows="5"><?php echo(trim($group['ips']))?></textarea>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-6">
			<button type="submit" name="submit" value="submit" class="btn btn-primary">Save changes</button>
			<?php	if($id>0) { ?>		<button type="submit" onclick="return clickDelete();" name="deleteGroup" value="deleteGroup" class="btn btn-danger">Delete Group</button><?php } ?>
		</div>
	</div>
	<input type="hidden" value="<?php echo($id);?>" name="id"/>
</form>

<br/><br/><br/><br/>

<?php include('footer.inc.php'); ?>
