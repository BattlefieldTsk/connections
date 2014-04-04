<?php

require_once('config.php');

// check for form submission - if it doesn't exist then send back to contact form
if (!isset($_POST["save"]) || $_POST["save"] != "deleteserver") {
    header("Location: ../servers.php"); exit;
}

// connect and insert server data into database
$con=mysqli_connect(PA_DATABASE_SERVER, PA_DATABASE_USER, PA_DATABASE_PASSWORD, PA_DATABASE_NAME);
// get the posted data
$id = htmlspecialchars(mysqli_real_escape_string($con, $_POST["id"]));
$name = htmlspecialchars(mysqli_real_escape_string($con, $_POST["name"]));

// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

$S_ = mysqli_query($con,"DELETE FROM servers WHERE server_id='$id'");

mysqli_close($con);

// send the user back to the form
header("Location: ../servers.php?s=".urlencode("You have successfully deleted $name.")); exit;

?>