<?php
class_exists('Setup', false) or include('classes/Setup.class.php');
class_exists('Utilities', false) or include('classes/Utilities.class.php');
class_exists('_MySQL', false) or include('classes/_MySQL.class.php');
class_exists('PHPMailer', false) or include('classes/class.phpmailer.php');

$titlePreFix = "login";

$username = array_key_exists('username', $_POST) ? substr($_POST['username'],0,99) : '';
$location = array_key_exists('location', $_REQUEST) ? $_REQUEST['location'] : 'account.php';
$passwd = array_key_exists('passwd', $_POST) ? $_POST['passwd'] : '';
$logout = array_key_exists('logout', $_GET) ? (int)$_GET['logout'] : 0;
$message = '';

if($logout==1){
	session_destroy();
	echo("<script>window.location='/';</script>");
	exit();
}

if(isset($_POST["submit"])){
	$id = Utilities::validateLogin($username, $passwd);
	if($id != 0){
		$_SESSION['id'] = $id;
		?>
		<script>window.location='<?php echo($location);?>';</script>
		<?php
		exit();
	}else{
		$message .= 'Invalid login.<br/>';
	}
}?>
<?php include('header.inc.php'); ?>
<?php
if($message!='') echo("<div class=\"message\">$message</div>");
?>

<form action="" method="post" class="form-signin">
	<h2 class="form-signin-heading">Please sign in</h2>
	<input type="text" class="form-control" name="username" value="<?php echo($username);?>" placeholder="username">
	<input type="password" name="passwd" value="<?php echo($passwd);?>" class="form-control" placeholder="Password">
	<input type="hidden" name="location" value="<?php echo($location);?>">
	<button class="btn btn-lg btn-primary" name="submit" value="login" type="submit">Sign in</button>
</form>
<?php include('footer.inc.php'); ?>
