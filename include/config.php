<?php

define("BASEURL", "http://www.example.com/");

//Timezones https://php.net/manual/en/timezones.php
//Uncomment to set the timezone, leave commented to use webserver default:
//date_default_timezone_set('America/Chicago');

//Database Info
define("PA_DATABASE_SERVER",      'localhost');
define("PA_DATABASE_USER",        'root');
define("PA_DATABASE_PASSWORD",    '');
define("PA_DATABASE_NAME",        'player_analytics');

//Flags
//Replace the value with your flags, ConnFlags will check database for the specific value defined.
define("ROOT", 		'z');
define("ADMIN", 	'ef');
define("MOD", 		'b');
define("DONOR", 	'op');

//Profile Page or Player Page
if (isset($Profile)) {
    define("PROFILE", $Profile);
}
else {
	define("PROFILE", 0);
}

//You must add your Steam API Key to get Player Status, Player Profile Link.
const STEAM_APIKEY  = 'IF83MD8234NA84KNM39ADF82F4CZ9ZXC';

function ConnFlags($Flags)
    {
    	//This is the name as it will appear in Player Profile
    	if (PROFILE == 1){
			if (strpos($Flags, ROOT) !== false) {
				return "Root";
			}
			else if (strpos($Flags, ADMIN) !== false) {
				return "Admin";
			}
			else if (strpos($Flags, MOD) !== false) {
				return "MOD";
			}
			else if (strpos($Flags, DONOR) !== false) {
				return "Donor";
			}
			else if ($Flags == NULL) {
				return "None";
			}
			else {
				return "$Flags";
			}
		}
		//This is the ICON as it will appear in Players/Sessions page.
		//Change Icon, Icon Color, and Title to match your server setup.
		else 
		{
			if (strpos($Flags, 'z') !== false) {
				return "<i style='color:#e74c3c' class='tip fa fa-star' data-toggle='tooltip' title='Root'></i>";
			}
			else if (strpos($Flags, ADMIN) !== false) {
				return "<i style='color:#e67e22' class='tip fa fa-star' data-toggle='tooltip' title='Admin'></i>";
			}
			else if (strpos($Flags, MOD) !== false) {
				return "<i style='color:#f1c40f' class='tip fa fa-star-half-o' data-toggle='tooltip' title='Admin'></i>";
			}
			else if (strpos($Flags, DONOR) !== false) {
				return "<i style='color:#2ecc71' class='tip fa fa-usd' data-toggle='tooltip' title='Donor'></i>";
			}
			else if ($Flags == NULL) {
				return "<i style='color:#e74c3c' class='tip fa fa-ban' data-toggle='tooltip' title='None'></i>";
			}
			else 
			{
				return "$Flags";
			}
		}
    }

function ConnMethod($Method)
    {
      if ($Method == "serverbrowser_internet") {
        return "Browser";
      }
      else if ($Method == "serverbrowser_favorites") {
        return "Favorites";
      }
      else if ($Method == "steam") {
        return "Steam";
      }
      else if ($Method == "serverbrowser_friends") {
        return "Friends";
      }
      else if ($Method == "serverbrowser_history") {
        return "History";
      }
      else if ($Method == "serverbrowser_lan") {
        return "LAN";
      }
      else if ($Method == "redirect") {
        return "Redirect";
      }
      else if (preg_match("/quickplay/", $Method)) {
        return "Quickplay";
      }
      else if ($Method == "matchmaking") {
        return "Matchmaking";
      }
      else {
        return "Console";
      }
    }

function ConnLocation($City,$Region,$CCode3,$Country)
    {
    	if (PROFILE == 1) {
	    	if ($Country == NULL){
	    		return "<i class='fa fa-question'></i>";
			}
			else if  ($Region == NULL && $City == NULL){
				return "$Country";
			}
			else if  ($City == NULL){
				return "$Region, $CCode3";
			}
			else {
				return "$City, $Region, $CCode3";
			}
		}
		else {
			if ($CCode3 == NULL){
				return "<i class='fa fa-question'></i>";
			}
			else {
				return "$CCode3";
			}
			
		}
    }

function ConnPremium($Premium)
    {
    	if (PROFILE == 1) {
	    	if ($Premium == NULL){
	    		return "<i class='fa fa-question'></i>";
			}
			else if ($Premium == "1"){
				return "Yes";
			}
			else {
				return "No";
			}
		}
		else {
			if ($Premium == NULL){
				return "<i class='tip fa fa-question'>?</i>";
			}
			else if ($Premium == "1") {
				return "<i style='color:#1abc9c' class='tip fa fa-check' data-toggle='tooltip' title='Premium'></i>";
			}
			else {
				return "<i style='color:#e74c3c' class='tip fa fa-ban' data-toggle='tooltip' title='F2P'></i>";
			}
			
		}
    }

function ConnMOTD($MOTD)
    {
    	if (PROFILE == 1) {
	    	if ($MOTD == NULL){
	    		return "<i class='tip fa fa-question'>NULL</i>";
			}
			else if ($MOTD == "0"){
				return "HTML Enabled";
			}
			else {
				return "HTML Disabled";
			}
		}
		else {
			if ($MOTD == NULL){
				return "<i class='tip fa fa-question' data-toggle='tooltip' title='Null'></i>";
			}
			else if ($MOTD == "0") {
				return "<i style='color:#1abc9c' class='tip fa fa-check' data-toggle='tooltip' title='Enabled'></i>";
			}
			else {
				return "<i style='color:#e74c3c' class='tip fa fa-ban' data-toggle='tooltip' title='Disabled'></i>";
			}
			
		}
    }

function ConnOS($OS)
    {
    	if (PROFILE == 1) {
			if ($OS == "Windows"){
				return "Windows";
			}
			else if ($OS == "Linux"){
				return "Linux";
			}
			else if ($OS == "MacOS"){
				return "Mac";
			}
			else {
				return "<i class='fa fa-question'></i>";
			}
		}
		else {
			if ($OS == "Windows"){
				return "<i style='color:#3498db' class='tip fa fa-windows' data-toggle='tooltip' title='Windows'></i>";
			}
			else if ($OS == "Linux"){
				return "<i class='tip fa fa-linux' data-toggle='tooltip' title='Linux'></i>";
			}
			else if ($OS == "MacOS"){
				return "<i class='tip fa fa-apple' data-toggle='tooltip' title='Mac'></i>";
			}
			else {
				return "<i class='tip fa fa-question' data-toggle='tooltip' title='Null'></i>";
			}
			
		}
    }

function ConvertMin($Playtime)
  {
    $Playtime=($Playtime);
    $m = floor(($Playtime%3600)/60);
    $h = floor(($Playtime)/3600);


    if ($Playtime == NULL){
      return "<i class='fa fa-question'></i>";
    }
    else if ($h == 0){
      return "$m min.";
    }
	else {
      return "$h hr. $m min.";
    }

  }

function ConnRegion($City,$Region,$view,$Country)
{
	if ($view == "region"){
		return "<a href='region.php?view=$Region'>$Region</a>";
	}
	else if ($view == "city"){
		return "<a href='region.php?view=$City'>$City</a>";
	}
	else {
		return "<a href='region.php?view=$Country'>$Country</a>";
	}
}

function Active($requestUri)
{
    $current_file_name = basename($_SERVER['SCRIPT_FILENAME'], ".php");

    if ($current_file_name == strpos($current_file_name, $requestUri)){
        return 'class="active"';
    }
}
?>