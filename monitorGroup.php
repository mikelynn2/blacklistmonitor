<?php
class_exists('Setup', false) or include('classes/Setup.class.php');
class_exists('Utilities', false) or include('classes/Utilities.class.php');
class_exists('_MySQL', false) or include('classes/_MySQL.class.php');

$groupName = array_key_exists('searchS', $_GET) ? trim($_GET['searchS']) : '';

if(Utilities::isLoggedIn()===false){
	header('Location: login.php?location='.urlencode('monitorGroup.php'));
	exit();
}

$titlePreFix = "Monitor Groups";

$user = Utilities::getAccount();
$mysql = new _MySQL();
$mysql->connect(Setup::$connectionArray);
$sql = "
select g.*,
	(select count(*) from monitors where g.id = monitorGroupId) as hostCount,
	(select count(*) from monitors where isBlocked = 1 and g.id = monitorGroupId) as hostCountError
from monitorGroup g
order by g.groupName
";
$rs = $mysql->runQuery($sql);

include('header.inc.php');
include('accountSubnav.inc.php');

?>

<script src="js/jquery.tablesorter.min.js"></script>

<script>
$(document).ready(function() { 
	$("#hostGroupTable").tablesorter();
});
</script>

<div>
<ul class="nav nav-pills">
	<li role="presentation"><a href="editHostGroup.php">New Group</a></li>
	<li role="presentation"><a href="hosts.php">All Host Stats</a></li>
</ul>
</div>
<div class="table-responsive">
	<table id="hostGroupTable" class="tablesorter table table-bordered table-striped">
		<thead>
			<tr>
				<th style="white-space: nowrap">Monitor Group</th>
				<th style="white-space: nowrap">Total Hosts</th>
				<th style="white-space: nowrap">Total Blocks</th>
			</tr>
		</thead>
		<tbody>
		<?php
		while($row = mysqli_fetch_array($rs, MYSQL_ASSOC)){
			echo('<tr>');
			echo('<td><a href="hosts.php?monitorGroupId='.$row['id'].'"><div class="glyphicon glyphicon-stats glyphicon-stats-lg"></div></a> &nbsp; <a href="editHostGroup.php?id='.urlencode($row['id']).'">'.$row['groupName'].'</a></td>');
			echo('<td>'.$row['hostCount'].'</td>');
			echo('<td>'.$row['hostCountError'].'</td>');
			echo('</tr>');
		}
		$mysql->close();
		?>
		</tbody>
	</table>
</div>

<?php include('footer.inc.php'); ?>
