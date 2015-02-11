<?php
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

require('inc/includes/password.php');

// Install file check
clearstatcache();
if(file_exists("pages/install.php")){
	unlink("pages/install.php");
}

// Check for version
$last_checked = $queries->getWhere("settings", array("name", "=", "version_checked"));
$last_checked = $last_checked[0]->value;

$need_update = "false";

if($last_checked < strtotime('-1 day', $last_checked)){
	$uid = $queries->getWhere("settings", array("name", "=", "unique_id"));
	$uid = htmlspecialchars($uid[0]->value);

	$version = $queries->getWhere("settings", array("name", "=", "version"));
	$version = htmlspecialchars($version[0]->value);

	$latest_version = file_get_contents("https://worldscapemc.co.uk/nl_core/stats.php?uid=" . $uid . "&version=" . $version);
	if($latest_version !== "failed"){
		if($version < $latest_version){
			// Need to update!
			$queries->update("settings", 32, array(
				"value" => htmlspecialchars($latest_version)
			));
			$need_update = htmlspecialchars($latest_version);
		}
	}
	
	// Get current unix time
	$date = new DateTime();
	$date = $date->getTimestamp();
	
	$queries->update("settings", 31, array(
		"value" => $date
	));

	$uid = null;
	$version = null;
	$date = null;
	$latest_version = null;
}
$last_checked = null;

if($user->isAdmLoggedIn()){
	// Is authenticated
	if($user->data()->group_id != 2){
		Redirect::to('/');
		die();
	}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Samerton">
    <link rel="icon" href="/assets/favicon.ico">

	<?php 
	$sitename = $queries->getWhere("settings", array("name", "=", "sitename"));
	$sitename = htmlspecialchars($sitename[0]->value);
	?>
    <title><?php echo $sitename; ?> &bull; Admin</title>
	
	<?php require('inc/templates/header.php'); ?>
	
	<!-- Custom style -->
	<style>
	html {
		overflow-y: scroll;
	}
	</style>
	
  </head>
  <body>
    <?php require('inc/templates/navbar.php'); ?>
	<div class="container">
		<div class="row">
			<?php
			if($need_update !== "false"){
			?>
			<div class="alert alert-warning">
			A new update is available. Latest version: <?php echo htmlspecialchars($need_update); ?><br />
			Download from <a class="white-text" style="text-decoration: underline;" href="https://github.com/samerton/NamelessMC/archive/master.zip" target="_blank">GitHub</a><br />
			<a class="white-text" style="text-decoration: underline;" href="/admin/update">Update guide</a>
			</div>
			<?php
			}
			?>
		</div>
	</div>
  </body>
</html>
<?php
} else {
	// Isn't authenticated
	if(Input::exists()) {
		if(Token::check(Input::get('token'))) {
			$validate = new Validate();
			$validation = $validate->check($_POST, array(
				'username' => array('required' => true, 'isbanned' => true, 'isactive' => true),
				'password' => array('required' => true)
			));
			
			if($validation->passed()) {
				$user = new User();

				$login = $user->adminLogin(Input::get('username'), Input::get('password'));
				
				if($login) {
					Redirect::to("/admin");
					die();
				} else {
					Session::flash('adm_auth_error', '<div class="alert alert-danger">Incorrect details</div>');
				}
			} else {
				Session::flash('adm_auth_error', '<div class="alert alert-danger">Incorrect details</div>');
			}
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Samerton">
    <link rel="icon" href="/assets/favicon.ico">

	<?php 
	$sitename = $queries->getWhere("settings", array("name", "=", "sitename"));
	$sitename = htmlspecialchars($sitename[0]->value);
	?>
    <title><?php echo $sitename; ?> &bull; Admin</title>
	
	<?php require('inc/templates/header.php'); ?>
	
	<!-- Custom style -->
	<style>
	html {
		overflow-y: scroll;
	}
	</style>
	
  </head>
  <body>
	<div class="container">
		<div class="row">
			<br /><br />
			<div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
			<?php
			if(Session::exists('adm_auth_error')){
				echo Session::flash('adm_auth_error');
			}
			?>
				<form role="form" action="" method="post">
					<center><h2>Please re-authenticate</h2></center>
					<div class="form-group">
						<input type="text" name="username" id="username" autocomplete="off" value="<?php echo htmlspecialchars(Input::get('username')); ?>" class="form-control" placeholder="Username" tabindex="3">
					</div>
					<div class="form-group">
						<input type="password" name="password" id="password" class="form-control" placeholder="Password" tabindex="4">
					</div>
					<div class="row">
						<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
						<center>
						<input type="submit" value="Sign In" class="btn btn-primary btn-lg" tabindex="5">
						<a href="/" class="btn btn-danger btn-lg">Back</a>
						</center>
					</div>
				</form>
			</div>
		</div>
	</div>
  </body>
</html>
<?php
}
?>