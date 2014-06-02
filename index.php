<?php
/*
 *   Steam Player Analytics
 *   Copyright (C) 2013  Jake "rannmann" Forrester
 *   http://firepoweredgaming.com
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

include_once("include/session.php");

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
require_once('data/users.php');
require_once('data/servers.php');
$db = new DatabaseCon();
$sessions = new PlayerSessions($db);
$user = new UserSessions($db);
$server = new ServerSessions($db);

$con=mysqli_connect(PA_DATABASE_SERVER, PA_DATABASE_USER, PA_DATABASE_PASSWORD, PA_DATABASE_NAME);
// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

if(isset( $_GET['server'] )) {
  $IP = mysqli_real_escape_string($con, $_GET['server']);
}
else {
  $IP = "All Servers";
}

if(isset( $_GET['to'] ) && isset( $_GET['from'] )) {
  $to = mysqli_real_escape_string($con, $_GET['to']);
  $from = mysqli_real_escape_string($con, $_GET['from']);

  if (empty ($from) && empty ($to)){
    $from = date("Y-m-d");
    $to = date("Y-m-d", strtotime("-30 days"));
  }
}
else {
  $from = date("Y-m-d");
  $to = date("Y-m-d", strtotime("-30 days"));
}

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

    <title>Dashboard - Player Analytics</title>
    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Add custom CSS here -->
    <link rel="stylesheet" href="css/daterangepicker-bs3.css">
    <link href="css/player_analytics.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- Page Specific CSS -->
    <link rel="stylesheet" href="css/morris-0.4.3.min.css">
  </head>
  <body>
  <?php require("include/nav.php") ?>
      <div class="main">
          <h1 class="page-header">Dashboard <small>Overview</small></h1>
      <ol class="breadcrumb">
        <li class="active">Dashboard</li>
        <li class="pull-right">
          <?php echo "<form action=\"index.php\" method=\"GET\">"; ?>
            <div id="reportrange" class="btn btn-green date">
              <i class="fa fa-calendar"></i>
              <span></span> <i class="fa fa-caret-down"></i>
              <input type="hidden" name="to" id="to" value="">
              <input type="hidden" name="from" id="from" value="">
            </div>
            <span>
              <button type="submit" id="1" class="btn btn-green btn-flatleft date" type="button"><i class="fa fa-search"></i></button>
            </span>
          </form>
        </li>
      </ol>
          <div class="row">
            <div class="col-md-3 col-sm-6">
              <div class="panel panel-default">
                <div class="panel-body">
                  <div class="row">
                    <div class="col-sm-3 col-xs-2">
                      <i class="fa fa-users fa-5x"></i>
                    </div>
                    <div class="col-sm-9 col-xs-10 text-right">
                      <p class="announcement-heading"><?php echo number_format($user->monthlyTotal()); ?></p>
                      <p class="announcement-text">Total Users</p>
                    </div>
                  </div>
                </div>
                <a href="players.php">
                  <div class="panel-footer">
                    <div class="row">
                      <div class="col-xs-10">
                        View Users
                      </div>
                      <div class="col-xs-2 text-right">
                        <i class="fa fa-arrow-right"></i>
                      </div>
                    </div>
                  </div>
                </a>
            </div>
          </div>
            <div class="col-md-3 col-sm-6">
              <div class="panel panel-default">
              <div class="panel-body">
                <div class="row">
                <div class="col-sm-3 col-xs-2">
                  <i class="fa fa-sun-o fa-5x"></i>
                </div>
                  <div class="col-sm-9 col-xs-10 text-right">
                    <p class="announcement-heading"><?php echo number_format($sessions->monthlyTotal()); ?></p>
                    <p class="announcement-text">Total Sessions</p>
                  </div>
                </div>
              </div>
                <a href="sessions.php">
          <div class="panel-footer">
            <div class="row">
            <div class="col-xs-10">
              View Sessions
            </div>
            <div class="col-xs-2 text-right">
              <i class="fa fa-arrow-right"></i>
            </div>
            </div>
          </div>
          </a>
              </div>
            </div>
            <div class="col-md-3 col-sm-6">
              <div class="panel panel-default">
              <div class="panel-body">
                <div class="row">
                <div class="col-sm-3 col-xs-2">
                  <i class="fa fa-clock-o fa-5x"></i>
                </div>
                  <div class="col-sm-9 col-xs-10 text-right">
                  <p class="announcement-heading"><?php echo gmdate('G:i:s', $sessions->monthlyTimeAvg()); ?></p>
                      <p class="announcement-text">Avg Playtime</p>
                  </div>
                </div>
              </div>
                <a href="sessions.php">
          <div class="panel-footer">
            <div class="row">
            <div class="col-xs-10">
              View Playtimes
            </div>
            <div class="col-xs-2 text-right">
              <i class="fa fa-arrow-right"></i>
            </div>
            </div>
          </div>
          </a>
              </div>
            </div>
            <div class="col-md-3 col-sm-6">
              <div class="panel panel-default">
              <div class="panel-body">
                <div class="row">
                <div class="col-sm-3 col-xs-2">
                  <i class="fa fa-globe fa-5x"></i>
                </div>
                  <div class="col-sm-9 col-xs-10 text-right">
                  <p class="announcement-heading"><?php $countryData = $sessions->monthlyCountryAvg(); echo $countryData[0]['country_code3'] ?></p>
                      <p class="announcement-text">Most Active Region</p>
                  </div>
                </div>
              </div>
                <a href="regions.php">
          <div class="panel-footer">
            <div class="row">
            <div class="col-xs-10">
              View Regions
            </div>
            <div class="col-xs-2 text-right">
              <i class="fa fa-arrow-right"></i>
            </div>
            </div>
          </div>
          </a>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-12 chart">
              <div class="panel panel-default">
                <div class="panel-heading">
                  <h3 class="panel-title"><i class="fa fa-bar-chart-o"></i> Connection Statistics</h3>
                </div>
                <div class="panel-body">
                  <div id="session-month-daily" style="height:250px"></div><br><br><br>
                </div>
              </div>
            </div>
          </div><!-- /.row -->
          <div class="row">
            <div class="col-lg-3 chart">
              <div class="panel panel-default">
                <div class="panel-heading">
                  <h3 class="panel-title"><i class="fa fa-long-arrow-right"></i> Connection Sources</h3>
                </div>
                <div class="panel-body">
                  <div id="morris-chart-donut"></div>
                </div>
              </div>
            </div>
            <div class="col-lg-3 chart">
              <div class="panel panel-default">
                <div class="panel-heading">
                  <h3 class="panel-title"><i class="fa fa-clock-o"></i> Recent Activity (24 hours)</h3>
                </div>
                <div class="panel-body">
                  <div id="morris-chart-line"></div>
                </div>
              </div>
            </div>
            <div class="col-lg-3 chart">
              <div class="panel panel-default">
                <div class="panel-heading">
                  <h3 class="panel-title"><i class="fa fa-dollar"></i> User Status</h3>
                </div>
                <div class="panel-body">
                  <div id="f2p"></div>
                </div>
              </div>
            </div>
            <div class="col-lg-3 chart">
              <div class="panel panel-default">
                <div class="panel-heading">
                  <h3 class="panel-title"><i class="fa fa-link"></i> Connection Method</h3>
                </div>
                <div class="panel-body">
                  <div id="method"></div>
                </div>
              </div>
            </div>
          </div><!-- /.row -->
        </div>
      </div>
    </div>
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
        var cb = function(start, end) {
            console.log("Callback has been called!");
            $('#reportrange span').html(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
            $('#to').val(start.format('YYYY-MM-DD'));
            $('#from').val(end.format('YYYY-MM-DD'));
           }
        var optionSet1 = {
          startDate: moment().subtract('days', 29),
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
  data: 
    <?php echo $sessions->jsDailyTotal(); ?>
  ,
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
  hidehover: true,
});

Morris.Donut({
  element: 'morris-chart-donut',
  labelColor: '#ecf0f1',
  data: <?php echo $sessions->jsonMonthlyCountryAvgPct(10); ?>,
  formatter: function (y) { return y + "%" ;}
});

Morris.Donut({
  element: 'f2p',
  labelColor: '#ecf0f1',
  data: <?php echo $user->jsPremiumTotal(); ?>,
  formatter: function (y) { return y + "%" ;}
});

Morris.Donut({
  element: 'method',
  labelColor: '#ecf0f1',
  data: <?php echo $user->jsMethodTotal(); ?>,
  formatter: function (y) { return y + "%" ;}
});

Morris.Line({
  // ID of the element in which to draw the chart.
  element: 'morris-chart-line',
  // Chart data records -- each entry in this array corresponds to a point on
  // the chart.
  data: <?php echo json_encode($sessions->hourly()); ?>,
  // The name of the data record attribute that contains x-visitss.
  xkey: 'time',
  // A list of names of data record attributes that contain y-visitss.
  ykeys: ['total'],
  // Labels for the ykeys -- will be displayed when you hover over the
  // chart.
  labels: ['Visits'],
  // Disables line smoothing
  smooth: false,
  hidehover: true,
});
    </script>
  </body>
</html>
<?php
}
?>