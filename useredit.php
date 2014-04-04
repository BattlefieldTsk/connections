<?php
/**
 * UserEdit.php
 *
 * This page is for users to edit their account information
 * such as their password, email address, etc. Their
 * usernames can not be edited. When changing their
 * password, they must first confirm their current password.
 *
 * Written by: Jpmaster77 a.k.a. The Grandmaster of C++ (GMC)
 * Last Updated: August 2, 2009 by Ivan Novak
 */
include("include/session.php");
$page = "useredit.php";
if(!$session->logged_in){
   header("Location: main.php");
}
else{
?>

<html>
   <head>
   	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
   	<title>Player Analytics - User Info</title>
      <!-- Bootstrap core CSS -->
      <link href="css/bootstrap.min.css" rel="stylesheet">
      <!-- Add custom CSS here -->
      <link href="css/player_analytics.css" rel="stylesheet">
      <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet">
   </head>
   <body>
      <?php require("include/nav.php") ?>
      <div class="main">
        <div class="row">
          <div class="col-lg-12">
            <h1>Player Analytics <small>User Info</small></h1>
            <ol class="breadcrumb">
              <li><a href="index.php">Dashboard</a></li>
              <li><a href="userinfo.php?user=<?php echo $session->username; ?>"><?php echo $session->username; ?></a></li>
              <li class="active">Edit Info</li>
            </ol>
          </div>
        </div><!-- /.row -->
        <div class="well col-lg-6">
<?php
/**
 * User has submitted form without errors and user's
 * account has been edited successfully.
 */
if(isset($_SESSION['useredit'])){
   unset($_SESSION['useredit']);
   
   echo "<h1>User Account Edit Success!</h1>";
   echo "<p><b>$session->username</b>, your account has been successfully updated. "
       ."<a href=\"userinfo.php\">Profile</a>.</p>";
}
else{
?>

<h1>Edit : <?php echo $session->username; ?></h1>
<?php
if($form->num_errors > 0){
   echo "<td><font size=\"2\" color=\"#ff0000\">".$form->num_errors." error(s) found</font></td>";
}
?>
<div id="userupdate">
	<form action="include/process.php" method="POST">
		<div class="form-group">
    		<label for="name">Name</label>
    		<input type="text" class="form-control" id="name" name="name" value="<?php
				if($form->value("name") == ""){
					echo $session->userinfo['name'];
				}else{
					echo $form->value("name");
				}
				?>"
			>
  		</div>
		  <div class="form-group">
    		<label for="curpass">Current Password</label>
    		<input type="text" class="form-control" id="curpass" name="curpass" value="<?php echo $form->value("curpass"); ?>">
  			<?php echo $form->error("curpass"); ?>
  		</div>
  		<div class="form-group">
    		<label for="newpass">New Password</label>
    		<input type="text" class="form-control" id="newpass" name="newpass" value="<?php echo $form->value("newpass"); ?>">
  			<?php echo $form->error("newpass"); ?>
  		</div>
  		<div class="form-group">
    		<label for="email">Email</label>
    		<input type="text" class="form-control" id="email" name="email" value="<?php
				if($form->value("email") == ""){
					echo $session->userinfo['email'];
				}else{
					echo $form->value("email");
				}
				?>"
			>
  			<?php echo $form->error("email"); ?>
  		</div>
			<input type="hidden" name="subedit" value="1" />
			<button class="btn btn-md btn-primary btn-block" type="submit">Edit Account</button>
	</form>
</div>
		</div>
		<!-- Bootstrap core JavaScript -->
	    <script src="js/jquery/jquery-1.11.0.min.js"></script>
	    <script src="js/bootstrap/bootstrap.min.js"></script>
	</body>
</html>
<?php
}
}
?>