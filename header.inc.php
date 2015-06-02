<?php
class_exists('Utilities', false) or include('classes/Utilities.class.php');
?><!DOCTYPE html>
<html lang="en">
	<head>
	<meta charset="utf-8">
	<title><?php if(isset($titlePreFix)) echo("$titlePreFix | ");?>Open Source Blacklist Monitoring</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
	<script type="text/javascript" src="//www.google.com/jsapi"></script>
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootswatch/3.3.4/yeti/bootstrap.min.css" type="text/css" media="all" />
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="css/site.css" />
	<link rel="SHORTCUT ICON" href="favicon.ico" />
	<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<script src="//oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="//oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
	<![endif]-->
	</head>
	<body>
	<div class="container">
		<div class="header">
			<ul class="nav nav-pills pull-right">
			<?php $pageName = basename($_SERVER['PHP_SELF']);?>
				<li <?php if($pageName=='index.php') echo('class="active"');?>><a href="/">Lookup</a></li>
			<?php
			if(Utilities::isLoggedIn()!==false){
			?>
				<li <?php if(in_array($pageName,array('account.php','hosts.php','blockLists.php','apiDocumentation.php','hostHistory.php'))) echo('class="active"');?>><a href="account.php">Account</a></li>
				<li><a href="login.php?logout=1">Logout</a></li>
			<?php
			}else{
			?>
				<li <?php if($pageName=='login.php') echo('class="active"');?>><a href="login.php">Login</a><li>
			<?php
			}
			?>
			</ul>
			<a href="/"><img src="img/logo.png" alt="logo"></a><br>
		</div>