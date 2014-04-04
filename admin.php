<?php
/**
 * Admin.php
 *
 * This is the Admin Center page. Only administrators
 * are allowed to view this page. This page displays the
 * database table of users and banned users. Admins can
 * choose to delete specific users, delete inactive users,
 * ban users, update user levels, etc.
 *
 * Written by: Jpmaster77 a.k.a. The Grandmaster of C++ (GMC)
 * Last Updated: August 2, 2009 by Ivan Novak
 */
include("include/session.php");
if(!$session->isAdmin()){
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
               <h1>Player Analytics <small>Logged in as <?php echo $session->username; ?></small></h1>
               <ol class="breadcrumb">
                 <li><a href="index.php">Dashboard</a></li>
                 <li class="active">Admin Panel</li>
               </ol>
             </div>
           </div><!-- /.row -->
           <div class="row">
            <div class="well col-lg-6" style="margin-left:15px">
         <?php
            if($form->num_errors > 0){
               echo "<font size=\"4\" color=\"#ff0000\">"
                   ."!*** Error with request, please fix</font><br><br>";
            }
         ?>
         <?php
         /**
          * displayUsers - Displays the users database table in
          * a nicely formatted html table.
          */
            function displayUsers(){
               global $database;
               $q = "SELECT username,userlevel,email,timestamp "
                   ."FROM ".TBL_USERS." ORDER BY userlevel DESC,username";
               $result = $database->query($q);
               /* Error occurred, return given name by default */
               $num_rows = mysql_numrows($result);
               if(!$result || ($num_rows < 0)){
                  echo "Error displaying info";
                  return;
               }
               if($num_rows == 0){
                  echo "Database table empty";
                  return;
               }
               /* Display table contents */
               echo "<table id='display' class='table table-striped table-condensed'>";
               echo "<tr><td>Username</td><td>Level</td><td>Email</td><td>Last Active</td></tr>";
               for($i=0; $i<$num_rows; $i++){
                  $uname  = mysql_result($result,$i,"username");
                  $ulevel = mysql_result($result,$i,"userlevel");
                  $email  = mysql_result($result,$i,"email");
                  $time   = mysql_result($result,$i,"timestamp");

                  echo "<tr><td>".$uname."</td><td>".$ulevel."</td><td>".$email."</td><td>".$time."</td></tr>";
               }
               echo "</table>";
            }
         ?>
               <div class="col-sm-12">
                  <h3>Add User</h3>
                  <?php echo $form->error("adduser"); ?>
                  <form action="include/process.php" method="POST">
                     <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" name="name" maxlength="30" value="<?php echo $form->value("name"); ?>">
                     </div>
                     <div class="form-group">
                        <label for="user">Username</label>
                        <input type="text" class="form-control" name="user" maxlength="30" value="<?php echo $form->value("user"); ?>">
                     </div>
                     <div class="form-group">
                        <label for="pass">Password</label>
                        <input type="text" class="form-control" name="pass" maxlength="30" value="<?php echo $form->value("pass"); ?>">
                     </div>
                     <div class="form-group">
                        <label for="email">Email</label>
                        <input type="text" class="form-control" name="email" maxlength="30" value="<?php echo $form->value("email"); ?>">
                     </div>
                     <input type="hidden" name="subjoin" value="1">
                     <button class="btn btn-md btn-primary btn-block" type="submit">Add User</button>
                  </form>
               </div>
               <div class="col-sm-12">
               	<h3>Update User Level</h3>
               	<?php echo $form->error("upduser"); ?>
               	<form action="include/adminprocess.php" method="POST">
                     <div class="form-group">
                        <div class="col-sm-6">
                          <label for="upduser">Username</label>
               		     <input type="text" class="form-control" name="upduser" maxlength="30" value="<?php echo $form->value("upduser"); ?>">
               		  </div>
                     </div>
                     <div class="form-group">
                       <div class="col-sm-2">
                         <label for="updlevel">Level</label>	
                          <select name="updlevel" class="form-control" style="padding-left:3px;padding-right:3px">
                   				 <option value="1">1</option>
                   				 <option value="9">9</option>
                   			  </select>
               		     </div>
                     </div>
                     <div class="form-group">
                       <div class="col-sm-4" style="margin-top:25px">
               		   <input type="hidden" name="subupdlevel" value="1">
               		   <button class="btn btn-md btn-primary btn-block" type="submit">Update Level</button>
                       </div>
                     </div>
               	</form>
               </div>
               <div class="col-sm-12">
               	<h3>Delete User</h3>
               	<?php echo $form->error("deluser"); ?>
                  <form action="include/adminprocess.php" method="POST">
                     <div class="form-group">
                        <div class="col-sm-6">
                          <label for="upduser">Username</label>
                          <input type="text" class="form-control" name="deluser" maxlength="30" value="<?php echo $form->value("deluser"); ?>">
                       </div>
                     </div>
                     <div class="form-group">
                       <div class="col-sm-4 col-sm-offset-2" style="margin-top:25px">
                        <input type="hidden" name="subdeluser" value="1">
                        <button class="btn btn-md btn-primary btn-block" type="submit">Delete User</button>
                       </div>
                     </div>
                  </form>
               </div>
               <div class="col-sm-12">
               	<h3>Delete Inactive Users</h3>
               	<form action="include/adminprocess.php" method="POST">
                     <div class="form-group">
                       <div class="col-sm-6">
                         <label for="inactdays">Days</label>
                           <select name="inactdays" class="form-control">
            						<option value="3">3</option>
            						<option value="7">7</option>
            						<option value="14">14</option>
            						<option value="30">30</option>
            						<option value="100">100</option>
            						<option value="365">365</option>
            					</select>
                        </div>
                     </div>
                     <div class="form-group">
                       <div class="col-sm-4 col-sm-offset-2" style="margin-top:25px">
                        <input type="hidden" name="subdelinact" value="1">
                        <button class="btn btn-md btn-primary btn-block" type="submit">Delete Inactive</button>
                       </div>
                     </div>
               	</form>
               </div>
            </div>
            <div class="well col-lg-5 pull-right" style="margin-right:18px">
               <h3>Users:</h3>
               <?php
                  displayUsers();
               ?>
            </div>
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