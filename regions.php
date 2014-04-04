<?php
header( 'Content-Type: text/html; charset=UTF-8' );

include("include/session.php");
require_once('data/analytics.php');
require_once('data/database.php');
require_once('data/sessions.php');
require_once('data/users.php');
$db = new DatabaseCon();
$sessions = new PlayerSessions($db);
$user = new UserSessions($db);
?>

<?php
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
if(isset( $_GET['view'] )) {
  $view = mysqli_real_escape_string($con, $_GET["view"]);
}
else {
  $view = "country";
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

$sql = "SELECT ";
      if (isset( $_GET['server'] ) && isset( $_GET['view'] )){
        $sql .= "COUNT(DISTINCT $view) FROM player_analytics WHERE server_ip='$IP' AND $view IS NOT NULL";
      }
      elseif (isset( $_GET['server'] )){
        $sql .= "COUNT(DISTINCT country) FROM player_analytics WHERE server_ip='$IP' AND country IS NOT NULL";
      }
      elseif (isset( $_GET['view'] )){
        $sql .= "COUNT(DISTINCT $view) FROM player_analytics WHERE $view IS NOT NULL";
      }
      else{
        $sql .= "COUNT(DISTINCT country) FROM player_analytics WHERE country IS NOT NULL";
      }
        $sql .= " AND connect_date BETWEEN '$to' AND '$from'";

  $CountRows = mysqli_query($con,$sql);
  $rows = mysqli_fetch_row($CountRows);
  $numrows = $rows[0];
  // number of rows to show per page
  $rowsperpage = 50;
  // find out total pages
  $totalpages = ceil($numrows / $rowsperpage);
  // get the current page or set a default
  if (isset($_GET['page']) && is_numeric($_GET['page'])) {
     // cast var as int
     $currentpage = (int) $_GET['page'];
  } else {
     $currentpage = 1;
  }
  // the offset of the list, based on current page 
  $offset = ($currentpage - 1) * $rowsperpage;
  $prevpage = $currentpage - 1;
  $nextpage = $currentpage + 1;

/*if (!$check1_res) {
    printf("Error: %s\n", mysqli_error($con));
    exit();
}
*/
$sql = "SELECT *, ";
      if (isset( $_GET['server'] ) && isset( $_GET['view'] )){
        $sql .= "COUNT($view) AS total, SUM(duration) AS playtime FROM player_analytics 
                 WHERE server_ip='$IP' AND connect_date BETWEEN '$to' AND '$from'
                 AND country IS NOT NULL AND region IS NOT NULL AND city IS NOT NULL GROUP BY $view";
      }
      elseif (isset( $_GET['server'] )){
        $sql .= "COUNT(country) AS total, SUM(duration) AS playtime FROM player_analytics 
                 WHERE server_ip='$IP' AND connect_date BETWEEN '$to' AND '$from' GROUP BY country";
      }
      elseif (isset( $_GET['view'] )){
        $sql .= "COUNT($view) AS total, SUM(duration) AS playtime FROM player_analytics 
                 WHERE connect_date BETWEEN '$to' AND '$from' 
                 AND $view IS NOT NULL GROUP BY $view";
      }
      else{
        $sql .= "COUNT(country) AS total, SUM(duration) AS playtime FROM player_analytics WHERE country IS NOT NULL
                 AND connect_date BETWEEN '$to' AND '$from' GROUP BY country";
      }
        $sql .= " ORDER BY total DESC LIMIT $offset, $rowsperpage";

$locations = mysqli_query($con,$sql);

$mindate = mysqli_query($con,"SELECT connect_time FROM player_analytics ORDER BY connect_time ASC LIMIT 0,1");
  $row = mysqli_fetch_array($mindate);
    $epoch = $row['connect_time'];
    $min = date("m/d/Y", $epoch);

mysqli_close($con);

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
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php echo "$IP - Player Analytics" ?></title>
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
            <h1>Player Analytics 
              <small>Regions 
                <span class="pull-right"><?php if(isset($_GET['server'])) {echo "$sname";} ?></span>
              </small>
            </h1>
            <ol class="breadcrumb">
              <li><a href="index.php">Dashboard</a></li>
              <li><a href="regions.php">Regions</a></li>
              <li class="active"><?php echo "$IP" ?></li>
              <li class="pull-right">
              <?php if(isset( $_GET['server'] )) {echo "<form action=\"regions.php?server=$IP\" method=\"GET\">";}
                    elseif(isset( $_GET['view'] )) {echo "<form action=\"regions.php?view=$view\" method=\"GET\">";}
                    elseif(isset( $_GET['server'] ) && isset( $_GET['view'] )) {echo "<form action=\"regions.php?server=$IP&view=$View\" method=\"GET\">";}
                    else{echo "<form action=\"regions.php\" method=\"GET\">";} 
              ?>
                  <div id="reportrange" class="btn btn-green date">
                    <i class="fa fa-calendar"></i>
                    <span></span> <i class="fa fa-caret-down"></i>
                    <?php if(isset( $_GET['server'] )) {echo "<input type=\"hidden\" name=\"server\" value=\"$IP\">";}
                          elseif(isset( $_GET['view'] )) {echo "<input type=\"hidden\" name=\"view\" value=\"$view\">";}
                    ?>
                    <input type="hidden" name="to" id="to" value="">
                    <input type="hidden" name="from" id="from" value="">
                  </div>
                  <span>
                    <button type="submit" id="1" class="btn btn-green btn-flatleft date" type="button"><i class="fa fa-search"></i></button>
                  </span>
                </form>
              </li>
            </ol>
          </div>
        </div><!-- /.row -->
        <div class="row">
          <div class="col-lg-4">
            <div class="panel panel-default">
              <div class="panel-body">
                <div class="row">
                  <div class="col-xs-2">
                    <i class="fa fa-map-marker fa-5x"></i>
                  </div>
                  <div class="col-xs-10 text-right">
                    <p class="announcement-heading"><?php $countryData = $sessions->monthlyCountryAvg(); echo $countryData[0]['country_code3'] ?></p>
                    <p class="announcement-text">Largest Audience by Country</p>
                  </div>
                </div>
              </div>
              <?php
                if(isset( $_GET['server'] )) {
                  echo "<a href='regions.php?server=$IP&view=country&to=$to&from=$from'>";
                }
                else {
                  echo "<a href='regions.php?view=country&to=$to&from=$from'>";
                }
              ?>
                <div class="panel-footer">
                  <div class="row">
                    <div class="col-xs-6">
                      View Countrys
                    </div>
                    <div class="col-xs-6 text-right">
                      <i class="fa fa-arrow-right"></i>
                    </div>
                  </div>
                </div>
              </a>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="panel panel-default">
              <div class="panel-body">
                <div class="row">
                  <div class="col-xs-2">
                    <i class="fa fa-map-marker fa-5x"></i>
                  </div>
                  <div class="col-xs-10 text-right">
                    <p class="announcement-heading"><?php $regionData = $sessions->monthlyRegionAvg(); echo $regionData[0]['region'] ?></p>
                    <p class="announcement-text">Largest Audience by Region</p>
                  </div>
                </div>
              </div>
              <?php
                if(isset( $_GET['server'] )) {
                  echo "<a href='regions.php?server=$IP&view=region&to=$to&from=$from'>";
                }
                else {
                  echo "<a href='regions.php?view=region&to=$to&from=$from'>";
                }
              ?>
                <div class="panel-footer">
                  <div class="row">
                    <div class="col-xs-6">
                      View Regions
                    </div>
                    <div class="col-xs-6 text-right">
                      <i class="fa fa-arrow-right"></i>
                    </div>
                  </div>
                </div>
              </a>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="panel panel-default">
              <div class="panel-body">
                <div class="row">
                  <div class="col-xs-2">
                    <i class="fa fa-map-marker fa-5x"></i>
                  </div>
                  <div class="col-xs-10 text-right">
                    <p class="announcement-heading"><?php $cityData = $sessions->monthlyCityAvg(); echo $cityData[0]['city'] ?></p>
                    <p class="announcement-text">Largest Audience by City</p>
                  </div>
                </div>
              </div>
              <?php
                if(isset( $_GET['server'] )) {
                  echo "<a href='regions.php?server=$IP&view=city&to=$to&from=$from'>";
                }
                else {
                  echo "<a href='regions.php?view=city&to=$to&from=$from'>";
                }
              ?>
                <div class="panel-footer">
                  <div class="row">
                    <div class="col-xs-6">
                      View Cities
                    </div>
                    <div class="col-xs-6 text-right">
                      <i class="fa fa-arrow-right"></i>
                    </div>
                  </div>
                </div>
              </a>
            </div>
          </div>
        </div><!-- /.row -->
        <div class="row">
          <div class="col-lg-12">
            <?php
              echo "<ul class='pager' style='margin-top:0'>";
              if ($currentpage <= 1){
                echo "<li class='previous disabled'><a><i class='fa fa-angle-left'></i> Previous</a></li>";
              }
              else {
                if(isset( $_GET['server'] ) && (isset( $_GET['view'] ))){
                  echo "<li class='previous'><a href='regions.php?server=$IP&view=$view&to=$to&from=$from&page=$prevpage'><i class='fa fa-angle-left'></i> Previous</a></li>";
                }
                elseif(isset( $_GET['server'] )){
                  echo "<li class='previous'><a href='regions.php?server=$IP&to=$to&from=$from&page=$prevpage'><i class='fa fa-angle-left'></i> Previous</a></li>";
                }
                elseif(isset( $_GET['view'] )){
                  echo "<li class='previous'><a href='regions.php?view=$view&to=$to&from=$from&page=$prevpage'><i class='fa fa-angle-left'></i> Previous</a></li>";
                }
                else {
                  echo "<li class='previous'><a href='regions.php?to=$to&from=$from&page=$prevpage'><i class='fa fa-angle-left'></i> Previous</a></li>";
                }
              }
                echo "<li style='font-size:18px'>$currentpage/$totalpages</li>";
              if ($currentpage != $totalpages){
                if(isset( $_GET['server'] ) && (isset( $_GET['view'] ))){
                  echo "<li class='next'><a href='regions.php?server=$IP&to=$to&from=$from&view=$view&page=$nextpage'>Next <i class='fa fa-angle-right'></i></a></li>";
                }
                elseif(isset( $_GET['server'] )){
                  echo "<li class='next'><a href='regions.php?server=$IP&to=$to&from=$from&page=$nextpage'>Next <i class='fa fa-angle-right'></i></a></li>";
                }
                elseif(isset( $_GET['view'] )){
                  echo "<li class='next'><a href='regions.php?view=$view&to=$to&from=$from&page=$nextpage'>Next <i class='fa fa-angle-right'></i></a></li>";
                }
                else {
                  echo "<li class='next'><a href='regions.php?to=$to&from=$from&page=$nextpage'>Next <i class='fa fa-angle-right'></i></a></li>";
                }
              }
              else {
                echo "<li class='next disabled'><a>Next <i class='fa fa-angle-right'></i></a></li>";
              }
                echo "</ul>";
              ?>
            <div class="panel panel-primary">
              <div class="panel-heading">
                <h3 class="panel-title">
                  <i class="fa fa-globe"></i> 
                  Ranked by "<?php echo "$view"; ?>" 
                  <span class="pull-right"><?php echo "$to  -  $from"; ?></span>
                </h3>
              </div>
              <div class="table-responsive">
                <table id="regions" class="table table-striped table-bordered table-condensed table-tablesorter">
                  <thead>
                    <tr>
                      <th class="default" style="text-align:left"><?php if($view == "region"){ echo "Region";} elseif($view == "city"){ echo "City";} else{ echo "Country";} ?></th>
                      <th class="default" style="text-align:right">Connections</th>
                      <th class="default" style="text-align:right">Playtime</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
            while($row = mysqli_fetch_array($locations))
              {
                $Country = $row['country'];
                $CCode3 = $row['country_code3'];
                $Region = $row['region'];
                $City = $row['city'];
                $Connections = $row['total'];
                $Playtime = $row['playtime'];
          ?>
            <tr>
              <td style="text-align:left"><?php echo(ConnRegion($City,$Region,$view,$Country)); ?></td>
              <td style="text-align:right"><?php echo "$Connections"; ?></td>
              <td style="text-align:right"><?php echo(ConvertMin($Playtime)); ?></td>
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
    <script src="js/tablesorter/jquery.tablesorter.min.js"></script>
    <script src="js/daterangepicker/moment.min.js"></script>
    <script src="js/daterangepicker/daterangepicker.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
          $("#regions").tablesorter({
            headers: {
              2: {
                sorter: 'timespan'
              }
            },
            sortList: [[1,1]]
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
  </body>
</html>
<?php
}
?>