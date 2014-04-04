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
require_once('data/sessions.php');
$db = new DatabaseCon();
$sessions = new PlayerSessions($db);
?>
<?php

$con=mysqli_connect(PA_DATABASE_SERVER, PA_DATABASE_USER, PA_DATABASE_PASSWORD, PA_DATABASE_NAME);
// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

if(isset( $_GET['map'] )) {
  $Map = mysqli_real_escape_string($con, $_GET['map']);
  $E_ = mysqli_query($con,"SELECT * FROM player_analytics WHERE map='$Map'");
  if(mysqli_num_rows($E_) < 1) {
    $error = "No information for map $Map.";
    header("Location: maps.php?e=".urlencode($error)); exit;
  }
}
else {
  header("Location: maps.php"); exit;
}

if(isset( $_GET['to'] ) && isset( $_GET['from'] )) {
  $to = mysqli_real_escape_string($con, $_GET['to']);
  $from = mysqli_real_escape_string($con, $_GET['from']);

  if (empty ($from) && empty ($to)){
    $from = date("Y-m-d");
    $to = date("Y-m-d", strtotime("-6 days"));
  }
}
else {
  $from = date("Y-m-d");
  $to = date("Y-m-d", strtotime("-6 days"));
}

$connections = mysqli_query($con,"SELECT player_analytics.*, COUNT(`auth`) AS players, SUM(`numplayers`)
  AS total_numplayers, servers.ip, servers.server_name AS servername 
  FROM player_analytics
  LEFT JOIN servers
  ON player_analytics.server_ip=servers.ip
  WHERE map='$Map'
  AND connect_date BETWEEN '$to' AND '$from' 
  GROUP BY connect_date, server_ip  ORDER BY connect_date DESC LIMIT 0,31");

/*if (!$check1_res) {
    printf("Error: %s\n", mysqli_error($con));
    exit();
}*/

$profile = mysqli_query($con,"SELECT *, COUNT(`id`) AS players, SUM(`numplayers`)
  AS total_numplayers, SUM(`duration`) AS playtime FROM player_analytics WHERE map='$Map'");
  $p_ = mysqli_fetch_array($profile);

$mindate = mysqli_query($con,"SELECT connect_time FROM player_analytics ORDER BY connect_time ASC LIMIT 0,1");
  $row = mysqli_fetch_array($mindate);
    $epoch = $row['connect_time'];
    $min = date("m/d/Y", $epoch);

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php echo "$Map - Player Analytics" ?></title>
    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Add custom CSS here -->
    <link rel="stylesheet" href="css/daterangepicker-bs3.css">
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
            <h1>Player Analytics <small><?php echo "$Map" ?></small></h1>
            <ol class="breadcrumb">
              <li><a href="index.php">Dashboard</a></li>
              <li><a href="maps.php">Maps</a></li>
              <li class="active"><?php echo "$Map" ?></li>
              <li class="pull-right">
              <?php echo "<form action=\"map.php?map=$Map\" method=\"GET\">"; ?>
                  <div id="reportrange" class="btn btn-green date">
                    <i class="fa fa-calendar"></i>
                    <span></span> <i class="fa fa-caret-down"></i>
                    <?php echo "<input type=\"hidden\" name=\"map\" value=\"$Map\">"; ?>
                    <input type="hidden" name="to" id="to" value="">
                    <input type="hidden" name="from" id="from" value="">
                  </div>
                  <span>
                    <button type="submit" id="1" class="btn btn-green btn-flatleft date" type="button"><i class="fa fa-search"></i></button>
                  </span>
                </form>
            </ol>
          </div>
        </div><!-- /.row -->
        <div class="row">
          <div class="col-lg-4">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-picture-o"></i> Map Profile</h3>
              </div>
              <img width='100%' <?php echo "src='images/maps/$Map.jpg'"; ?> onerror="this.src='images/maps/340x255.gif'">
              <div class="profile-info">
                  <h3><?php echo "$Map"; ?></h3>
              </div>
              <div>
                <table class="table profile">
                  <tr><td class="left">Total Sessions</td><td class="right"><?php echo $p_['players']; ?></td></tr>
                  <tr>
                    <td class="left">AVG Players</td>
                    <td class="right"><?php echo round($p_['total_numplayers']/$p_['players'],0); ?></td>
                  </tr>
                  <tr><td class="left">Total Duration</td><td class="right"><?php echo (ConvertMin($p_['playtime'])); ?></td></tr>
                  <tr>
                    <td class="left">AVG Duration</td>
                    <td class="right"><?php echo (ConvertMin(round($p_['playtime']/$p_['players'],0))); ?></td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
          <div class="col-lg-8">
              <div class="panel panel-primary chart">
                <div class="panel-heading">
                  <h3 class="panel-title"><i class="fa fa-picture-o"></i> Map Statistics</h3>
                </div>
                <div class="panel-body">
                  <div id="session-month-daily" style="height:150px"></div>
                </div>
              </div>
            <div class="panel panel-primary">
              <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-picture-o"></i> Daily Map Statistics</h3>
              </div>
              <div class="table-responsive">
                <table id="maps" class="table table-striped table-bordered table-condensed">
                  <thead>
                    <tr>
                      <th width="15%" class="default" style="text-align:left">Date</th>
                      <th class="default" style="text-align:right">Server</th>
                      <th width="15%" class="default" style="text-align:right">Sessions</th>
                      <th width="15%" class="default" style="text-align:right">Avg Players</th>
                      <th width="15%" class="default" style="text-align:right">Time</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
            while($row = mysqli_fetch_array($connections))
              {
                $Date = $row['connect_date'];
                $Map = $row['map'];
                $Duration = $row['duration'];
                $Players = $row['players'];
                $Server = $row['server_ip'];
                $TotalPlayers = $row['total_numplayers'];
                $ServerName = $row['servername'];
          ?>
                    <tr>
                      <td style="text-align:left"><?php echo "$Date"; ?></td>
                      <td style="text-align:right"><a href="server.php?server=<?php echo $Server;?>"><?php if($ServerName == NULL) {echo "$Server";} else{echo "$ServerName";} ?></a></td>
                      <td style="text-align:right"><?php echo "$Players"; ?></td>
                      <td style="text-align:right"><?php echo round("$TotalPlayers"/"$Players",0); ?></td>
                      <td style="text-align:right"><?php if ($Duration==NULL) {echo "<i style='color:#3498db' class='fa fa-refresh fa-spin' title='Connected'></i>";} else {echo(ConvertMin($Duration));} ?></td>
                    </tr>
          <?php
            };
          ?>
                    
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
    <script src="js/daterangepicker/moment.min.js"></script>
    <script src="js/daterangepicker/daterangepicker.js"></script>
    <script type="text/javascript">
      $(document).ready(function() {
        $("#maps").tablesorter({
          sortList: [[0,1]]
        });
      });
      $(document).ready(function() {
      var cb = function(start, end) {
          console.log("Callback has been called!");
          $('#reportrange span').html(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
          $('#to').val(start.format('YYYY-MM-DD'));
          $('#from').val(end.format('YYYY-MM-DD'));
         }
      var optionSet1 = {
        startDate: moment().subtract('days', 6),
        endDate: moment(),
        minDate: '<?php echo $min; ?>',
        maxDate: '<?php echo date("m/d/Y"); ?>',
        dateLimit: { days: 60 },
        showDropdowns: true,
        showWeekNumbers: true,
        timePicker: false,
        timePickerIncrement: 1,
        timePicker12Hour: true,
        ranges: {
           'Today': [moment().format('MM/DD/YYYY'), moment().format('MM/DD/YYYY')],
           'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
           'Last 7 Days': [moment().subtract('days', 6), moment()],
           'Last 30 Days': [moment().subtract('days', 29), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
        },
        opens: 'left',
        buttonClasses: ['btn btn-default'],
        applyClass: 'btn-small btn-primary',
        cancelClass: 'btn-small',
        format: 'MM/DD/YYYY',
        separator: ' to ',
        locale: {
            applyLabel: 'Submit',
            cancelLabel: 'Clear',
            fromLabel: 'From',
            toLabel: 'To',
            customRangeLabel: 'Custom',
            daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr','Sa'],
            monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            firstDay: 1
        }
      };

        $('#reportrange span').html('<?php echo "$to"; ?>' + ' - ' + '<?php echo "$from" ?>');
        $('#reportrange').daterangepicker(optionSet1, cb);

      });
    </script>
    <script type="text/javascript">
      Morris.Area({
      // ID of the element in which to draw the chart.
      element: 'session-month-daily',
      // Chart data records -- each entry in this array corresponds to a point on
      // the chart.
      data: <?php echo $sessions->jsDailyTotal(); ?>,
      // The name of the data record attribute that contains x-visitss.
      xkey: 'connect_date',
      // A list of names of data record attributes that contain y-visitss.
      ykeys: ['total'],
      // Labels for the ykeys -- will be displayed when you hover over the
      // chart.
      labels: ['Sessions'],
      // Disables line smoothing
      smooth: false,
      fillOpacity: 0.5,
    });
    </script>
  </body>
</html>
<?php
}
?>