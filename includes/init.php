<?php
#############################################################################
#	Written by Jeremy Jacobs
#	Fitness Plus Equipment Services, Inc.
#	http://www.FitnessRepairParts.com
#
#	This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
#	the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
#############################################################################

//this should not be called directly
if( basename($_SERVER['SCRIPT_NAME']) == "init.php")
{
	header("Location: " . "http://" . cl('http_host'));
	exit(0);
}


//Some initial things common to all pages before their main runs.
//error_reporting(2047);
require_once("common.php");
require_once("config.php");
require_once("common_file.php");
require_once("mail.php");
Init();
exit;

function Init()
{
	if(cl('debug'))
	{
		print "Debugging enabled.\n";
	}
	
	//call log database
	$GLOBALS['db'] = dbconnect(cl('db_host'),cl('db_user'),cl('db_pass'));
	if(cl('debug') && ($GLOBALS['db'] == ''))
	{
		print "Failed to connect to the database.\n";
	}
  
	Main();
	exit(0);
}
?>