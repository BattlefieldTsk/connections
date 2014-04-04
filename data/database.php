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

class DatabaseCon
{
    
    public $mysqli; // Connection
    public $query_count;

    /**
     * Creates initial database connection using config.php params
     *
     */
    public function __construct() 
    {
        $this->query_count = 0;
        $this->mysqli = new mysqli(PA_DATABASE_SERVER, PA_DATABASE_USER, PA_DATABASE_PASSWORD, PA_DATABASE_NAME);
        if ($this->mysqli->connect_error) {
            die('Connect Error (' . $this->mysqli->connect_errno . ') ' . $this->mysqli->connect_error);
        }
    }
    function __destruct()
    {
        $this->mysqli->close() or die("Error closing connection: " . $this->mysqli->error);
    }

    /**
     *
     * @param string SQL query
     * @param bool Return all results
     *
     * @return array result (assoc)
     */
    public function query($query, $all = false)
    {
        $result = $this->mysqli->query($query) or die('Query Error: ' . $this->mysqli->error . ' :\n' . $query);
        $this->query_count++;

        if ($result)
        {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $ret[] =  $row;
            }
            if ($all) {
                return (isset($ret) ? $ret : false);
            }
            else {
                return (isset($ret) ? $ret[0] : false);
            }
        }
    }

}

?>