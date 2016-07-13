<?php
class_exists('Setup', false) or include('classes/Setup.class.php');
class_exists('Utilities', false) or include('classes/Utilities.class.php');
class_exists('_MySQL', false) or include('classes/_MySQL.class.php');

if(Utilities::isLoggedIn()===false){
	header('Location: login.php?location='.urlencode('hosts.php'));
	exit();
}
$host = array_key_exists('host', $_POST) ? $_POST['host'] : '';
$toggle = array_key_exists('toggle', $_POST) ? (int)$_POST['toggle'] : 0;

$titlePreFix = "Block Lists";

$user = Utilities::getAccount();
$mysql = new _MySQL();
$mysql->connect(Setup::$connectionArray);
if($host != ''){
	if($toggle==0){
		$mysql->runQuery("
			update blockLists
			set isActive = '0'
			where md5(host) = '".$mysql->escape($host)."'");
	}else{
		$mysql->runQuery("
			update blockLists
			set isActive = '1'
			where md5(host) = '".$mysql->escape($host)."'");
	}
	exit();
}

$sql = "
select *
from blockLists
order by isActive desc, blocksToday desc
";
$rs = $mysql->runQuery($sql);

include('header.inc.php');
include('accountSubnav.inc.php');
?>

<script src="js/jquery.tablesorter.min.js"></script>

<script>
$(document).ready(function() {
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
	$.post("blockLists.php", {host: host, toggle: status} )
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

<div class="table-responsive">
	<table id="blockListTable" class="tablesorter table table-bordered table-striped">
		<thead>
			<tr>
				<th>Status</th>
				<th>Blacklist</th>
				<th>Type</th>
				<th>Description</th>
				<th>Importance</th>
				<th>Blocks Today</th>
				<th>Clean Today</th>
				<th>Blocks Yest</th>
				<th>Clean Yest</th>
			</tr>
		</thead>
		<tbody>
		<?php
		while($row = mysqli_fetch_array($rs)){
			echo('<tr>');
			echo('<td style="text-align: center;">');
			if($row['isActive']==0){
				echo('<a data-blstatus="0" data-host="'.md5($row['host']).'" id="'.md5($row['host']).'" class="blockListLinks glyphicon glyphicon-remove" href="#"></a></td>');
			}else{
				echo('<a data-blstatus="1" data-host="'.md5($row['host']).'" id="'.md5($row['host']).'" class="blockListLinks glyphicon glyphicon-ok" href="#"></a></td>');
			}
			echo('<td style="white-space: nowrap"><a target="_blank" href="'.$row['website'].'">'.$row['host'].'</a></td>');
			echo('<td style="white-space: nowrap">'.($row['monitorType']=='ip' ? 'IP' : 'Domain').'</td>');
			echo('<td>'.$row['description'].'</td>');
			echo('<td style="text-align: center;">');
			switch($row['importance']){
				case 3: echo('<span class="label label-primary">High</span>'); break;
				case 2: echo('<span class="label label-info">Medium</span>'); break;
				case 1: echo('<span class="label label-default">Low</span>'); break;
			}
			echo('</td>');
			echo('<td style="white-space: nowrap">'.number_format($row['blocksToday'],0).'</td>');
			echo('<td style="white-space: nowrap">'.number_format($row['cleanToday'],0).'</td>');
			echo('<td style="white-space: nowrap">'.number_format($row['blocksYesterday'],0).'</td>');
			echo('<td style="white-space: nowrap">'.number_format($row['cleanYesterday'],0).'</td>');
			echo('</tr>');
		}
		$mysql->close();
		?>
		</tbody>
	</table>
</div>

<?php include('footer.inc.php'); ?>
