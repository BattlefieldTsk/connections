<?php

require_once('config.php');

// check for form submission - if it doesn't exist then send back to contact form
if (!isset($_POST["save"]) || $_POST["save"] != "addserver") {
    header("Location: ../servers.php"); exit;
}
  
// connect and insert server data into database
$con=mysqli_connect(PA_DATABASE_SERVER, PA_DATABASE_USER, PA_DATABASE_PASSWORD, PA_DATABASE_NAME);
// get the posted data
$name = htmlspecialchars(mysqli_real_escape_string($con, $_POST['name']));
$ip = htmlspecialchars(mysqli_real_escape_string($con, $_POST['ip']));
$max = htmlspecialchars(mysqli_real_escape_string($con, $_POST['max']));

// check that a name was entered
if (empty ($name))
    $error = "You must enter the name of your server.";

// check that an email address was entered
elseif (empty ($ip)) 
    $error = "You must enter the server IP address.";

// check that a message was entered
elseif (empty ($max))
    $error = "You must enter your server's max player limit.";
    
// check if an error was found - if there was, send the user back to the form
if (isset($error)) {
    header("Location: ../servers.php?e=".urlencode($error)); exit;
}

// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
$E_ = mysqli_query($con,"SELECT * FROM servers WHERE server_name='$name' OR ip='$ip'");
	if(mysqli_num_rows($E_) != 0) {
		$error = "A server with  that name ($name) or IP ($ip) already exists.";
		header("Location: ../servers.php?e=".urlencode($error)); exit;
	}
	else {
		$E_ = mysqli_query($con,"SELECT server_ip FROM player_analytics WHERE server_ip='$ip' LIMIT 0,1");
			if(mysqli_num_rows($E_) == 0) {
			$error = "No matching server found for $ip.";
			header("Location: ../servers.php?e=".urlencode($error)); exit;
			}
			else{
			$S_ = mysqli_query($con,"INSERT INTO `servers`(`ip`, `server_name`, `player_limit`) 
			VALUES ('$ip', '$name', '$max')");
			}
	}

mysqli_close($con);

// send the user back to the form
header("Location: ../servers.php?s=".urlencode("You have successfully added $name to the database.")); exit;

?>