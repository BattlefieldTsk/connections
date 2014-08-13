<?php
header( 'Content-Type: text/html; charset=UTF-8' );
//Dictates that is page will use profile settings.
$Profile = 1;

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

class PersonaState
  {
    const Offline = 0;
    const Online = 1;
    const Busy = 2;
    const Away = 3;
    const Snooze = 4;
    const Trading = 5;
    const Looking = 6;
  }

function PlayerStatus($state)
  {
    if ($state == PersonaState::Online || $state == PersonaState::Trading || $state == PersonaState::Looking)
      return "profile online";
    else if ($state == PersonaState::Offline) {
      return "profile offline";
    }
    else if ($state == PersonaState::Busy)
      return "profile busy";
    else
      return "profile away";
  }

function SteamTo64($id)
  {
    $parts = explode(':', str_replace('STEAM_', '' ,$id));
    return bcadd(bcadd('76561197960265728', $parts['1']), bcmul($parts['2'], '2'));
  }

function GetPlayerInformation($steam64)
  {
    $url = "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".STEAM_APIKEY."&steamids=".$steam64.'&format=json';
    $information = json_decode(file_get_contents($url), true);
    return $information['response']['players'][0];
  }

$steam64 = SteamTo64($_GET['id']);
$data = GetPlayerInformation(SteamTo64($_GET['id']));
$id = $_GET['id'];

$con=mysqli_connect(PA_DATABASE_SERVER, PA_DATABASE_USER, PA_DATABASE_PASSWORD, PA_DATABASE_NAME);
// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

$id = mysqli_real_escape_string($con, $_GET['id']);
$CountRows = mysqli_query($con,"SELECT COUNT(auth) FROM player_analytics WHERE auth='$id'
   AND `connect_date` BETWEEN DATE_FORMAT(NOW() - INTERVAL 30 DAY, '%Y-%m-%d') AND DATE_FORMAT(NOW(), '%Y-%m-%d')");

$rows = mysqli_fetch_row($CountRows);
$numrows = $rows[0];

// number of rows to show per page
$rowsperpage = 15;

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

$connections = mysqli_query($con,"SELECT player_analytics.*, player_analytics.ip AS player_ip, servers.ip, servers.server_name AS servername, servers.player_limit AS max
  FROM player_analytics
  LEFT JOIN servers
  ON player_analytics.server_ip=servers.ip
  WHERE auth='$id'
  AND `connect_date` BETWEEN DATE_FORMAT(NOW() - INTERVAL 30 DAY, '%Y-%m-%d') AND DATE_FORMAT(NOW(), '%Y-%m-%d')
  ORDER BY connect_time DESC LIMIT $offset, $rowsperpage");

$profile = mysqli_query($con,"SELECT *, SUM(duration) AS total FROM player_analytics WHERE auth='$id'
  ORDER BY connect_time DESC LIMIT 0, 1");
  while($row = mysqli_fetch_array($profile))
    {
      $ID = $row['id'];
      $Server = $row['server_ip'];
      $Player = $row['name'];
      $SteamID = $row['auth'];
      $Time = $row['connect_time'];
      $Date = $row['connect_date'];
      $Method = $row['connect_method'];
      $NUMPlayers = $row['numplayers'];
      $Map = $row['map'];
      $Duration = $row['duration'];
      $Flags = $row['flags'];
      $IP = $row['ip'];
      $City = $row['city'];
      $Region = $row['region'];
      $Country = $row['country'];
      $CCode = $row['country_code'];
      $CCode3 = $row['country_code3'];
      $Premium = $row['premium'];
      $MOTD = $row['html_motd_disabled'];
      $OS = $row['os'];
      $Internet="serverbrowser_internet";
      $Favorites="serverbrowser_favorites";
      $Steam="steam";
      $Friends="serverbrowser_friends";
      $Playtime=$row['total'];
    };

$alt_accounts = mysqli_query($con,"SELECT name, auth, connect_time,
  COUNT(auth) AS visits, SUM(duration) AS duration FROM
  (SELECT *  FROM player_analytics WHERE ip='$IP' AND auth!='$SteamID' ORDER BY connect_time DESC)
  AS player_analytics");

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php echo "$Player - Player Analytics"; ?></title>

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
            <h1>Player Analytics <small><?php echo "$Player"; ?></small></h1>
            <ol class="breadcrumb">
                <li> <a href="index.php">Dashboard</a></li>
                <li><a href="players.php">Players</a></li>
                <li class="active"><?php echo "$Player"; ?></li>
            </ol>
          </div>
        </div><!-- /.row -->

        <div class="row">
          <div class="column">
            <div class="col-lg-3">
              <div class="panel panel-primary">
                <div class="panel-heading">
                  <h3 class="panel-title"><i class="fa fa-user"></i> Player</h3>
                </div>
                <div class="profile-info">
                  <img class="<?php echo(PlayerStatus($data['personastate'])); ?>" src="<?php echo($data['avatarfull']); ?>">
                  <h3><a href="<?php echo $data['profileurl'];?>"><?php echo "$Player"; ?></a></h3>
                  <h5><?php echo(ConnLocation($City,$Region,$CCode3,$Country)); ?></h5>
                </div>
                <div>
                  <table class="table profile">
                    <tr><td class="left">ID</td><td class="right"><?php echo "$SteamID"; ?></td></tr>
                    <tr><td class="left">IP</td><td class="right"><?php echo "$IP"; ?></td></tr>
                    <tr><td class="left">Flags</td><td class="right"><?php echo(ConnFlags($Flags)); ?></td></tr>
                    <tr><td class="left">Premium</td><td class="right"><?php echo(ConnPremium($Premium)); ?></td></tr>
                    <tr><td class="left">MOTD</td><td class="right"><?php echo(ConnMOTD($MOTD)); ?></td></tr>
                    <tr><td class="left">OS</td><td class="right"><?php echo(ConnOS($OS)); ?></td></tr>
                    <tr><td class="left">Playtime</td><td class="right"><?php echo(ConvertMin($Playtime));?></td></tr>
                  </table>
                </div>
              </div>
            </div>
            <?php if(mysqli_fetch_array($alt_accounts)[0]['auth'])
            {
                // if the first result is empty, there are no alt accounts so dont make the table.
                // we then need to seek back to the 0th array position because num_rows doesn't
                // work in this situation; because our query always returns at least 1 row.
                mysqli_data_seek($alt_accounts, 0);
            ?>
            <div class="col-lg-9">
              <div class="panel panel-primary">
                <div class="panel-heading">
                  <h3 class="panel-title"><i class="fa fa-user"></i> Other Accounts at this IP</h3>
                </div>
                <div class="table-responsive">
                  <table id="alt_accounts" class="table table-striped table-bordered table-condensed table-tablesorter">
                    <thead>
                      <tr>
                        <th style="text-align:left" class="default">Name</th>
                        <th style="text-align:left" class="default">Playtime</th>
                        <th style="text-align:left" class="default">Visits</th>
                        <th style="text-align:right" class="default">Last Seen</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      while($row = mysqli_fetch_array($alt_accounts))
                      {
                          $Player   = $row['name'];
                          $SteamID  = $row['auth'];
                          $Time     = $row['connect_time'];
                          $Visits   = $row['visits'];
                          $Duration = $row['duration'];
                      ?>
                      <tr>
                          <td style="text-align:left"><a href="<?php echo "player.php?id=$SteamID";?>"><?php echo "$Player"; ?></a></td>
                          <td style="text-align:left"><?php echo ConvertMin($Duration) ?></td>
                          <td style="text-align:left"><?php echo $Visits ?></td>
                          <td style="text-align:right"><?php echo date('m/d/y g:i a', $Time);?></td>
                      </tr>
                   <?php }; ?>
                    </tbody>
                  </table>
                </div>
            </div>
          </div><!-- /.column -->
          <?php }; ?>
          <div class="col-lg-9">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-link"></i> Connections (30 day)</h3>
              </div>
              <div class="table-responsive">
                <table id="player" class="table table-striped table-bordered table-condensed table-tablesorter">
                  <thead>
                    <tr>
                      <th style="text-align:left" class="default">Name</th>
                      <th style="text-align:left" class="default">Server</th>
                      <th style="text-align:left" class="default">Method</th>
                      <th style="text-align:right" class="default">Time</th>
                      <th style="text-align:right" class="default">Duration</th>
                      <th style="text-align:center" class="default"><i class="fa fa-users"></i></th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
            while($row = mysqli_fetch_array($connections))
              {
                $ID = $row['id'];
                $Server = $row['server_ip'];
                $Player = $row['name'];
                $SteamID = $row['auth'];
                $Time = $row['connect_time'];
                $Date = $row['connect_date'];
                $Method = $row['connect_method'];
                $NUMPlayers = $row['numplayers'];
                $Map = $row['map'];
                $Duration = $row['duration'];
                $Flags = $row['flags'];
                $IP = $row['player_ip'];
                $City = $row['city'];
                $Region = $row['region'];
                $Country = $row['country'];
                $CCode = $row['country_code'];
                $CCode3 = $row['country_code3'];
                $Premium = $row['premium'];
                $MOTD = $row['html_motd_disabled'];
                $OS = $row['os'];
                $Internet="serverbrowser_internet";
                $Favorites="serverbrowser_favorites";
                $Steam="steam";
                $Friends="serverbrowser_friends";
                $History="serverbrowser_history";
                $LAN="serverbrowser_lan";
                $Redirect="redirect";
                $ServerName = $row['servername'];
                $MaxPlayers = $row['max'];
          ?>
                    <tr>
                      <td style="text-align:left"><?php echo "$Player"; ?></td>
                      <td style="text-align:left"><a href="server.php?server=<?php echo $Server;?>"><?php if($ServerName == NULL) {echo "$Server";} else{echo "$ServerName";} ?></a></td>
                      <td style="text-align:left"><?php echo(ConnMethod($Method)); ?></td>
                      <td style="text-align:right"><?php echo date('m/d/y g:i a', $Time);?></td>
                      <td style="text-align:right"><?php if ($Duration==NULL) {echo "<i style='color:#3498db' class='fa fa-refresh fa-spin' title='Connected'></i>";} else {echo(ConvertMin($Duration));} ?></td>
                      <td style="text-align:center"><?php echo "$NUMPlayers/$MaxPlayers" ?></td>
                    </tr>
          <?php
            };
          ?>
                  </tbody>
                </table>
              </div>
            </div>
            <?php
              echo "<ul class='pager'>";
              if ($currentpage <= 1){
                echo "<li class='previous disabled'><a><i class='fa fa-angle-left'></i> Previous</a></li>";
              }
              else {
                echo "<li class='previous'><a href='player.php?id=$id&page=$prevpage'><i class='fa fa-angle-left'></i> Previous</a></li>";
              }
              echo "<li style='font-size:18px'>$currentpage/$totalpages</li>";
              if ($currentpage != $totalpages){
                echo "<li class='next'><a href='player.php?id=$id&page=$nextpage'>Next <i class='fa fa-angle-right'></i></a></li>";
              }
              else {
                echo "<li class='next disabled'><a>Next <i class='fa fa-angle-right'></i></a></li>";
              }
              echo "</ul>";
            ?>
          </div>
        </div><!-- /.row -->
      </div><!-- /#page-wrapper -->
    </div><!-- /#wrapper -->

    <!-- Bootstrap core JavaScript -->
    <script src="js/jquery/jquery-1.11.0.min.js"></script>
    <script src="js/bootstrap/bootstrap.min.js"></script>
    <!-- Page Specific Plugins -->
    <script src="js/tablesorter/jquery.tablesorter.min.js"></script>
    <script type="text/javascript">
      $(function() {
        $('.tip').tooltip();
    });
        $(document).ready(function() {
          $("#player").tablesorter({
            headers: {
              0: {
                sorter:  false
              },
              3: {
                sorter: 'digit'
              },
              4: {
                sorter: 'digit'
              },
              5: {
                sorter: 'digit'
              }
            },
            sortList: [[3,1]]
          });
        });
    </script>
  </body>
</html>
<?php
}
?>
