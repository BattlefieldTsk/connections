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

class PlayerSessions extends Analytics
{
    private $mTotal; // Monthly total sessions
    private $dTotal; // Monthly total sessions grouped by day
    private $mTimeAvg; // Monthly average session length
    private $mAudience; // Most frequent country 
    private $mAudienceRegion; // Most frequent country 
    private $mAudienceCity; // Most frequent country 
    private $hTotal; // Last 24h of sessions

    /**
    *
    * @param class Database class
    */
    function __construct(&$db)
    {
        $this->db = $db;
    }

    /**
    *
    *  Gets the total number of sessions total in a month (30d)
    *
    * @return string Number of sessions
    */
    public function monthlyTotal() {
        if (is_null($this->mTotal)) {
            $this->_monthlyAvgs();
        }
        return $this->mTotal;
    }

    /**
    *
    *  Gets the average sessions length for a month (30d)
    *
    * @return string Number of seconds
    */
    public function monthlyTimeAvg() {
        if (is_null($this->mTimeAvg)) {
            $this->_monthlyAvgs();
        }
        return $this->mTimeAvg;
    }

    /**
    *
    *  Sets the total monthly session count, and monthly average session length
    */
    private function _monthlyAvgs() {
        if(isset( $_GET['to'] ) && isset( $_GET['from'] )) {
              $to = ($_GET['to']);
              $from = ($_GET['from']);

              if (empty ($from) && empty ($to)){
                $from = date("Y-m-d");
                $to = date("Y-m-d", strtotime("-30 days"));
              }
            }
            else {
              $from = date("Y-m-d");
              $to = date("Y-m-d", strtotime("-30 days"));
            }
        if (is_null($this->mTimeAvg)) {
            $q = "SELECT COUNT(id) AS total, SUM(duration) / COUNT(id) AS avgTime FROM ";
            if (isset($_GET["server"])){
                $IP = $_GET['server'];
                $q .= "(SELECT * FROM player_analytics WHERE server_ip='$IP') AS";
            }
            $q .= "player_analytics WHERE connect_date BETWEEN '$to' AND '$from'";

            $tmp = $this->db->query($q);
            $this->mTotal = $tmp['total'];
            $this->mTimeAvg = $tmp['avgTime'];
        }
        return $this->hTotal;
    }

    /**
    *
    *  Gets the total number of sessions grouped by hour (24h)
    *
    * @return array
    */
    public function hourly() {
        if (is_null($this->hTotal)) {
            $this->_hourly();
        }
        while (count($this->hTotal) > 24) {
            array_shift($this->hTotal);
        }
        return $this->hTotal;
    }
    /**
    *
    *  Sets the new sessions per 1h period for the previous 24h
    */
    private function _hourly() {
        if (isset($_GET["server"])){
            $IP = $_GET['server'];
            if (is_null($this->hTotal)) {
                $q = "
                SELECT      DATE_FORMAT( FROM_UNIXTIME( `connect_time` ) , 
                            '%Y-%m-%d %H:00' ) AS  `time` , 
                            COUNT( `connect_time` ) AS  `total` 
                FROM (SELECT * FROM `player_analytics` WHERE `server_ip`='$IP') AS s
                WHERE       `connect_time` BETWEEN UNIX_TIMESTAMP(NOW()) - 90000 AND UNIX_TIMESTAMP(NOW())
                GROUP BY    HOUR( FROM_UNIXTIME( `connect_time` ) ) 
                ORDER BY    `time` ASC
                LIMIT       24";
                $this->hTotal = $this->db->query($q,true);
            }
        return $this->hTotal;
        }
        else{
            if (is_null($this->hTotal)) {
                $q = "
                SELECT      DATE_FORMAT( FROM_UNIXTIME( `connect_time` ) , 
                            '%Y-%m-%d %H:00' ) AS  `time` , 
                            COUNT( `connect_time` ) AS  `total` 
                FROM        `player_analytics` 
                WHERE       `connect_time` BETWEEN UNIX_TIMESTAMP(NOW()) - 90000 AND UNIX_TIMESTAMP(NOW())
                GROUP BY    HOUR( FROM_UNIXTIME( `connect_time` ) ) 
                ORDER BY    `time` ASC
                LIMIT       24";
                $this->hTotal = $this->db->query($q,true);
            }
        return $this->hTotal;
        }
    }

    /**
    *
    *  Gets the average sessions length for a month (30d)
    *
    * @return array Most popular to least popular audience by country
    */
    public function monthlyCountryAvg() {
        if (is_null($this->mAudience)) {
            $this->_monthlyCountryAvg();
        }
        return $this->mAudience;
    }

    /**
    *
    *  Gets the average sessions length for a month (30d)
    *
    * @return array Most popular to least popular audience by region
    */
    public function monthlyRegionAvg() {
        if (is_null($this->mAudienceRegion)) {
            $this->_monthlyRegionAvg();
        }
        return $this->mAudienceRegion;
    }

    /**
    *
    *  Gets the average sessions length for a month (30d)
    *
    * @return array Most popular to least popular audience by city
    */
    public function monthlyCityAvg() {
        if (is_null($this->mAudienceCity)) {
            $this->_monthlyCityAvg();
        }
        return $this->mAudienceCity;
    }

    /**
    *
    *  Gets the average sessions length for a month (30d)
    *
    * @param int Number of maximum results to return
    * @return string JSON data of monthlyCountryAvg
    */
    public function jsonMonthlyCountryAvg($limit = null) {
        if (is_null($this->mAudience)) {
            $this->_monthlyCountryAvg();
        }
        if ($limit)
            return json_encode(array_slice($this->mAudience, 0, $limit));
        else
            return json_encode($this->mAudience);
    }

    /**
    *
    *  Gets the average sessions length for a month (30d)
    *
    * @param int Number of maximum results to return
    * @return string JSON data of monthlyCountryAvg in percentages
    */
    public function jsonMonthlyCountryAvgPct($limit = null) {
        if (is_null($this->mAudience)) {
            $this->_monthlyCountryAvg();
        }
        $total = 0;
        foreach ($this->mAudience as $n => $v) {
            $total += $v['total'];
        }

        if (!is_null($limit) && $limit < count($this->mAudience)) { // Trimming some data off
            $pctused = 0;
            for ($i=0; $i < $limit -1; $i++) {  // Leave room for "Other"
                $pct = number_format($this->mAudience[$i]['total'] * 100 / $total, 2);
                $pctused += $pct;
                $ret[] = array(
                    //'country_code3' => $this->mAudience[$i]['country_code3'],
                    'label' => $this->mAudience[$i]['label'],
                    'value' => $pct
                );
            }
            $ret[] = array('label' => 'Other', 'value' => 100 - $pctused);
            return json_encode($ret);
        }
        else {
            foreach ($this->mAudience as $n => $v) {
                $ret[] = array(
                    //'country_code3' => $this->mAudience[$n]['country_code3'],
                    'label' => $this->mAudience[$n]['label'],
                    'value' => number_format($this->mAudience[$n]['total'] * 100 / $total, 2)
                );
            }
            return json_encode($ret);
        }
    }

    /**
    *
    *  Sets the most popular country
    */
    private function _monthlyCountryAvg() {
        if (is_null($this->mAudience)) {
            if(isset( $_GET['to'] ) && isset( $_GET['from'] )) {
              $to = ($_GET['to']);
              $from = ($_GET['from']);

              if (empty ($from) && empty ($to)){
                $from = date("Y-m-d");
                $to = date("Y-m-d", strtotime("-30 days"));
              }
            }
            else {
              $from = date("Y-m-d");
              $to = date("Y-m-d", strtotime("-30 days"));
            }
            $q = "SELECT country_code3, country AS label, COUNT(country_code3) AS total FROM ";
            if (isset($_GET["server"])) {
                $IP = $_GET['server'];
               "(SELECT * FROM player_analytics WHERE server_ip='$IP') AS ";
            }
            $q .= "player_analytics WHERE connect_date BETWEEN '$to' AND '$from' AND country_code3 IS NOT NULL
                   GROUP BY country_code3 ORDER BY total DESC";
            $this->mAudience = $this->db->query($q,true);
        }
        return $this->mAudience;
    }

    /**
    *
    *  Sets the most popular region
    */
    private function _monthlyRegionAvg() {
        if (is_null($this->mAudienceRegion)) {
            if(isset( $_GET['to'] ) && isset( $_GET['from'] )) {
              $to = ($_GET['to']);
              $from = ($_GET['from']);

              if (empty ($from) && empty ($to)){
                $from = date("Y-m-d");
                $to = date("Y-m-d", strtotime("-30 days"));
              }
            }
            else {
              $from = date("Y-m-d");
              $to = date("Y-m-d", strtotime("-30 days"));
            }
            $q = "SELECT region, COUNT(region) AS total FROM ";
            if (isset($_GET["server"])) {
                $IP = $_GET['server'];
               "(SELECT * FROM player_analytics WHERE server_ip='$IP') AS ";
            }
            $q .= "player_analytics WHERE connect_date BETWEEN '$to' AND '$from' AND region IS NOT NULL
                   GROUP BY region ORDER BY total DESC";
            $this->mAudienceRegion = $this->db->query($q,true);
        }
        return $this->mAudienceRegion;
    }

    /**
    *
    *  Sets the most popular city
    */
    private function _monthlyCityAvg() {
        if (is_null($this->mAudienceCity)) {
            if(isset( $_GET['to'] ) && isset( $_GET['from'] )) {
              $to = ($_GET['to']);
              $from = ($_GET['from']);

              if (empty ($from) && empty ($to)){
                $from = date("Y-m-d");
                $to = date("Y-m-d", strtotime("-30 days"));
              }
            }
            else {
              $from = date("Y-m-d");
              $to = date("Y-m-d", strtotime("-30 days"));
            }
            $q = "SELECT city, COUNT(city) AS total FROM ";
            if (isset($_GET["server"])) {
                $IP = $_GET['server'];
               "(SELECT * FROM player_analytics WHERE server_ip='$IP') AS ";
            }
            $q .= "player_analytics WHERE connect_date BETWEEN '$to' AND '$from' AND city IS NOT NULL
                   GROUP BY city ORDER BY total DESC";
            $this->mAudienceCity = $this->db->query($q,true);
        }
        return $this->mAudienceCity;
    }

    /**
    *
    * Gets the total number of sessions total in a month (30d)
    *
    * @return array Number of sessions by date
    */
    public function dailyTotal() {
        if (is_null($dTotal)) {
            $this->_dailyTotal();
        }
        return $this->dTotal;
    }

    /**
    *
    * Gets the total number of sessions total in a month (30d)
    *
    * @return string Number of sessions by date in a json object
    */
    public function jsDailyTotal() {
        if (is_null($this->dTotal)) {
            $this->_dailyTotal();
        }
        return json_encode($this->dTotal);
    }

    /**
    *
    * Sets the total number of sessions total in a month (30d)
    */
    private function _dailyTotal() {
        if(isset( $_GET['to'] ) && isset( $_GET['from'] )) {
          $to = ($_GET['to']);
          $from = ($_GET['from']);

          if (empty ($from) && empty ($to)){
            $from = date("Y-m-d");
            $to = date("Y-m-d", strtotime("-30 days"));
          }
        }
        else {
          $from = date("Y-m-d");
          $to = date("Y-m-d", strtotime("-30 days"));
        }
        $q = "SELECT connect_date, COUNT(id) AS total FROM ";
        if (isset($_GET["server"])){
            $IP = $_GET['server'];
            $q .= "(SELECT * FROM player_analytics WHERE server_ip='$IP') AS ";
        }
        if (isset($_GET["map"])){
            $Map = $_GET['map'];
            $q .= "(SELECT * FROM player_analytics WHERE map='$Map') AS ";
        }
        
        $q .= "player_analytics WHERE connect_date BETWEEN '$to' AND '$from' GROUP BY connect_date";
        $this->dTotal = $this->db->query($q,true);
    }
}

?>