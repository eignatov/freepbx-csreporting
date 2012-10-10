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
if( basename($_SERVER['SCRIPT_NAME']) == "config.php")
{
	header("Location: " . "http://" . $_SERVER['HTTP_HOST']);
	exit(0);
}

//Database Settings
require_once 'DB.php';

define("AMP_CONF", "/etc/amportal.conf");

$amp_conf = parse_amportal_conf(AMP_CONF);
if (count($amp_conf) == 0)
{
	fatal("FAILED");
}

function parse_amportal_conf($filename)
{
	$file = file($filename);
	foreach ($file as $line)
	{
		if (preg_match("/^\s*([a-zA-Z0-9_]+)\s*=\s*(.*)\s*([;#].*)?/",$line,$matches))
		{
			$conf[ $matches[1] ] = $matches[2];
		}
	}
	return $conf;
}

//Database Settings
$GLOBALS['print_lookup_array']['db_host'] = $amp_conf['AMPDBHOST'];
$GLOBALS['print_lookup_array']['db_user'] = $amp_conf['AMPDBUSER'];
$GLOBALS['print_lookup_array']['db_pass'] = $amp_conf['AMPDBPASS'];

// asterisk's voicemail spool directory
$GLOBALS['print_lookup_array']['vboxspool'] = '/var/spool/asterisk/voicemail';
$GLOBALS['print_lookup_array']['context']   = 'default';
$GLOBALS['print_lookup_array']['boxbase']   = cl('vboxspool').'/'.cl('context');
$GLOBALS['print_lookup_array']['MAXMSG']    = "99";
$GLOBALS['print_lookup_array']['fileMode']  = "0660";
$GLOBALS['print_lookup_array']['dirMode']   = "2770";
?>