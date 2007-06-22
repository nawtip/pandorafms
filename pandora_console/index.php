<?php

// Pandora FMS - the Free Monitoring System
// ========================================
// Copyright (c) 2004-2007 Sancho Lerena, slerena@gmail.com
// Main PHP/SQL code development, project architecture and management.
// Copyright (c) 2004-2007 Raul Mateos Martin, raulofpandora@gmail.com
// CSS and some PHP code additions
// Copyright (c) 2006 Jose Navarro <jnavarro@jnavarro.net>
// Additions to code for Pandora FMS 1.2 graph code

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation for version 2.
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

// Pandora FMS 1.x uses icons from famfamfam, licensed under CC Atr. 2.5
// Silk icon set 1.3 (cc) Mark James, http://www.famfamfam.com/lab/icons/silk/
// Pandora FMS 1.x uses Pear Image::Graph code

//Pandora Version, if not defined here it would take from config.php
$build_version="PC070622";
$pandora_version="v1.3 Beta 1";
global $build_version;
global $pandora_version;

// Set to 1 to do not check for installer or config file (for development!).
$develop_bypass = 0;

if ($develop_bypass != 1){
	// If no config file, automatically try to install
	if (! file_exists("include/config.php")){
		if (!file_exists("install.php")){
			include "general/error_install.php";
			exit;
		} else
			include ("install.php");
		exit;
	}
	// Check for installer presence
	if (file_exists("install.php")){
		include "general/error_install.php";
		exit;
	}
	// Check perms for config.php
	if ((substr(sprintf('%o', fileperms('include/config.php')), -4) != "0600") &&
	    (substr(sprintf('%o', fileperms('include/config.php')), -4) != "0660") &&
	    (substr(sprintf('%o', fileperms('include/config.php')), -4) != "0640") &&
	    (substr(sprintf('%o', fileperms('include/config.php')), -4) != "0600"))
	{
		include "general/error_perms.php";
		exit;
	}
}

// Real start
session_start();
include "include/config.php";
include "include/languages/language_".$language_code.".php";
require "include/functions.php"; // Including funcions.
require "include/functions_db.php";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<?php
// Refresh page
if ( (isset ($_GET["refr"])) || (isset($_POST["refr"])) ){
	if (isset ($_GET["refr"]))
		$intervalo = entrada_limpia ($_GET["refr"]);
	if (isset ($_POST["refr"]))
		$intervalo = entrada_limpia ($_POST["refr"]);
	if ($intervalo > 0){
		// Agent selection filters and refresh
		$query = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . '&refr=' . $intervalo;
		if (isset ($_POST["ag_group"])) {
			$ag_group = $_POST["ag_group"];
			$query = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . '&ag_group_refresh=' . $ag_group;
		} else 
			echo '<meta http-equiv="refresh" content="' . $intervalo . '; URL=' . $query . '">';
	}
}
?>
<title>Pandora FMS - <?php echo $lang_label["header_title"]; ?></title>
<meta http-equiv="expires" content="0">
<meta http-equiv="content-type" content="text/html; charset=utf8">
<meta name="resource-type" content="document">
<meta name="distribution" content="global">
<meta name="author" content="Sancho Lerena, Raul Mateos">
<meta name="copyright" content="This is GPL software. Created by Sancho Lerena and others">
<meta name="keywords" content="pandora, monitoring, system, GPL, software">
<meta name="robots" content="index, follow">
<link rel="icon" href="images/pandora.ico" type="image/ico">
<?php
// Pandora FMS 1.3 custom style selection
	echo '<link rel="stylesheet" href="include/styles/'.$config_style.'.css" type="text/css">';
?>
<script type='text/JavaScript' src='include/javascript/calendar.js'></script>
<script type="text/javascript" src="include/javascript/wz_jsgraphics.js"></script>
</head>

<?php
	// Show custom background
	echo '<body bgcolor="#555555">';
	$REMOTE_ADDR = getenv ("REMOTE_ADDR");
   	global $REMOTE_ADDR;

        // Login process 
   	if ( (! isset ($_SESSION['id_usuario'])) AND (isset ($_GET["login"]))) {
		$nick = entrada_limpia ($_POST["nick"]);
		$pass = entrada_limpia ($_POST["pass"]);
		
		// Connect to Database
		$sql1 = 'SELECT * FROM tusuario WHERE id_usuario = "'.$nick.'"';
		$result = mysql_query ($sql1);
		
		// For every registry
		if ($row = mysql_fetch_array ($result)){
			if ($row["password"] == md5 ($pass)){
				// Login OK
				// Nick could be uppercase or lowercase (select in MySQL
				// is not case sensitive)
				// We get DB nick to put in PHP Session variable,
				// to avoid problems with case-sensitive usernames.
				// Thanks to David Muñiz for Bug discovery :)
				$nick = $row["id_usuario"];
				unset ($_GET["sec2"]);
				$_GET["sec"] = "general/logon_ok";
				update_user_contact ($nick);
				logon_db ($nick, $REMOTE_ADDR);
				$_SESSION['id_usuario'] = $nick;
				
			} else {
				// Login failed (bad password)
				unset ($_GET["sec2"]);
				include "general/logon_failed.php";
				// change password to do not show all string
				$primera = substr ($pass,0,1);
				$ultima = substr ($pass, strlen ($pass) - 1, 1);
				$pass = $primera . "****" . $ultima;
				audit_db ($nick, $REMOTE_ADDR, "Logon Failed",
					  "Incorrect password: " . $nick . " / " . $pass);
				exit;
			}
		}
		else {
			// User not known
			unset ($_GET["sec2"]);
			include "general/logon_failed.php";
			$primera = substr ($pass, 0, 1);
			$ultima = substr ($pass, strlen ($pass) - 1, 1);
			$pass = $primera . "****" . $ultima;
			audit_db ($nick, $REMOTE_ADDR, "Logon Failed",
				  "Invalid username: " . $nick . " / " . $pass);
			exit;
		}
	} elseif (! isset ($_SESSION['id_usuario'])) {
		// There is no user connected
		include "general/login_page.php";
		exit;
	}

	// Log off
	if (isset ($_GET["bye"])) {
		include "general/logoff.php";
		$iduser = $_SESSION["id_usuario"];
		logoff_db ($iduser, $REMOTE_ADDR);
		session_unregister ("id_usuario");
		exit;
	}
	$pagina = "";
	if (isset ($_GET["sec2"])){
		$sec2 = parametro_limpio ($_GET["sec2"]);
		$pagina = $sec2;
	} else
		$sec2 = "";
		
	if (isset ($_GET["sec"])){
		$sec = parametro_limpio ($_GET["sec"]);
		$pagina = $sec2;
	}
	else
		$sec = "";
	// http://es2.php.net/manual/en/ref.session.php#64525
	// Session locking concurrency speedup!
	session_write_close(); 
?>

<div id="container">  
	<div id='head'>
		<?php require("general/header.php"); ?>
	</div>
	<div id="page">
		<div id="menu">
			<?php require ("general/main_menu.php"); ?>
		</div>
		<div id="main" style="background: url(images/backgrounds/<?php echo $config_bgimage; ?>)" >
			<?php
				// Page loader / selector
				if ($pagina != ""){
					if (file_exists ($pagina . ".php")) {
						require ($pagina . ".php");
					} else {
						echo "<br><b class='error'>Sorry! I can't find the page!</b>";
					}	
				} else
					require ("general/logon_ok.php");  //default
			?>
		</div>
		<div style="clear:both"></div>
	</div>
	<div id="foot">
		<?php require("general/footer.php") ?>
	</div>
</div>

</body>
</html>
