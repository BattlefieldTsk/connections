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


class MapSessions extends Analytics
{
    private $mTotal; // map total users
    private $mTimeAvg; // map average users

    /**
    *
    *  Gets the total number of sessions per map
    *
    * @return string Number of users
    */
    public function mapTotal($limit = null) {
        if (is_null($mTotal)) {
            $this->_mapAvgs();
        }
        return json_encode($this->mTotal);
    }

    /**
    *
    *  Sets the total session count per map
    */
    private function _mapAvgs() {
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
            $q = "SELECT map, COUNT(auth) AS players, SUM(duration) AS total
            FROM player_analytics 
            WHERE connect_date BETWEEN '$to' AND '$from'
            GROUP BY map
            ORDER BY players DESC";
            $total = ConvertMin($total);
            $this->mTotal = $this->db->query($q,true);
        }
    }
}
?>