<?php 
//
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

//tts_findengines()
if(count($_POST))
{
	csreporting_saveconfig();
}

$data = csreporting_getconfig();
//print_r($data);
$cs_stats_sup_email = $data[0];
$cs_stats_sup_ext = $data[1];
$cs_stats_ext = $data[2];
$cs_stats_min_rec = $data[3];
$cs_stats_num_rec = $data[4];
$cs_stats_min_rec_time = $data[5];
$cs_stats_rec_direction = $data[6];
$cs_stats_num_days = $data[7];
$cs_stats_from_email = $data[8];
$voicemailconf = $data[9];
$cs_stats_cron_day = $data[10];

$sup_ext_array = explode(',',$cs_stats_sup_ext);
$cs_ext_array = explode(',',$cs_stats_ext);
	
#	die();

?>
<form method="POST" action="">
	<font face="Arial">
	<br></font><h2><font face="Arial"><?php echo _("Customer Service Reporting")?></font><hr></h5>
	<font face="Arial"></td></tr>
	<p>With this customer service reporting module, you will receive emails with call volume statistics and copies of random phone calls weekly. Every Sunday night, reports on call volume for the 
	previous week will be generated and emailed for each of the extensions chosen, to both the user at that an extension and the supervisor(s) listed. Random call recordings from the previous week 
	will also be delivered to the voicemail of the user as well as supervisor extensions you specify, so that users and supervisors can review and critique calls.</p>
</font> 
	</p>
	<p><a href="javascript:Ht_Run_Report();">Click Here to Run The Report Now</a></p>
<h3><font face="Arial">Reporting Configuration Details</font><hr></h3>
	<font face="Arial"><b>Supervisor Information</b><br>
	<br>
	
	</font>
	<table>
		<tr>
			<td><a href="javascript: return false;" class="info"><?php echo _("Supervisor Email Address(s):")?><span><?php echo _("Email copies of CS agent reports will be sent here.<br><br>Customer service supervisor email address, comma separated (no spaces) if more than one.")?></span></a></td>
			<td><input type="text" name="cs_stats_sup_email" value="<?php echo (isset($cs_stats_sup_email) ? $cs_stats_sup_email : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
		</tr>
		
		<tr>
			<td valign="top"><a href="javascript: return false;" class="info"><?php echo _("Supervisor Extensions") ?>:<span><br><?php echo _("Copies of CS agent recordings will be delivered to these supervisors voicemails.<br><br>List extensions one per line.") ?><br><br></span></a></td>
			<td valign="top">
				<textarea id="cs_stats_sup_ext" cols="15" rows="<?php $rows = count($sup_ext_array)+1; echo (($rows < 5) ? 5 : (($rows > 20) ? 20 : $rows) ); ?>" name="cs_stats_sup_ext" tabindex="<?php echo ++$tabindex;?>"><?php
						$already_listed = array();
						foreach($sup_ext_array as $mem)
						{
							$mem = trim($mem);
							if((!in_array($mem,$already_listed)) && ($mem != ''))
							{
								print $mem."\n";
								$already_listed[] = $mem;
							}
						}
					?></textarea>
			</td>
		</tr>
	
		<tr>
			<td>
			<a href=# class="info"><?php echo _("Extension Quick Pick")?>
				<span>
					<?php echo _("Choose an extension to append to the end of the supervisor extension list above.")?>
				</span>
			</a>
			</td>
			<td>
				<select onChange="insertExten('sup_ext','cs_stats_sup_ext');" id="sup_ext" tabindex="<?php echo ++$tabindex;?>">
					<option value=""><?php echo _("(pick extension)")?></option>
					<?php
					$results = core_users_list();
					foreach ($results as $result)
					{
						echo "<option value='".$result[0]."'>".$result[0]." (".$result[1].")</option>\n";
					}
					?>
				</select>
			</td>
		</tr>
		
		<tr>
			<td valign="top"><a href="javascript: return false;" class="info"><?php echo _("Extensions to Process") ?>:<span><br><?php echo _("Generate reports for these extensions.<br>List extensions one per line.") ?><br><br></span></a></td>
			<td valign="top">
				<textarea id="cs_stats_ext" cols="15" rows="<?php $rows = count($cs_ext_array)+1; echo (($rows < 5) ? 5 : (($rows > 20) ? 20 : $rows) ); ?>" name="cs_stats_ext" tabindex="<?php echo ++$tabindex;?>"><?php
						$already_listed = array();
						foreach($cs_ext_array as $mem)
						{
							$mem = trim($mem);
							if((!in_array($mem,$already_listed)) && ($mem != ''))
							{
								print $mem."\n";
								$already_listed[] = $mem;
							}
						}
					?></textarea>
			</td>
		</tr>
	
		<tr>
			<td>
			<a href=# class="info"><?php echo _("Extension Quick Pick")?>
				<span>
					<?php echo _("Choose an extension to append to the end of the static agents list above.")?>
				</span>
			</a>
			</td>
			<td>
				<select onChange="insertExten('rep_ext','cs_stats_ext');" id="rep_ext" tabindex="<?php echo ++$tabindex;?>">
					<option value=""><?php echo _("(pick extension)")?></option>
					<?php
					$results = core_users_list();
					foreach ($results as $result)
					{
						echo "<option value='".$result[0]."'>".$result[0]." (".$result[1].")</option>\n";
					}
					?>
				</select>
			</td>
		</tr>
		
		<tr>
			<td><a href="javascript: return false;" class="info"><?php echo _("Night of Week to Run Report:")?><span><?php echo _("Select the night of the week that you would like the report to be run<br><br>Generally the best night of the week to do this is Sunday night because PHP runs on a Monday-Sunday week, so Sunday night is the very end of the week.")?></span></a></td>
			<td>
				<?php
				print '<SELECT name="cs_stats_cron_day" tabindex="'.++$tabindex.'">';
				$days_array = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
				foreach($days_array as $val)
				{
					print '<OPTION value="'.$val.'"';
					if($cs_stats_cron_day == $val)
					{
						print ' selected';
					}
					print '>'.$val.'</OPTION>';
				}
				print '</select>';
				?>
			</td>
		</tr>
		
		<tr>
			<td><a href="javascript: return false;" class="info"><?php echo _("Num Days to Go Back:")?><span><?php echo _("Stats in the email are based on weeks, so increments of 7 are recomended here. Ex: set this number to 28 if you want statistics on the previous week and the prior 3 weeks for comparison to the latest week.")?></span></a></td>
			<td><input type="text" name="cs_stats_num_days" value="<?php echo (isset($cs_stats_num_days) ? $cs_stats_num_days : '10'); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
		</tr>
		
		<tr>
			<td><a href="javascript: return false;" class="info"><?php echo _("Min Available Recordings:")?><span><?php echo _("Specify the minimum number of recordings that must be in the system over the last week to select random recordings to send to the user. If the minimum threshold is not met for each user, no recordings will be delivered for that user.")?></span></a></td>
			<td><input type="text" name="cs_stats_min_rec" value="<?php echo (isset($cs_stats_min_rec) ? $cs_stats_min_rec : '10'); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
		</tr>
		
		<tr>
			<td><a href="javascript: return false;" class="info"><?php echo _("Min Recording Length:")?><span><?php echo _("Minimum number of seconds a recording must be to be eligible for delivery to the user.<br><br>If the min length is not met, it will not be counted for the min available above.")?></span></a></td>
			<td><input type="text" name="cs_stats_min_rec_time" value="<?php echo (isset($cs_stats_min_rec_time) ? $cs_stats_min_rec_time : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
		</tr>
		
		<tr>
			<td><a href="javascript: return false;" class="info"><?php echo _("Recording Direction:")?><span><?php echo _("Minimum number of seconds a recording must be to be eligible for delivery to the user.<br><br>If the min length is not met, it will not be counted for the min available above.")?></span></a></td>
			<td>
				<select name="cs_stats_rec_direction" tabindex="<?php echo ++$tabindex;?>">
					<option value="both"<?php if($cs_stats_rec_direction == 'both') { print ' selected'; } ?>>Both</option>
					<option value="inbound"<?php if($cs_stats_rec_direction == 'inbound') { print ' selected'; } ?>>In Bound</option>
					<option value="outbound"<?php if($cs_stats_rec_direction == 'outbound') { print ' selected'; } ?>>Out Bound</option>
				</select>
			</td>
		</tr>
		
		<tr>
			<td><a href="javascript: return false;" class="info"><?php echo _("Recordings to Deliver:")?><span><?php echo _("Specify the number of randomly selected recordings that you would like to be delivered for each user.")?></span></a></td>
			<td><input type="text" name="cs_stats_num_rec" value="<?php echo (isset($cs_stats_num_rec) ? $cs_stats_num_rec : '2'); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
		</tr>
		
		<tr>
			<td><a href="javascript: return false;" class="info"><?php echo _("Email Comes From:")?><span><?php echo _("Specify the email address that the reports appear to come from.<br><br>If nothing is specified, the email address that is used in the \"General Settings\" area, from which faxes are sent, will be used.")?></span></a></td>
			<td><input type="text" name="cs_stats_from_email" value="<?php echo (isset($cs_stats_from_email) ? $cs_stats_from_email : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
		</tr>
	</table>
	
	<h3><font face="Arial">Platform Configuration Details<hr></h3></font>
	<p><font face="Arial"><b>Path to voicemail configuration file<br>
	</b>complete path to asterisk's voicemail.conf, starting with <b>/etc<br>
	</b> <input type="text" name="voicemailconf" size="80" value="<?php echo (isset($voicemailconf) ? $voicemailconf : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></font></p>
	
	<hr><font face="Arial"><br><br><input type="submit" value="Update" name="B1"><br><br><br>
	</font>
<small><center><font face="Arial"><br>Customer Service Reporting was written, and is maintained by   by <a target="_blank" style="color: #0000FF" href="http://www.FitnessRepairParts.com">Jeremy Jacobs</a>  Queue Reporting was put into FreePBX Module format by Tony Shiffer.<br>
	</font></center></small>
<br><br>

<script language="javascript">
<!--

function insertExten(this_select,text_area_name)
{
	exten = document.getElementById(this_select).value;

	grpList=document.getElementById(text_area_name);
	if(grpList.value[ grpList.value.length - 1 ] == "\n")
	{
		grpList.value = grpList.value + exten;
	}
	else
	{
		grpList.value = grpList.value + '\n' + exten;
	}

	// reset element
	document.getElementById(this_select).value = '';
}

var isWorking = false;
var http = getHTTPObject();

function getHTTPObject()
{
	var xmlhttp;
	//do not take out this section of code that appears to be commented out...if you do the guns stop working.
	/*@cc_on
	@if (@_jscript_version >= 5)
	try
	{
		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	}
	catch (e)
	{
		try
		{
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
		catch (E)
		{
			xmlhttp = false;
		}
	}
	@else
	{
		xmlhttp = false;
	}
	@end @*/

	if(!xmlhttp && typeof XMLHttpRequest != 'undefined')
	{
		try
		{
			xmlhttp = new XMLHttpRequest();
		}
		catch (e)
		{
			xmlhttp = false;
		}
	}
	return xmlhttp;
}

function Ht_Response()
{
	if (http.readyState == 4)
	{
		alert("Report has been completed.");
		isWorking = false;
	}
}

function Ht_Run_Report()
{
	if(!isWorking)
	{
		http.open("GET", "modules/csreporting/cron/every_week.php?ignore_day=yes&math=" + Math.random(), true);
		isWorking = true;
		http.onreadystatechange = Ht_Response;
		http.send(null);
	}
	else
	{
		setTimeout("Ht_Run_Report()",100);
	}
}

//-->
</script>
