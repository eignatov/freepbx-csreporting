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
if( basename($_SERVER['SCRIPT_NAME']) == "mail.php")
{
	header("Location: " . "http://" . cl('http_host'));
	exit(0);
}

function makeAttachment($attachment)
{
	$headers = "Content-Type: " . $attachment["type"];   

	if(isset($attachment["name"]))
	{
		$headers .= "; name=\"{$attachment["name"]}\"";
		$headers .= "\r\n";

		$headers .= "Content-Transfer-Encoding: base64\r\n";

		$headers .= "Content-Disposition: attachment; filename=\"{$attachment["name"]}\"\r\n";
		$headers .= "\r\n";

		$headers .= chunk_split(base64_encode($attachment["content"]));
		$headers .= "\r\n";
	}
	else if($attachment["type"] == "text/plain")
	{
		$headers .= "\r\n";
		$headers .= "Content-Disposition: inline\r\n";
		$headers .= "\r\n";
		$headers .= $attachment["content"];
		$headers .= "\r\n";
	}
	else
	{
		$headers .= "\r\n";

		$headers .= "Content-Transfer-Encoding: base64\r\n";

		$headers .= "\r\n";

		$headers .= chunk_split(base64_encode($attachment["content"]));
		$headers .= "\r\n";
	}

	return($headers);
}

function mailAttachment($to, $from, $subject, $attachment)
{
	$headers = "From: $from\r\n";

	$headers .= "MIME-Version: 1.0\r\n";

	if(count($attachment) > 1)
	{  
		$boundary = uniqid("STAFSTER");

		$headers .= "Content-Type: multipart/mixed";
		$headers .= "; boundary=\"$boundary\"\r\n\r\n";
		$headers .= "This is a MIME encoded message.\r\n\r\n";
		$headers .= "--$boundary";

		foreach($attachment as $a)
		{
			$headers .= "\r\n";
			$headers .= makeAttachment($a);
			$headers .= "--$boundary";
		}

		$headers .= "--\r\n";
	}
	else
	{
		$headers .= makeAttachment($attachment[0]);
	}

	mail($to, $subject, "", $headers);
}
?>
