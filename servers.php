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
require_once('data/servers.php');
$db = new DatabaseCon();
$server = new ServerSessions($db);
?>
<?php

$con=mysqli_connect(PA_DATABASE_SERVER, PA_DATABASE_USER, PA_DATABASE_PASSWORD, PA_DATABASE_NAME);
// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

$A_ = mysqli_query($con,"SELECT server_ip, COUNT(server_ip) AS total, SUM(duration) 
  AS playtime, servers.ip AS ip, servers.server_name AS servername, servers.player_limit AS max, servers.server_id AS server_id
  FROM player_analytics
  LEFT JOIN servers
  ON player_analytics.server_ip=servers.ip
  GROUP BY server_ip ORDER BY server_ip DESC");

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php echo "Servers - Player Analytics" ?></title>
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
            <h1>Player Analytics <small>Servers</small></h1>
            <ol class="breadcrumb">
              <li><a href="index.php">Dashboard</a></li>
              <li class="active">Servers</li>
            </ol>
          </div>
        </div><!-- /.row -->
<?php  
  
        // check for a successful form post  
        if (isset($_GET['s'])) echo "<div class=\"alert alert-success\">".$_GET['s']."</div>";  
  
        // check for a form error  
        elseif (isset($_GET['e'])) echo "<div class=\"alert alert-danger\">".$_GET['e']."</div>";  
  
?>

        <!-- Add Server -->
        <div class="modal fade" id="addserver" tabindex="-1" role="dialog" aria-labelledby="addServer" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="Add">Add Server Details</h4>
              </div>
              <div class="modal-body">
                <div class="row">
                  <form action="include/addserver.php" method="POST">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Server Name</label>
                            <input type="text" class="form-control" placeholder="Server Name" name="name">
                        </div>
                        <div class="form-group has-success">
                            <label>Server IP</label>
                            <input type="text" class="form-control" id="addserverIP" name="ip">
                        </div>
                        <div class="form-group">
                            <label>Player Limit</label>
                            <input type="number" class="form-control" placeholder="0-128" maxlength="3" name="max">
                        </div>
                    </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <input type="hidden" name="save" value="addserver">
                <button type="submit" class="btn btn-primary">Add</button>
              </div>
                </form>
            </div>
          </div>
        </div>

        <!-- Modify Server -->
        <div class="modal fade" id="modify" tabindex="-1" role="dialog" aria-labelledby="modify" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="Modify">Modify Server Details</h4>
              </div>
              <div class="modal-body">
                <div class="row">
                  <div class="col-lg-12">
                    <form action="include/modifyserver.php" method="POST">
                      <div class="form-group has-warning">
                          <label>Server Name</label>
                          <input type="text" class="form-control" name="name" id="serverName">
                          <label>Server IP</label>
                          <input type="text" class="form-control" name="ip" id="modifyserverIP">
                          <label>Max Players</label>
                          <input type="number" class="form-control" name="max" maxlength="3" id="serverMax">
                          <input type="hidden" class="form-control" name="id" id="serverID">
                      </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <input type="hidden" name="save" value="modifyserver">
                <button type="submit" class="btn btn-warning">Modify</button>
              </div>
                </form>
            </div>
          </div>
        </div>

        <!-- Delete Server -->
        <div class="modal fade" id="delete" tabindex="-1" role="dialog" aria-labelledby="delete" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="Delete">Delete Server Details</h4>
              </div>
              <div class="modal-body">
                <div class="row">
                  <div class="col-lg-12">
                    <form action="include/deleteserver.php" method="POST">
                      <div class="form-group">
                        <div class="alert alert-danger">
                          Are you sure you wish to delete this servers details?
                        </div>
                        <input type="text" class="form-control" name="name" id="deleteserverName" readonly>
                        <input type="hidden" class="form-control" name="id" id="deleteserverID">
                      </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <input type="hidden" name="save" value="deleteserver">
                <button type="submit" class="btn btn-danger">Delete</button>
              </div>
                </form>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-hdd-o"></i> Servers</h3>
                
              </div>
              <div class="table-responsive">
                <table id="servers" class="table table-striped table-bordered table-condensed table-tablesorter">
                  <thead>
                    <th width="33%"style="text-align:left">Server</th>
                    <th style="text-align:center">IP</th>
                    <th style="text-align:center">Max Players</th>
                    <th style="text-align:center">Total Connections</th>
                    <th style="text-align:center">Total Playtime</th>
                    <th style="text-align:center">Avg Playtime</th>
                    <?php 
                      if($session->isAdmin()){
                       echo "<th style=\"text-align:center\">Add/Delete</th>";
                      }
                     ?>
                  </thead>
                  <tbody>
                  <?php
            while($row = mysqli_fetch_array($A_))
              {
                $Server = $row['server_ip'];
                $total = $row['total'];
                $Playtime = $row['playtime'];
                $ServerName = $row['servername'];
                $MaxPlayers = $row['max'];
                $ID = $row['server_id'];
                $IP = $row['ip'];

          ?>
                  <tr>
                    <td style="text-align:left"><?php echo "<a href=\"server.php?server=$Server\">"?><?php if($ServerName == NULL) {echo "$Server";} else{echo "$ServerName";} ?></a></td>
                    <td style="text-align:center"><?php echo "<a href=\"server.php?server=$Server\">"?><?php echo "$Server"; ?></a></td>
                    <td style="text-align:center"><?php echo "$MaxPlayers" ?></td>
                    <td style="text-align:center"><?php echo "$total" ?></td>
                    <td style="text-align:center"><?php echo(ConvertMin($Playtime)); ?></td>
                    <?php if($Playtime == NULL){$Playtime=0;} else{$Playtime=($Playtime/$total);} ?>
                    <td style="text-align:center"><?php echo(ConvertMin($Playtime)); ?></td>
                    <?php 
                      if($session->isAdmin()){
                        echo "<td style=\"text-align:center\">";
                        if($ServerName == NULL) { echo "<a data-ip=\"$Server\" title=\"Add Server\" class=\"server btn btn-xs btn-block btn-success\" href=\"#addserver\">Add Server</a>";}
                        else{ echo "<a data-id=\"$ID\" data-ip=\"$IP\" data-name=\"$ServerName\" data-max=\"$MaxPlayers\" title=\"Modify Existing Server\" class=\"server btn btn-xs btn-warning\" href=\"#modify\">Modify</a>
                          <a data-id=\"$ID\" data-name=\"$ServerName\" title=\"Delete Existing Server\" class=\"server btn btn-xs btn-danger\" href=\"#delete\">Delete</a>";}
                        echo "</td>";
                      }
                     ?>
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
        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-bar-chart-o"></i> Server Statistics (30 day)</h3>
              </div>
              <div id="Statistics" class="collapse in">
                <div class="panel-body">
                  <div id="morris-chart-bar" style="height:300px"></div>
                </div>
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
        $("#servers").tablesorter({
          headers: {
            3: {
              sorter: 'digit'
            }
          },
          sortList: [[3,1]]
        });
      });
    </script>
    <script type="text/javascript">

Morris.Bar ({
  element: 'morris-chart-bar',
  data: <?php echo $server->monthlyTotal(5); ?>,
  xkey: 'server_ip',
  ykeys: ['total'],
  labels: ['Sessions'],
  barRatio: 0.4,
  hideHover: 'auto',
});

    </script>
    <script>

$(document).on("click", ".server", function (e) {

  e.preventDefault();

  var _self = $(this);

  var serverIP = _self.data('ip');
  var serverName = _self.data('name');
  var serverMax = _self.data('max');
  var serverID = _self.data('id');
  $("#addserverIP").val(serverIP);
  $("#modifyserverIP").val(serverIP);
  $("#serverName").val(serverName);
  $("#serverMax").val(serverMax);
  $("#serverID").val(serverID);
  $("#deleteserverID").val(serverID);
  $("#deleteserverName").val(serverName);

  $(_self.attr('href')).modal('show');
});

    </script>
  </body>
</html>
<?php
}
?>