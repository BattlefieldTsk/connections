<?php

$con=mysqli_connect(PA_DATABASE_SERVER, PA_DATABASE_USER, PA_DATABASE_PASSWORD, PA_DATABASE_NAME);
// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

$S_total = mysqli_query($con,"SELECT COUNT(DISTINCT server_ip) AS total FROM player_analytics
      WHERE connect_date BETWEEN DATE_FORMAT(NOW() - INTERVAL 30 DAY, '%Y-%m-%d') AND DATE_FORMAT(NOW(), '%Y-%m-%d')");
        $S_total = mysqli_fetch_array($S_total);
$S_ = mysqli_query($con,"SELECT server_ip, COUNT(DISTINCT auth) 
    AS total, servers.ip, servers.server_name AS servername 
    FROM player_analytics
    LEFT JOIN servers
    ON player_analytics.server_ip=servers.ip
    WHERE connect_date BETWEEN DATE_FORMAT(NOW() - INTERVAL 30 DAY, '%Y-%m-%d') AND DATE_FORMAT(NOW(), '%Y-%m-%d')
    GROUP BY server_ip ORDER BY total DESC LIMIT 0,5");

if (isset($_GET['server'])) {
  $sql = mysqli_query($con,"SELECT server_name FROM servers WHERE ip='$IP'");
    $sname = mysqli_fetch_array($sql);
    $sname = $sname['server_name'];
}

mysqli_close($con);
?>
      <!-- Sidebar -->
  <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container-fluid">
      <!-- Brand and toggle get grouped for better mobile display -->
      <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="index.php">Player Analytics</a>
      </div>

      <!-- Collect the nav links, forms, and other content for toggling -->
      <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
          <ul class="nav navbar-nav navbar-right navbar-user">
            <li class="dropdown user-dropdown hidden-lg hidden-md">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bars"></i> Menu<b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li <?php echo Active("index"); ?>><a href="index.php">Overview</a></li>
                <li class="divider"></li>
                <li <?php echo Active("player"); ?>><a href="players.php">Players</a></li>
                <li class="divider"></li>
                <li <?php echo Active("session"); ?>><a href="sessions.php">Sessions</a></li>
                <li class="divider"></li>
                <li <?php echo Active("server"); ?>><a href="servers.php">Servers</a></li>
                <li class="divider"></li>
                <li <?php echo Active("region"); ?>><a href="regions.php">Regions</a></li>
                <li class="divider"></li>
                <li <?php echo Active("map"); ?>><a href="maps.php">Maps</a></li>
                <li class="divider"></li>
              </ul>
            </li>
            <li class="dropdown messages-dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <span class="badge badge-color"><?php echo $S_total['total']; ?></span> Servers
                <b class="caret"></b>
              </a>
              <ul class="dropdown-menu">
              <?php
                  while($row = mysqli_fetch_array($S_))
                    {
                      $Server = $row['server_ip'];
                      $total = $row['total'];
                      $ServerName = $row['servername'];
              ?>
                  <?php
                      echo "<li>";
                      echo    "<a href='server.php?server=$Server'>";
                      echo        "<div>";
                      echo            "<p>";
                                        if($ServerName == NULL) {echo "$Server";} else{echo "$ServerName";}
                      echo            "</p>";
                      echo            "<div>";
                      echo                "<span class='server-muted'>$Server</span>";
                      echo                "<span class='pull-right server-muted'><i class='fa fa-link'></i> $total</span>";
                      echo            "</div>";
                      echo        "</div>";
                      echo    "</a>";
                      echo "</li>";
                      echo "<li class='divider'></li>";
                  ?>
              <?php
                  };
              ?>
                <li>
                  <a class="text-center" href="servers.php">
                    <strong>See All Servers</strong>
                    <i class="fa fa-angle-right"></i>
                  </a>
                </li>
              </ul>
              <li class="dropdown user-dropdown" style="margin-right:10px">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> <?php echo $session->username; ?><b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="userinfo.php?user=<?php echo $session->username; ?>"><i class="fa fa-user"></i> Profile</a></li>
                  <?php 
                    if($session->isAdmin()){
                          echo "<li><a href=\"admin.php\"><i class=\"fa fa-gear\"></i> Admin Center</a></li>";
                       }
                   ?>
                  <li class="divider"></li>
                  <li><a href="include/process.php"><i class="fa fa-power-off"></i> Log Out</a></li>
                </ul>
              </li>
            </li>
      </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
  </nav>
  <div class="container-fluid">
      <div class="row">
        <div class="sidebar">
          <ul class="nav nav-sidebar">
            <li <?php echo Active("index"); ?>><a href="index.php">Overview</a></li>
            <li <?php echo Active("player"); ?>><a href="players.php">Players</a></li>
            <li class="divider"></li>
            <li <?php echo Active("session"); ?>><a href="sessions.php">Sessions</a></li>
            <li class="divider"></li>
            <li <?php echo Active("server"); ?>><a href="servers.php">Servers</a></li>
            <li class="divider"></li>
            <li <?php echo Active("region"); ?>><a href="regions.php">Regions</a></li>
            <li class="divider"></li>
            <li <?php echo Active("map"); ?>><a href="maps.php">Maps</a></li>
            <li class="divider"></li>
          </ul>
        </div>