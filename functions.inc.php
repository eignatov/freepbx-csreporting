<?php /* $Id: $ */
// Xavier Ourciere xourciere[at]propolys[dot]com
//
//This program is free software; you can redistribute it and/or
//modify it under the terms of the GNU General Public License
//as published by the Free Software Foundation; either version 2
//of the License, or (at your option) any later version.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.


if ( (isset($amp_conf['ASTVARLIBDIR'])?$amp_conf['ASTVARLIBDIR']:'') == '')
{
	$astlib_path = "/var/lib/asterisk";
}
else
{
	$astlib_path = $amp_conf['ASTVARLIBDIR'];
}


function csreporting_getconfig()
{
	#print_r($results);
	#die();
	require_once 'DB.php';

	$sql = "SELECT	cs_stats_sup_email,
									cs_stats_sup_ext,
									cs_stats_ext,
									cs_stats_min_rec,
									cs_stats_num_rec,
									cs_stats_min_rec_time,
									cs_stats_rec_direction,
									cs_stats_num_days,
									cs_stats_from_email,
									voicemailconf,
									cs_stats_num_days
					FROM		csreportingoptions
					LIMIT		1";
	$results= sql($sql, "getAll");
	$tmp = $results[0][4];
	$tmp = eregi_replace('"', '', $tmp);
	$tmp = eregi_replace('>', '', $tmp);
	$res = explode('<', $tmp);
	$results[0][] = trim($res[1]);
	$results[0][] = trim($res[0]);
	return $results[0];
}

function csreporting_saveconfig($c) {

	require_once 'DB.php';

	# clean up
	$cs_stats_sup_email = mysql_escape_string($_POST['cs_stats_sup_email']);
	$cs_stats_sup_ext_tmp = $_POST['cs_stats_sup_ext'];
	$cs_stats_sup_ext = '';
	$sup_ext_array = explode("\n",$cs_stats_sup_ext_tmp);
	$already_listed = array();
	$comma = '';
	foreach($sup_ext_array as $mem)
	{
		$mem = trim($mem);
		if((!in_array($mem,$already_listed)) && ($mem != ''))
		{
			$cs_stats_sup_ext .= $comma.$mem;
			$already_listed[] = $mem;
			$comma = ',';
		}
	}
	$cs_stats_sup_ext = mysql_escape_string($cs_stats_sup_ext);
	$cs_stats_ext_tmp = $_POST['cs_stats_ext'];
	$cs_stats_ext = '';
	$cs_ext_array = explode("\n",$cs_stats_ext_tmp);
	$already_listed = array();
	$comma = '';
	foreach($cs_ext_array as $mem)
	{
		$mem = trim($mem);
		if((!in_array($mem,$already_listed)) && ($mem != ''))
		{
			$cs_stats_ext .= $comma.$mem;
			$already_listed[] = $mem;
			$comma = ',';
		}
	}
	$cs_stats_ext = mysql_escape_string($cs_stats_ext);
	$cs_stats_min_rec = mysql_escape_string($_POST['cs_stats_min_rec']);
	$cs_stats_num_rec = mysql_escape_string($_POST['cs_stats_num_rec']);
	$cs_stats_min_rec_time = mysql_escape_string($_POST['cs_stats_min_rec_time']);
	$cs_stats_rec_direction = mysql_escape_string($_POST['cs_stats_rec_direction']);
	$cs_stats_num_days = mysql_escape_string($_POST['cs_stats_num_days']);
	$cs_stats_cron_day = mysql_escape_string($_POST['cs_stats_cron_day']);
	$voicemailconf = mysql_escape_string($_POST['voicemailconf']);
	$cs_stats_from_email = mysql_escape_string($_POST['cs_stats_from_email']);



	# Make SQL thing
	$sql = "UPDATE	csreportingoptions
					SET			cs_stats_sup_email = '$cs_stats_sup_email',
									cs_stats_sup_ext = '$cs_stats_sup_ext',
									cs_stats_ext = '$cs_stats_ext',
									cs_stats_min_rec = '$cs_stats_min_rec',
									cs_stats_num_rec = '$cs_stats_num_rec',
									cs_stats_min_rec_time = '$cs_stats_min_rec_time',
									cs_stats_rec_direction = '$cs_stats_rec_direction',
									cs_stats_num_days = '$cs_stats_num_days',
									cs_stats_cron_day = '$cs_stats_cron_day',
									cs_stats_from_email = '$cs_stats_from_email',
									voicemailconf = '$voicemailconf'
					LIMIT		1;";
	sql($sql);


//	needreload();
}
?>