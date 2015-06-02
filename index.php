<?php
class_exists('Setup', false) or include('classes/Setup.class.php');
class_exists('Utilities', false) or include('classes/Utilities.class.php');

$domainOrIp = array_key_exists('h', $_GET) ? trim($_GET['h']) : '';

include('header.inc.php');
?>
<br><br><br><br>
<div class="row">
	<div class="col-lg-12">
		<form role="form">
			<div class="input-group">
				<input placeholder="domain or ip" type="text" name="h" value="<?php echo($domainOrIp);?>" class="form-control">
				<span class="input-group-btn">
					<button type="submit" class="btn btn-success">Check Now</button>
				</span>
			</div>
		</form>
	</div>
</div>

<?php if($domainOrIp != '') {
	$results = Utilities::checkBlacklists($domainOrIp,true);
?>
	<div class="table-responsive">
		<table class="tablesorter table table-bordered table-striped">
			<thead>
				<tr>
					<th abbr="Blacklist">Blacklist</th>
					<th abbr="Result">Result</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($results as $r){ ?>
					<tr>
						<th><?php echo($r[0]);?></th>
						<td><?php 
						if($r[1] == false || $r[1] == ''){
							echo("CLEAN");
						}else{
							echo($r[1]);
						}
						?></td>
					</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
<?php } ?>

<?php
include('footer.inc.php');
?>
