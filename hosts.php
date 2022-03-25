<?php
class_exists('Setup', false) or include('classes/Setup.class.php');
class_exists('Utilities', false) or include('classes/Utilities.class.php');
class_exists('_MySQL', false) or include('classes/_MySQL.class.php');

$searchS = array_key_exists('searchS', $_GET) ? trim($_GET['searchS']) : '';
$oc = array_key_exists('oc', $_GET) ? (int)$_GET['oc'] : 4;
$hostType = array_key_exists('ht', $_GET) ? $_GET['ht'] : 'all';
$monitorGroupId = array_key_exists('monitorGroupId', $_GET) ? (int)$_GET['monitorGroupId'] : 0;
$limit = array_key_exists('l', $_GET) ? (int)$_GET['l'] : 100;

if(Utilities::isLoggedIn()===false){
	header('Location: login.php?location='.urlencode('hosts.php'));
	exit();
}


#Function to Enable/Disable IP as well
$ipDomain = array_key_exists('host', $_POST) ? $_POST['host'] : '';
$toggle = array_key_exists('toggle', $_POST) ? (int)$_POST['toggle'] : 0;
#

$titlePreFix = "Hosts";

$user = Utilities::getAccount();
$mysql = new _MySQL();
$mysql->connect(Setup::$connectionArray);
$searchSQL = "";
$hostTypeSQL = "";
$orderSQL = " order by ";
$limitSQL = ($limit > 0) ? " limit $limit " : '';
switch($oc){
	case 1:
		$searchSQL .= " and lastStatusChanged = 1 ";
		$orderSQL .= " lastStatusChangeTime desc ";
		break;
	case 2:
		$searchSQL .= " and isBlocked = 1 ";
		$orderSQL .= " lastStatusChangeTime desc ";
		break;
	case 3: 
		$searchSQL .= " and isBlocked = 0 ";
		$orderSQL .= " lastStatusChangeTime desc ";
		break;
	default:
		$searchSQL .= " ";
		$orderSQL .= " lastStatusChangeTime desc ";
		break;
}

if($monitorGroupId != 0) $searchSQL .= " and monitorGroupId = $monitorGroupId ";

switch($hostType){
	case 'domains':
		$hostTypeSQL .= " and isDomain = 1 ";
		break;
	case 'ips':
		$hostTypeSQL .= " and isDomain = 0 ";
		break;
}

if($searchS != ''){
	$searchSQL .= " and (
		ipDomain like '%".$mysql->escape($searchS)."%' 
		or rDNS like '%".$mysql->escape($searchS)."%'
		or status like '%".$mysql->escape($searchS)."%' ) "; 
}

if($ipDomain != ''){
	if($toggle==0){
		$mysql->runQuery("
			update monitors
			set isActive = '0'
			where md5(ipDomain) = '".$mysql->escape($ipDomain)."'");
	}else{
		$mysql->runQuery("
			update monitors
			set isActive = '1'
			where md5(ipDomain) = '".$mysql->escape($ipDomain)."'");
	}
	exit();
}

$sql = "
select m.isBlocked, m.lastUpdate, m.ipDomain, m.lastStatusChangeTime, m.rDNS, m.status, m.isActive, g.groupName, g.id
from monitors m 
	inner join monitorGroup g on g.id = m.monitorGroupId
where 1=1 $hostTypeSQL $searchSQL
$orderSQL
$limitSQL
";


$rs = $mysql->runQuery($sql);

include('header.inc.php');
include('accountSubnav.inc.php');


$hostsCount = Utilities::getHostCount($mysql, $monitorGroupId);
$hostsCountError = Utilities::getHostErrorCount($mysql, $monitorGroupId);
?>

<script src="js/jquery.tablesorter.min.js"></script>

<script>
$(document).ready(function() { 
	$("#hostTable").tablesorter();
	$(".reportType").change(function() {
		$("#reportForm").submit();
	});
	$(".recentFilter").change(function() {
		$("#reportForm").submit();
	});
	$(".hostType").change(function() {
		$("#reportForm").submit();
	});
		$("#blockListTable").tablesorter();
	$(".blockListLinks").click( function(event) {
		var host = $("#"+event.target.id).data("host");
		toggleBlacklist(host);
		return false;
	});
});

function toggleBlacklist(host){
	var status = $("#"+host).data("blstatus");
	if(status == 1) {
		status = 0;
	}else{
		status = 1;
	}
	$.post("hosts.php", {host: host, toggle: status} )
		.done(function( data ) {
			if(status==1){
				$("#"+host).removeClass('glyphicon-remove');
				$("#"+host).addClass('glyphicon-ok');
			}else{
				$("#"+host).removeClass('glyphicon-ok');
				$("#"+host).addClass('glyphicon-remove');
			}
			$("#"+host).data("blstatus", status);
		});
}
</script>

<div class="panel panel-default">
	<div class="panel-body">
		<a class="glyphicon glyphicon-ok"></a> - Enabled<br>
		<a class="glyphicon glyphicon-remove"></a> - Disabled<br>
	</div>
</div>

<script type="text/javascript">
	google.load("visualization", "1", {packages:["corechart"]});
	google.setOnLoadCallback(drawChart);
	function drawChart() {
	var data = google.visualization.arrayToDataTable([
		['Hosts', 'Status'],
		['Blocked',<?php echo($hostsCountError);?>],
		['Clean',<?php echo($hostsCount-$hostsCountError);?>]
	]);
	data.setFormattedValue(0, 0, data.getValue(0, 0) + ' ' + (<?php echo($hostsCountError);?>));
	data.setFormattedValue(1, 0, data.getValue(1, 0) + ' ' + (<?php echo($hostsCount-$hostsCountError);?>));
	var options = {
		title: 'Current Network Status',
		is3D: true,
		sliceVisibilityThreshold:0,
		slices: {
			0: { color: 'red' },
			1: { color: 'blue' }
		}
	};
	var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
	chart.draw(data, options);
	}
</script>

<div id="piechart_3d" style="width: 100%; height: 90px;"></div>

<div style="margin-bottom:5px;">
	<form class="form-inline" id="reportForm" role="form">
		<label class="radio-inline">
			<input class="reportType" type="radio" name="oc" value="1"<?php if($oc==1) echo(' checked');?>>Last Status Changed
		</label>
		<label class="radio-inline">
			<input class="reportType" type="radio" name="oc" value="2"<?php if($oc==2) echo(' checked');?>>Blocked
		</label>
		<label class="radio-inline">
			<input class="reportType" type="radio" name="oc" value="3"<?php if($oc==3) echo(' checked');?>>Clean
		</label>
		<label class="radio-inline">
			<input class="reportType" type="radio" name="oc" value="4"<?php if($oc==4) echo(' checked');?>>All
		</label>
		<div class="form-group">
			<div class="col-md-6">
				<select id="l" name="l" class="form-control recentFilter input-sm">
					<option value="0"<?php if($limit==0) echo(' selected');?>>all</option>
					<option value="20"<?php if($limit==20) echo(' selected');?>>20 most recent</option>
					<option value="100"<?php if($limit==100) echo(' selected');?>>100 most recent</option>
					<option value="500"<?php if($limit==500) echo(' selected');?>>500 most recent</option>
				</select>
			</div>
		</div>
		<div class="form-group">
			<div class="col-md-6">
				<select id="ht" name="ht" class="form-control hostType input-sm">
					<option value="all"<?php if($hostType=='all') echo(' selected');?>>all</option>
					<option value="ips"<?php if($hostType=='ips') echo(' selected');?>>ips</option>
					<option value="domain"<?php if($hostType=='domains') echo(' selected');?>>domains</option>
				</select>
			</div>
		</div>
		<div class="form-group">
			<label class="sr-only" for="searchS">Search</label>
			<input type="text" class="form-control input-sm" id="searchS" name="searchS" placeholder="search" value="<?php echo($searchS);?>">
		</div>
		<button type="submit" class="btn btn-default">Go</button>
		<input type="hidden" name="monitorGroupId" value="<?php echo($monitorGroupId); ?>">
	</form>
</div>

<div class="table-responsive">
	<table id="hostTable" class="tablesorter table table-bordered table-striped">
		<thead>
			<tr>
			    <th style="white-space: nowrap">Status</th>
				<th style="white-space: nowrap">Host</th>
				<th>Group</th>
				<th style="white-space: nowrap">Last Checked</th>
				<th style="white-space: nowrap">Last Change</th>
				<th>DNS</th>
				<th>Current Status</th>
			</tr>
		</thead>
		<tbody>
		<?php
		while($row = mysqli_fetch_array($rs)){
			echo('<tr>');
			echo('<td style="text-align: center;">');
			if($row['isActive']==0){
				echo('<a data-blstatus="0" data-host="'.md5($row['ipDomain']).'" id="'.md5($row['ipDomain']).'" class="blockListLinks glyphicon glyphicon-remove" href="#"></a></td>');
			}else{
				echo('<a data-blstatus="1" data-host="'.md5($row['ipDomain']).'" id="'.md5($row['ipDomain']).'" class="blockListLinks glyphicon glyphicon-ok" href="#"></a></td>');
			}
			
			echo('<td><a href="hostHistory.php?host='.urlencode($row['ipDomain']).'">'.$row['ipDomain'].'</a></td>');
			echo('<td><a href="editHostGroup.php?id='.$row['id'].'">'.$row['groupName'].'</a></td>');
			if('0000-00-00 00:00:00'==$row['lastUpdate']){
				echo('<td style="white-space: nowrap">'.Utilities::$hostNotCheckedMessage.'</td>');
				echo('<td style="white-space: nowrap">'.Utilities::$hostNotCheckedMessage.'</td>');
				echo('<td style="white-space: nowrap">'.Utilities::$hostNotCheckedMessage.'</td>');
				echo('<td style="white-space: nowrap">'.Utilities::$hostNotCheckedMessage.'</td>');
			}else{
				echo('<td style="white-space: nowrap">');
				echo(date("Y-n-j g:i a",strtotime($row['lastUpdate'])));
				echo('</td>');
				if('0000-00-00 00:00:00'==$row['lastStatusChangeTime']){
					echo('<td style="white-space: nowrap">n/a</td>');
				}else{
					echo('<td style="white-space: nowrap">');
					echo(date("Y-n-j g:i a",strtotime($row['lastStatusChangeTime'])));
					echo('</td>');
				}
				echo('<td>'.$row['rDNS'].'</td>');
				echo('<td>');
				if($row['isBlocked']==1){
					$s = unserialize($row['status']);
					foreach($s as $r){
						if($r[1] == false || $r[1] == ''){
						}else{
							echo htmlentities($r[0]) . " - " . htmlentities($r[1])."<br>\n";
						}
					}
				}else{
					echo('OK');
				}
				echo('</td>');
			}
			echo('</tr>');
		}
		$mysql->close();
		?>
		</tbody>
	</table>
</div>

<?php include('footer.inc.php'); ?>
