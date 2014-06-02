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


class ServerSessions extends Analytics
{
    private $mTotal; // Monthly total users
    private $mTimeAvg; // Monthly average users

    /**
    *
    *  Gets the total number of sessions total in a month (30d)
    *
    * @return string Number of users
    */
    public function monthlyTotal($limit = null) {
        if (is_null($this->mTotal)) {
            $this->_monthlyAvgs();
        }
        return json_encode($this->mTotal);
    }

    /**
    *
    *  Sets the total monthly session count per server
    */
    private function _monthlyAvgs() {
        if (is_null($this->mTimeAvg)) {
            $q = "SELECT `server_ip`, `connect_date`,  COUNT(`server_ip`) AS `total`
            FROM `player_analytics` 
            WHERE `connect_date` BETWEEN DATE_FORMAT(NOW() - INTERVAL 30 DAY, '%Y-%m-%d') AND DATE_FORMAT(NOW(), '%Y-%m-%d')
            GROUP BY `server_ip`
            ORDER BY `total` DESC";
            $this->mTotal = $this->db->query($q,true);
        }
    }
}
?>