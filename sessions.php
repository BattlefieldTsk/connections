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

$sql = "SELECT COUNT(DISTINCT auth) FROM player_analytics 
        WHERE connect_date BETWEEN '$to' AND '$from'";
    if($IP !== "All Servers" && isset($_GET['player'])){
        $sql .= "AND server_ip='$IP' AND NAME LIKE '%$player%'";
    }
    if(isset($_GET['player'])){
        $sql .= "AND NAME LIKE '%$player%'";
    }

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
     // default page num
     $currentpage = 1;
  } // end if

  // the offset of the list, based on current page 
  $offset = ($currentpage - 1) * $rowsperpage;
  $prevpage = $currentpage - 1;
  $nextpage = $currentpage + 1;


  $sql = "SELECT *, servers.ip, servers.server_name AS servername, servers.player_limit AS max 
              FROM player_analytics LEFT JOIN servers ON player_analytics.server_ip=servers.ip
              WHERE connect_date BETWEEN '$to' AND '$from'";
            if($IP !== "All Servers"){
              $sql .= "AND server_ip = '$IP'";
            }
              $sql .= "ORDER BY connect_time DESC LIMIT $offset, $rowsperpage";

$players = mysqli_query($con,$sql);

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

    <title>Sessions - Player Analytics</title>
    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Add custom CSS here -->
    <link rel="stylesheet" href="css/daterangepicker-bs3.css">
    <link href="css/player_analytics.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- Page Specific CSS -->
    <link rel="stylesheet" href="css/sorter.css">
  </head>
  <body>
<?php require("include/nav.php") ?>
      <div class="main">
        <div class="row">
          <div class="col-lg-12">
            <h1>Player Analytics 
              <small>Sessions 
                <span class="pull-right"><?php if(isset($_GET['server'])) {echo "$sname";} ?></span>
              </small>
            </h1>
            <ol class="breadcrumb">
              <li><a href="index.php">Dashboard</a></li>
              <?php 
                if(isset( $_GET['server'] )){
                  echo "<li><a href='sessions.php'>Sessions</a></li>";
                  echo "<li class='active'>$IP</li>";
                }
                else {
                  echo "<li class='active'>Sessions</li>";
                }
              ?>
              <li class="pull-right">
              <?php if(isset( $_GET['server'] )) {echo "<form action=\"sessions.php?server=$IP\" method=\"GET\">";}
                    else{echo "<form action=\"sessions.php\" method=\"GET\">";} 
              ?>
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
          </div>
        </div><!-- /.row -->
        <div class="row">
          <div class="col-lg-12">
                    <?php
                      echo "<ul class='pager' style='margin-top:0'>";
                        if ($currentpage <= 1){
                          echo "<li class='previous disabled'><a><i class='fa fa-angle-left'></i> Previous</a></li>";
                        }
                        elseif(isset( $_GET['server'] )){
                          echo "<li class='previous'><a href='sessions.php?server=$IP&to=$to&from=$from&page=$prevpage'><i class='fa fa-angle-left'></i> Previous</a></li>";
                        }
                        else{
                          echo "<li class='previous'><a href='sessions.php?to=$to&from=$from&page=$prevpage'><i class='fa fa-angle-left'></i> Previous</a></li>";
                        }
                      echo "<li style='font-size:18px'>$currentpage/$totalpages</li>";
                        if ($currentpage != $totalpages && isset( $_GET['server'] )){
                          echo "<li class='next'><a href='sessions.php?server=$IP&to=$to&from=$from&page=$nextpage'>Next <i class='fa fa-angle-right'></i></a></li>";
                        }
                        elseif ($currentpage != $totalpages){
                          echo "<li class='next'><a href='sessions.php?to=$to&from=$from&page=$nextpage'>Next <i class='fa fa-angle-right'></i></a></li>";
                        }
                        else {
                          echo "<li class='next disabled'><a>Next <i class='fa fa-angle-right'></i></a></li>";
                        }
                      echo "</ul>";
                  ?>
          </div>
        </div><!-- /.row -->
        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-users"></i> Connections</h3>
              </div>
              <div class="table-responsive">
                <table id="sessions" class="table table-striped table-bordered table-condensed">
                  <thead>
                    <tr>
                      <th style="text-align:left" class="default" s>Player</th>
                      <th width="7%" style="text-align:left" class="default">Method</th>
                      <th style="text-align:left" class="default">Server</th>
                      <th style="text-align:left" class="default">Map</th>
                      <th width="12%" style="text-align:right" class="default">Time</th>
                      <th style="text-align:right" class="default">Duration</th>
                      <th width="1%" style="text-align:right" class="default"><i class="fa fa-users"></i></th>
                      <th width="6%" style="text-align:center" class="default">Locale</th>
                      <th width="1%" style="text-align:center" class="default"><i class="fa fa-flag"></i></th>
                      <th width="1%" style="text-align:center" class="default">Prem.</th>
                      <th width="1%" style="text-align:center" class="default">HTML</th>
                      <th width="1%" style="text-align:center" class="default">OS</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
            while($row = mysqli_fetch_array($players))
              {
                $Player = $row['name'];
                $SteamID = $row['auth'];
                $ID = $row['id'];
                $Time = $row['connect_time'];
                $Method = $row['connect_method'];
                $Server = $row['server_ip'];
                $NUMPlayers = $row['numplayers'];
                $Map = $row['map'];
                $Duration = $row['duration'];
                $Playtime = $row['duration'];
                $Country = $row['country'];
                $CCode = $row['country_code'];
                $CCode3 = $row['country_code3'];
                $Region = $row['region'];
                $City = $row['city'];
                $Flags = $row['flags'];
                $Premium = $row['premium'];
                $MOTD = $row['html_motd_disabled'];
                $OS = $row['os'];
                $Internet="serverbrowser_internet";
                $Favorites="serverbrowser_favorites";
                $Steam="steam";
                $Friends="serverbrowser_friends";
                $ServerName = $row['servername'];
                $MaxPlayers = $row['max'];
          ?>
                    <tr>
                      <td style="text-align:left"><?php echo "<a href='player.php?id=$SteamID'>"?><?php echo "$Player"; ?></a></td>
                      <td style="text-align:left"><?php echo(ConnMethod($Method)); ?></td>
                      <td style="text-align:left"><a href="server.php?server=<?php echo $Server;?>"><?php if($ServerName == NULL) {echo "$Server";} else{echo "$ServerName";} ?></a></td>
                      <td style="text-align:left"><?php echo "<a href=\"map.php?map=$Map\">$Map</a>"; ?></td>
                      <td style="text-align:right"><?php echo date('m/d/y g:i a', $Time);?></td>
                      <td style="text-align:right"><?php if ($Duration==NULL) {echo "<i style='color:#3498db' class='fa fa-refresh fa-spin' title='Connected'></i>";} else {echo(ConvertMin($Playtime));} ?></td>
                      <td style="text-align:right"><?php echo "$NUMPlayers/$MaxPlayers" ?></td>
                      <td style="text-align:center"><a href="region.php?view=<?php echo $Country; ?>"><em class="tip"  data-toggle='tooltip' title='<?php echo "$Country"; ?>'><?php echo "$CCode3"; ?></em></a></td>
                      <td style="text-align:center"><?php echo(ConnFlags($Flags)); ?></td>
                      <td style="text-align:center"><?php echo(ConnPremium($Premium)); ?></td>
                      <td style="text-align:center"><?php echo(ConnMOTD($MOTD)); ?></td>
                      <td style="text-align:center"><?php echo(ConnOS($OS)); ?></td>
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
      $(function() { 
        $('.tip').tooltip();
    });
        $(document).ready(function() {
          $("#sessions").tablesorter({
            headers: {
              4: {sorter: 'digit'},
              6: {sorter: 'digit'},
              8: {sorter: false},
              9: {sorter: false},
              10: {sorter: false},
              11: {sorter: false},
              12: {sorter: false}
            },
            sortList: [[4,1]]
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