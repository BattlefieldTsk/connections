<?php
/**
 * UserInfo.php
 *
 * This page is for users to view their account information
 * with a link added for them to edit the information.
 *
 * Written by: Jpmaster77 a.k.a. The Grandmaster of C++ (GMC)
 * Last Updated: August 2, 2009 by Ivan Novak
 */
include("include/session.php");
$page = "userinfo.php";
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
              <li class="active">User Info</li>
            </ol>
          </div>
        </div><!-- /.row -->
        <div class="well col-lg-6">
<?php
/* Requested Username error checking */
$req_user = trim($_GET['user']);
if(!$req_user || strlen($req_user) == 0 ||
   !preg_match("/^([0-9a-z])+$/i", $req_user) ||
   !$database->usernameTaken($req_user)){
   die("Username not registered");
}

/* Logged in user viewing own account */
if(strcmp($session->username,$req_user) == 0){
   echo "<h1>My Account</h1>";
}
/* Visitor not viewing own account */
else{
   echo "<h1>User Info</h1>";
}

/* Display requested user information */
$req_user_info = $database->getUserInfo($req_user);

/* Name */
echo "<p><b>Name: ".$req_user_info['name']."</b><br />";

/* Username */
echo "<p><b>Username: ".$req_user_info['username']."</b><br />";

/* Email */
echo "<b>Email:</b> ".$req_user_info['email']."</p>";

/**
 * Note: when you add your own fields to the users table
 * to hold more information, like homepage, location, etc.
 * they can be easily accessed by the user info array.
 *
 * $session->user_info['location']; (for logged in users)
 *
 * ..and for this page,
 *
 * $req_user_info['location']; (for any user)
 */

/* If logged in user viewing own account, give link to edit */
if(strcmp($session->username,$req_user) == 0){
   echo "<a href=\"useredit.php\"><button type=\"button\" class=\"btn btn-primary\">Edit Account Information</button></a>";
}
?>
         </div>
      </div>
       <!-- Bootstrap core JavaScript -->
       <script src="js/jquery/jquery-1.11.0.min.js"></script>
       <script src="js/bootstrap/bootstrap.min.js"></script>
   </body>
</html>
<?php
}
?>