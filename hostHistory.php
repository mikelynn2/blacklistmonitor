<?php
class_exists('Setup', false) or include('classes/Setup.class.php');
class_exists('Utilities', false) or include('classes/Utilities.class.php');
class_exists('_MySQL', false) or include('classes/_MySQL.class.php');

$host = array_key_exists('host', $_GET) ? trim($_GET['host']) : '';

if(Utilities::isLoggedIn()===false){
	header('Location: login.php?location='.urlencode('hosts.php'));
	exit();
}

$titlePreFix = "history | $host";

$user = Utilities::getAccount();
$mysql = new _MySQL();
$mysql->connect(Setup::$connectionArray);

$daysOfHistory = Setup::$settings['history_keep_days'];
$cutoffDate = date('Y-m-d', strtotime("-$daysOfHistory days"));

$sql = "
select isBlocked,monitorTime,rDNS,status
from monitorHistory
where ipDomain = '".$mysql->escape($host)."'
	and monitorTime >= '".$mysql->escape($cutoffDate)."'
order by monitorTime desc
";
$rs = $mysql->runQuery($sql);
?>

<?php include('header.inc.php'); ?>
<?php include('accountSubnav.inc.php'); ?>
<script src="js/jquery.tablesorter.min.js"></script>
<script>
$(document).ready(function() { 
	$("#hostTable").tablesorter(); 
	} 
);
</script>

<script type="text/javascript">
	google.load("visualization", "1", {packages:["corechart"]});
	google.setOnLoadCallback(drawChart);
	function drawChart() {
		var data = google.visualization.arrayToDataTable([
		<?php
		$data = array();
		$chartData = '';
		while($row = mysqli_fetch_array($rs, MYSQL_ASSOC)){
			$data[] = $row;
			$t = date("Y-n-j g a",strtotime($row['monitorTime']));
			$chartData .= "['$t', ";
			if($row['isBlocked']==1){
				$c = count(unserialize($row['status']));
			}else{
				$c = 0;
			}
			$chartData .="$c],";
		}
		$mysql->close();
		if($chartData==''){
			$chartData = "['', { role: 'annotation' }],['', '']";
		}else{
			$chartData = "['Day', 'Blocks'],$chartData";
		}
		echo($chartData);
		?>
		]);
		var options = {
		title: '<?php echo($host);?>',
		hAxis: {direction:-1, title: 'Day', titleTextStyle: {color: '#333'}},
		vAxis: {minValue: 0 , viewWindow: {min:0}},
		pointSize: 3,
		curveType: "function",
		series:{
			0:{targetAxisIndex:0}
		}
		};
		var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
		chart.draw(data, options);
	}
</script>

<div id="chart_div" style="width: 100%; height: 170px; margin-bottom:5px;"></div>

<div><small><em><?php echo($daysOfHistory);?> days of data.</em></small></div>

<div class="table-responsive">
	<table id="hostTable" class="tablesorter table table-bordered table-striped">
		<thead>
			<tr>
				<th>Date</th>
				<th>DNS</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach($data as $row){
			echo('<tr>');
			echo('<td nowrap>'.date("Y-m-d h:i a",strtotime($row['monitorTime'])).'</td>');
			echo('<td>'.$row['rDNS'].'</td>');
			echo('<td>');
			if($row['isBlocked']==1){
				$s = unserialize($row['status']);
				foreach($s as $r){
					if(isset($r[0])) echo htmlentities($r[0]);
					if(isset($r[1])) echo ' - '. htmlentities($r[1]);
					echo "<br/>";
				}
			}else{
				echo('OK');
			}
			echo('</td>');
			echo('</tr>');
		}
		?>
		</tbody>
	</table>
</div>

<?php include('footer.inc.php'); ?>
