<?php
print 'Installing Customer Service Reporting<br>';

if((isset($amp_conf['ASTVARLIBDIR']) ? $amp_conf['ASTVARLIBDIR'] : '') == '')
{
	$astlib_path = "/var/lib/asterisk";
}
else
{
	$astlib_path = $amp_conf['ASTVARLIBDIR'];
}

print 'Creating Table csreportingoptions<br>';

$sql = "CREATE TABLE IF NOT EXISTS csreportingoptions (
  cs_stats_sup_email varchar(100),
  cs_stats_sup_ext varchar(255),
  cs_stats_ext varchar(255),
  cs_stats_min_rec mediumint,
  cs_stats_num_rec mediumint,
  cs_stats_min_rec_time mediumint,
  cs_stats_rec_direction enum('both','inbound','outbound'),
  cs_stats_num_days smallint,
  cs_stats_cron_day varchar(10),
  cs_stats_from_email varchar(255),
  voicemailconf varchar(255)
);";

$check = $db->query($sql);
if (DB::IsError($check)) {
        die_freepbx( "Can not create csreportingoptions table: " . $check->getMessage() .  "\n");
}

print 'Installing default values<br>';

$sql = "INSERT INTO	csreportingoptions
										(cs_stats_min_rec,
										cs_stats_num_rec,
										cs_stats_min_rec_time,
										cs_stats_num_days,
										cs_stats_rec_direction,
										cs_stats_cron_day,
										voicemailconf)
				VALUES			(10,
										2,
										60,
										35,
										'both',
										'Sunday',
										'/etc/asterisk/voicemail.conf')";

$check = $db->query($sql);
if (DB::IsError($check))
{
	die_freepbx( "Can not create default values in csreportingoptions table: " . $check->getMessage() .  "\n");
}

print 'Installing cronjob into the FreePBX cron manager.<br>';
$sql = "INSERT INTO	cronmanager
										(module,
										id,
										time,
										freq,
										command)
				VALUES			('csreporting',
										'every_week',
										22,
										24,
										'php ".$_SERVER['DOCUMENT_ROOT']."/admin/modules/csreporting/cron/every_week.php')";
$check = $db->query($sql);
if (DB::IsError($check))
{
	die_freepbx( "Can not create values in cronmanager table: " . $check->getMessage() .  "\n");
}
?>