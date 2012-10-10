<?php
print 'Un Installing Customer Service Reporting<br>
		Deleting the cron manager entries for this module.<br>';
$sql = "DELETE FROM cronmanager WHERE module = 'csreporting'";
$check = $db->query($sql);
if (DB::IsError($check))
{
	die_freepbx( "Can not delete values in cronmanager table: " . $check->getMessage() .  "\n");
}
?>