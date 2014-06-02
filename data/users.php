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

class UserSessions extends Analytics
{

    private $mTotal; // Monthly total users
    private $mTimeAvg; // Monthly average users
    private $pTotal; // Total Premium Users
    private $cmTotal; // Total Connect Methods of Users

    /**
    *
    *  Gets the total number of users total in a month (30d)
    *
    * @return string Number of users
    */
    public function monthlyTotal() {
        if (is_null($this->mTotal)) {
            $this->_monthlyAvgs();
        }
        return $this->mTotal;
    }

    /**
    *
    *  Sets the total monthly user count
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
            $q = "SELECT COUNT(DISTINCT auth) AS total FROM player_analytics WHERE ";
                if (isset($_GET["server"])){
                    $IP = $_GET['server'];
                    $q .= "server_ip='$IP' AND ";
                }
            $q .= "connect_date BETWEEN '$to' AND '$from'";
            $tmp = $this->db->query($q);
            $this->mTotal = $tmp['total'];
        }
        return $this->mTotal;
    }

    /**
    *
    *  Gets the total Premium/F2P user count
    */
    public function jsPremiumTotal() {
        if (is_null($this->pTotal)) {
            $this->_premiumTotal();
        }
        $total = 0;
        foreach ($this->pTotal as $n => $v) {
            $total += $v['total'];
        }
        foreach ($this->pTotal as $n => $v) {
            if ( $this->pTotal[$n]['premium']=='0') $this->pTotal[$n]['premium']='F2P';
            elseif ( $this->pTotal[$n]['premium']=='1') $this->pTotal[$n]['premium']='Premium';
                $ret[] = array(
                    //'country_code3' => $this->mAudience[$n]['country_code3'],
                    'label' => $this->pTotal[$n]['premium'],
                    'value' => number_format($this->pTotal[$n]['total'] * 100 / $total, 2)
                );
            }
            return json_encode($ret);
    }

    /**
    *
    *  Sets the total Premium/F2P user count
    */
    private function _premiumTotal() {
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
        $q = "SELECT premium, COUNT(premium) AS total FROM player_analytics WHERE ";
            if (isset($_GET["server"])){
                $IP = $_GET['server'];
                $q .= "server_ip='$IP' AND ";
            }
        $q .= "connect_date BETWEEN '$to' AND '$from' GROUP BY premium";

        $this->pTotal = $this->db->query($q,true);
    }

    /**
    *
    *  Gets the total Connect Method user count
    */
    public function jsMethodTotal() {
        if (is_null($this->cmTotal)) {
            $this->_MethodTotal();
        }
        $total = 0;
        foreach ($this->cmTotal as $n => $v) {
            $total += $v['total'];
        }
        foreach ($this->cmTotal as $n => $v) {
            if ( $this->cmTotal[$n]['connect_method']==NULL) $this->cmTotal[$n]['connect_method']='Console';
            elseif ( $this->cmTotal[$n]['connect_method']=='serverbrowser_favorites') $this->cmTotal[$n]['connect_method']='Favorites';
            elseif ( $this->cmTotal[$n]['connect_method']=='serverbrowser_friends') $this->cmTotal[$n]['connect_method']='Friends';
            elseif ( $this->cmTotal[$n]['connect_method']=='serverbrowser_history') $this->cmTotal[$n]['connect_method']='History';
            elseif ( $this->cmTotal[$n]['connect_method']=='serverbrowser_internet') $this->cmTotal[$n]['connect_method']='Browser';
            elseif ( $this->cmTotal[$n]['connect_method']=='steam') $this->cmTotal[$n]['connect_method']='Steam';
            elseif ( $this->cmTotal[$n]['connect_method']=='matchmaking') $this->cmTotal[$n]['connect_method']='Matchmaking';
            elseif (preg_match('/quickplay/', ( $this->cmTotal[$n]['connect_method']))) $this->cmTotal[$n]['connect_method']='Quickplay';
                $ret[] = array(
                    //'country_code3' => $this->mAudience[$n]['country_code3'],
                    'label' => $this->cmTotal[$n]['connect_method'],
                    'value' => number_format($this->cmTotal[$n]['total'] * 100 / $total, 2)
                );
            }
            return json_encode($ret);
    }

    /**
    *
    *  Sets the total Connect Method user count
    */
    private function _MethodTotal() {
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
        $q = "SELECT connect_method, COUNT(connect_method) AS total FROM player_analytics WHERE ";
            if (isset($_GET["server"])){
                $IP = $_GET['server'];
                $q .= "server_ip='$IP' AND ";
            }
        $q .= "connect_date BETWEEN '$to' AND '$from' GROUP BY connect_method";
        $this->cmTotal = $this->db->query($q,true);
    }
}