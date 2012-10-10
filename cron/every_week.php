<?php
#############################################################################
#	Written by Jeremy Jacobs
#	Fitness Plus Equipment Services, Inc.
#	http://www.FitnessRepairParts.com
#
#	This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
#	the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
#############################################################################


$GLOBALS['print_lookup_array']['debug'] = false;

$path_parts = pathinfo($_SERVER["SCRIPT_FILENAME"]);
$root = str_replace('/cron','',$path_parts['dirname']);
require_once($root."/includes/init.php");

function Main()
{
	if(cl('debug'))
	{
		print '<pre>';
	}
	set_time_limit(600);
	$ignore_day = get_safe('ignore_day');
	
	//set up some global variables for use by functions
	//start with the variables assigned to this module
	$query = "SELECT * FROM asterisk.csreportingoptions";
	$query_result = mysql_query($query, $GLOBALS['db']);
	$query_array = mysql_fetch_array($query_result);
	foreach($query_array as $key=>$val)
	{
		if(!is_numeric($key))
		{
			$GLOBALS['print_lookup_array'][$key] = $val;
		}
	}
	
	//Because the FreePBX cron manager is designed to run scripts everyday, in order to use it, we have to limit the 
	//script to running only on the day we want it because we only want this to run once a week.
	if((cl('cs_stats_cron_day') == date('l')) || ($ignore_day == 'yes') || cl('debug'))
	{
		//locate the inbound lines
		$query = "SELECT DISTINCT extension FROM asterisk.incoming WHERE extension <> ''";
		$query_result = mysql_query($query, $GLOBALS['db']);
		while($query_array = mysql_fetch_array($query_result))
		{
			$GLOBALS['print_lookup_array']['cs_stats_inbound_lines'][] = $query_array[0];
		}
		
		//get the email address used for faxes on this system, if a different email address hasn't been specified
		if(cl('cs_stats_from_email') == '')
		{
			$query = "SELECT value FROM asterisk.globals WHERE variable = 'FAX_RX_FROM'";
			$query_result = mysql_query($query, $GLOBALS['db']);
			$query_array = mysql_fetch_array($query_result);
			$GLOBALS['print_lookup_array']['cs_stats_from_email'] = $query_array[0];
		}
		
		GenerateReviewData();
	}
	if(cl('debug'))
	{
		print '</pre>';
	}
}

//Add Open or Recently closed RA's to the PO page for receiving purposes.
function GenerateReviewData()
{	
	$filename = cl('voicemailconf');
	if(file_exists($filename))
	{
		parse_voicemailconf($filename, $vmconf, $section);
		if(cl('debug'))
		{
			print "voicemail.conf file returned the following information:\n".print_r($vmconf,true);
		}
	
		$valid_ext = explode(',',cl('cs_stats_ext'));
		
		foreach($vmconf['default'] as $val)
		{
			if(in_array($val['mailbox'],$valid_ext))
			{
				$inbound_time = 0;
				$inbound_calls = 0;
				$outbound_time = 0;
				$outbound_calls = 0;
				$call_array = array();
				$longer_calls = array();
				$long_call_count = 0;
				$query = "SELECT		UNIX_TIMESTAMP(calldate),
														clid,
														src,
														dst,
														duration
									FROM			asteriskcdrdb.cdr
									WHERE			calldate > DATE_SUB(NOW(),INTERVAL ".cl('cs_stats_num_days')." DAY)
									AND				channel LIKE '%/".$val['mailbox']."%'
									AND				disposition = 'ANSWERED'
									AND				(length(src) > 6
									OR				length(dst) > 6)
									ORDER BY	calldate";
				$query_result = mysql_query($query, $GLOBALS['db']);
				
				if(!mysql_num_rows($query_result))
				{
					if(cl('debug'))
					{
						print "No results returned from the database, here is a copy of the query that was run\n".$query."\n";
					}
				}
				else
				{
					if(cl('debug'))
					{
						print "Located ".mysql_num_rows($query_result)." rows in the database to process.\n";
					}
					
					while($query_array=mysql_fetch_array($query_result))
					{
						$call_dir = (in_array($query_array[1],cl('cs_stats_inbound_lines'))) ? 'outbound' : 'inbound';
						if(!isset($call_array[date("D M j, Y",$query_array[0])]))
						{
							$call_array[date("D M j, Y",$query_array[0])]['inbound_calls'] = 0;
							$call_array[date("D M j, Y",$query_array[0])]['inbound_time'] = 0;
							$call_array[date("D M j, Y",$query_array[0])]['outbound_calls'] = 0;
							$call_array[date("D M j, Y",$query_array[0])]['outbound_time'] = 0;
						}
						$call_array[date("D M j, Y",$query_array[0])]['time'] = $query_array[0];
						
						if($call_dir == 'outbound')
						{
							$call_array[date("D M j, Y",$query_array[0])]['outbound_calls']++;
							$call_array[date("D M j, Y",$query_array[0])]['outbound_time'] = $call_array[date("D M j, Y",$query_array[0])]['outbound_time'] + $query_array[4];
						}
						else
						{
							$call_array[date("D M j, Y",$query_array[0])]['inbound_calls']++;
							$call_array[date("D M j, Y",$query_array[0])]['inbound_time'] = $call_array[date("D M j, Y",$query_array[0])]['inbound_time'] + $query_array[4];
						}
						
						//put calls longer than the specified time, made in the last week, and having recording files into an array for use later.
						if(($query_array[4] > cl('cs_stats_min_rec_time')) && ($query_array[0] > (time()-(60*60*24*7))) && ((cl('cs_stats_rec_direction') == $call_dir) || (cl('cs_stats_rec_direction') == 'both')))
						{
							foreach (glob("/var/spool/asterisk/monitor/".date("Ymd",$query_array[0])."-*".$query_array[0]."*.gsm") as $filename)
							{
								$longer_calls[$long_call_count]['file'] = $filename;
								$longer_calls[$long_call_count]['info'] = $query_array;
								$long_call_count++;
							}
							
							//if we're looking for outbound calls and the previous foreach didn't find the recording file, then look for this string pattern.
							if(!isset($longer_calls[$long_call_count]['file']) && ($call_dir == 'outbound'))
							{
								foreach (glob("/var/spool/asterisk/monitor/OUT".$val['mailbox']."-".date("Ymd",$query_array[0])."-*".$query_array[0]."*.gsm") as $filename)
								{
									$longer_calls[$long_call_count]['file'] = $filename;
									$longer_calls[$long_call_count]['info'] = $query_array;
									$long_call_count++;
								}
							}
							if(!isset($longer_calls[$long_call_count]['file']))
							{
								foreach (glob("/var/spool/asterisk/monitor/g".$val['mailbox']."-".date("Ymd",$query_array[0])."-*".$query_array[0]."*.gsm") as $filename)
								{
									$longer_calls[$long_call_count]['file'] = $filename;
									$longer_calls[$long_call_count]['info'] = $query_array;
									$long_call_count++;
								}
							}
							if(empty($longer_calls[$long_call_count]['file']))
							{
								foreach (glob("/var/spool/asterisk/monitor/q*-".date("Ymd",$query_array[0])."-*".$query_array[0]."*.gsm") as $filename)
								{
									$longer_calls[$long_call_count]['file'] = $filename;
									$longer_calls[$long_call_count]['info'] = $query_array;
									$long_call_count++;
								}
							}
						}
					}
					
					//sending random recordings to the user's voicemail
					//find recordings to review if there are more than the specified recordings available for review
					if(count($longer_calls) > cl('cs_stats_min_rec'))
					{
						if(cl('debug'))
						{
							print count($longer_calls)." elegable for sending to the voicemail for user .".$val['mailbox']."\n";
						}
						$super_ext = explode(',',cl('cs_stats_sup_ext'));
						$used_calls = array();
						$num_rec = (cl('cs_stats_num_rec') > count($longer_calls)) ? count($longer_calls) : cl('cs_stats_num_rec');
						
						$x = 0;
						while($x < $num_rec)
						{
							$this_rand = rand(0,count($longer_calls));
							if(!in_array($this_rand,$used_calls))
							{
								$used_calls[] = $this_rand;
								$x++;
								
								SendToVoicemail($val['mailbox'],$longer_calls[$this_rand]['file'],$longer_calls[$this_rand]['info'][0],$longer_calls[$this_rand]['info'][4],'"Review Call, Ext '.$val['mailbox'].'" <'.$val['mailbox'].'>');
								if(cl('debug'))
								{
									print "Sent call ".$longer_calls[$this_rand]['info'][1]." to voicemail for user .".$val['mailbox']."\n";
								}
								
								//if this is not a supervisor extension, then send the recording to the supervisor(s) extension(s).
								if(!in_array($val['mailbox'],$super_ext))
								{
									foreach($super_ext as $val3)
									{
										SendToVoicemail($val3,$longer_calls[$this_rand]['file'],$longer_calls[$this_rand]['info'][0],$longer_calls[$this_rand]['info'][4],'"Review Call, Ext '.$val['mailbox'].'" <'.$val['mailbox'].'>');
									}
								}
							}
						}
						
						/*
						old code used for generating an MP3 and emailing it. May use it again later, but right now the code is sending the files to the users voicemail.
						In order to use this code, the script generally needs to be run as root to have the correct file premissions and sox needs to be installed on the system.
						$rand1 = rand(0,count($longer_calls));
						$rand2 = rand(0,count($longer_calls));
						if($rand1 == $rand2)
						{
							$rand2 = rand(0,count($longer_calls));
						}
						$file1 = '/var/spool/asterisk/monitor/'.$val['mailbox'].'-'.date('Y-m-d-H-i-s',$longer_calls[$rand1]['info'][0]).'.mp3';
						$file2 = '/var/spool/asterisk/monitor/'.$val['mailbox'].'-'.date('Y-m-d-H-i-s',$longer_calls[$rand2]['info'][0]).'.mp3';
						exec('sox '.$longer_calls[$rand1]['file'].' '.$file1);
						$file = fopen($file1,'rb');
						$data = fread($file,filesize($file1));
						fclose($file);
						$attach[] = array("name"=>basename($file1),"content"=>$data,"type"=>"Application/octet-stream");
						exec('sox '.$longer_calls[$rand2]['file'].' '.$file2);
						$file = fopen($file2,'rb');
						$data = fread($file,filesize($file2));
						fclose($file);
						$attach[] = array("name"=>basename($file2),"content"=>$data,"type"=>"Application/octet-stream");
						*/
					}
					
					//if the user has an email address, we'll process the information and email it to them.
					if($val['email'] != '')
					{
						$stats = '<style>
								.odd {background-color: #EDF1FD;}
								.even {background-color: #FFFFFF;}
								.hl_red {background-color: #FF9696;}
							</style>
							<table border="0" cellspacing="1" cellpadding="0">
								<tr>
									<td><p><strong>Date</strong></p></td>
									<td><p><strong>Time</strong></p></td>
									<td>&nbsp;</td>
									<td><p><strong>Calls</strong></p></td>
									<td><p><strong>Out Time</strong></p></td>
									<td><p><strong>Out Calls</strong></p></td>
									<td><p><strong>In Time</strong></p></td>
									<td><p><strong>In Calls</strong></p></td>
									<td><p><strong>Avg. Call Time</strong></p></td>
								</tr>';
						
						//find the longest day
						$longest_day = 0;
						foreach($call_array as $val2)
						{
							$longest_day = ($longest_day < ($val2['outbound_time'] + $val2['inbound_time'])) ? ($val2['outbound_time'] + $val2['inbound_time']) : $longest_day;
						}
						
						//print out the days and the stats
						$this_week_outbound_calls = 0;
						$this_week_outbound_time = 0;
						$this_week_inbound_calls = 0;
						$this_week_inbound_time = 0;
						$last_week = '';
						$tr_class = 'even';
						foreach($call_array as $key=>$val2)
						{
							if(date('W',$val2['time']) != $last_week)
							{
								if($last_week == '')
								{
									$last_week = date('W',$val2['time']);
								}
								else
								{
									//print out stats for the last week.
									$stats .= '<tr class="hl_red">
											<td><p>Week Totals</p></td>
											<td>'.strTime($this_week_outbound_time + $this_week_inbound_time).'</td>
											<td>&nbsp;</td>
											<td><p>'.($this_week_inbound_calls + $this_week_outbound_calls).'</p></td>
											<td><p>'.strTime($this_week_outbound_time).'</p></td>
											<td><p>'.$this_week_outbound_calls.'</p></td>
											<td><p>'.strTime($this_week_inbound_time).'</p></td>
											<td><p>'.$this_week_inbound_calls.'</p></td>
											<td><p>'.strTime(round((($this_week_inbound_time + $this_week_outbound_time)/($this_week_inbound_calls + $this_week_outbound_calls)))).'</p></td>
										</tr>';
									$this_week_outbound_calls = 0;
									$this_week_outbound_time = 0;
									$this_week_inbound_calls = 0;
									$this_week_inbound_time = 0;
									$last_week = date('W',$val2['time']);
								}
							}
							
							$tr_class = ($tr_class == 'even') ? 'odd' : 'even';
							$stats .= '<tr class="'.$tr_class.'">
									<td><p>'.$key.'</p></td>
									<td><p>'.strTime($val2['outbound_time'] + $val2['inbound_time']).'</p></td>
									<td><hr color="blue" size="12" width="'.round(((($val2['outbound_time'] + $val2['inbound_time'])/$longest_day)*200)).'"></td>
									<td><p>'.($val2['inbound_calls'] + $val2['outbound_calls']).'</p></td>
									<td><p>'.strTime($val2['outbound_time']).'</p></td>
									<td><p>'.$val2['outbound_calls'].'</p></td>
									<td><p>'.strTime($val2['inbound_time']).'</p></td>
									<td><p>'.$val2['inbound_calls'].'</p></td>
									<td><p>'.strTime(round((($val2['inbound_time'] + $val2['outbound_time'])/($val2['inbound_calls'] + $val2['outbound_calls'])))).'</p></td>
								</tr>';
							$this_week_outbound_calls = $this_week_outbound_calls + $val2['outbound_calls'];
							$this_week_outbound_time = $this_week_outbound_time + $val2['outbound_time'];
							$this_week_inbound_calls = $this_week_inbound_calls + $val2['inbound_calls'];
							$this_week_inbound_time = $this_week_inbound_time + $val2['inbound_time'];
						}
						
						//print out stats for the very last week.
						$stats .= '<tr class="hl_red">
								<td><p>Week Totals</p></td>
								<td>'.strTime($this_week_outbound_time + $this_week_inbound_time).'</td>
								<td>&nbsp;</td>
								<td><p>'.($this_week_inbound_calls + $this_week_outbound_calls).'</p></td>
								<td><p>'.strTime($this_week_outbound_time).'</p></td>
								<td><p>'.$this_week_outbound_calls.'</p></td>
								<td><p>'.strTime($this_week_inbound_time).'</p></td>
								<td><p>'.$this_week_inbound_calls.'</p></td>
								<td><p>'.strTime(round((($this_week_inbound_time + $this_week_outbound_time)/($this_week_inbound_calls + $this_week_outbound_calls)))).'</p></td>
							</tr>';
						
						$stats .= '</table>';
						
						$mesgbody = '<html><body><p>'.$val['name'].',<br><br>
								Please take a few minutes to review your call stats from the last week.';
						if(cl('cs_stats_num_days') > 7)
						{
							$mesgbody .= 'For comparison, the previous '.cl('cs_stats_num_days').' days are also included.';
						}
						$mesgbody .= '<p>';
						if(count($longer_calls) > cl('cs_stats_min_rec'))
						{
							$mesgbody .= '<br><p>Random Recordings of phone conversations you had have been sent to your voicemail. Please listen to these and critique yourself in 
									order that our customer service might improve.</p>';
						}
						$mesgbody .= '<br><p>Thank you for your continued hard work and dedication to customer service.</p><br>'.$stats.'</body></html>';
						
						$attach = array();
						$attach[] = array("content"=>$mesgbody,"type"=>"text/html");
						
						//see if this is already a supervisor's email address, if not, add the supervisor to the emailing.
						$super_email = explode(',',cl('cs_stats_sup_email'));
						if((!in_array($val['email'],$super_email)) && (cl('cs_stats_sup_email') != ''))
						{
							$val['email'] .= ','.cl('cs_stats_sup_email');
						}
						
						mailAttachment($val['email'],cl('cs_stats_from_email'),$val['mailbox']." Phone Usage Stats",$attach);
						if(cl('debug'))
						{
							print "Processed information for ext. ".$val['mailbox']." and sent an email to ".$val['email']."\n";
						}
					}
				}
			}
		}
	}
	else if(cl('debug'))
	{
		print "Unable to find voicemail.conf file.\n";
	}
}
?>