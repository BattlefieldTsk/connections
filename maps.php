<?php
header( 'Content-Type: text/html; charset=UTF-8' );

include("include/session.php");

/**
 * User not an administrator, redirect to main page
 * automatically.
 */
if(!$session->logged_in){
   header("Location: main.php");
}
else{
/**
 * Administrator is viewing page, so display all
 * forms.
 */

require_once('data/analytics.php');
require_once('data/database.php');
require_once('data/maps.php');
$db = new DatabaseCon();
$MapSessions = new MapSessions($db);
?>
<?php

$con=mysqli_connect(PA_DATABASE_SERVER, PA_DATABASE_USER, PA_DATABASE_PASSWORD, PA_DATABASE_NAME);
// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

$Maps = mysqli_query($con,"SELECT map, SUM(numplayers) AS avg_player, COUNT(auth) AS players, SUM(duration) AS total FROM player_analytics GROUP BY map ORDER BY total DESC");

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php echo "Maps - Player Analytics" ?></title>
    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Add custom CSS here -->
    <link href="css/player_analytics.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- Page Specific CSS -->
    <link rel="stylesheet" href="css/morris-0.4.3.min.css">
    <link rel="stylesheet" href="css/sorter.css">
  </head>

  <body>

<?php require("include/nav.php") ?>
      <div class="main">
        <div class="row">
          <div class="col-lg-12">
            <h1>Player Analytics <small>Maps</small></h1>
            <ol class="breadcrumb">
              <li><a href="index.php">Dashboard</a></li>
              <li class="active">Maps</li>
            </ol>
          </div>
        </div><!-- /.row -->
<?php  
        // check for a form error  
        if (isset($_GET['e'])) echo "<div class=\"alert alert-danger\">".$_GET['e']."</div>";
?>
        <div class="row">
          <div class="col-lg-12 chart">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-bar-chart-o"></i> Connection Statistics</h3>
              </div>
              <div class="panel-body">
                <div id="maps-chart" style="height:250px"></div>
              </div>
            </div>
          </div>
        </div><!-- /.row -->

        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-picture-o"></i> Maps</h3>
              </div>
              <div class="table-responsive">
                <table id="maps" class="table table-striped table-bordered table-condensed">
                  <thead>
                    <tr>
                      <th style="text-align:left">Map</th>
                      <th style="text-align:right">Total Players</th>
                      <th style="text-align:right">Playtime</th>
                      <th style="text-align:right">Avg Playtime</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                  while($row = mysqli_fetch_array($Maps))
                    {
                      $total = $row['total'];
                      $map = $row['map'];
                      $players = $row['players'];
                ?>
                  <tr>
                    <td style="text-align:left"><?php echo "<a href='map.php?map=$map'>$map";?></td>
                    <td style="text-align:right"><?php echo "$players"; ?></td>
                    <td style="text-align:right"><?php echo(ConvertMin($total)); ?></td>
                    <td style="text-align:right"><?php echo (ConvertMin(round("$total"/"$players",0))); ?></td>
                  </tr>
                <?php }; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div><!-- /.row -->
      </div><!-- /#page-wrapper -->
    </div><!-- /#wrapper -->

    <!-- Bootstrap core JavaScript -->
    <script src="js/jquery/jquery-1.11.0.min.js"></script>
    <script src="js/bootstrap/bootstrap.min.js"></script>
    <!-- Page Specific Plugins -->
    <script src="js/morris/raphael-2.1.0.min.js"></script>
    <script src="js/morris/morris.min.js"></script>
    <script src="js/tablesorter/jquery.tablesorter.min.js"></script>
    <script type="text/javascript">
      $(document).ready(function() {
        $("#maps").tablesorter({
          sortList: [[1,1]]
        });
      });
    </script>
    <script type="text/javascript">

Morris.Bar ({
  element: 'maps-chart',
  data: <?php echo $MapSessions->mapTotal(10); ?>,
  xkey: 'map',
  ykeys: ['players'],
  labels: ['Players'],
  barRatio: 0.4,
  hideHover: 'auto',
});
    </script>
  </body>
</html>
<?php
}
?>