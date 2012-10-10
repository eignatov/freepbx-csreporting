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
if( basename($_SERVER['SCRIPT_NAME']) == "common.php")
{
	header("Location: " . "http://" . cl('http_host'));
	exit(0);
}

/**
connect to the database and return a resource link
*/
function dbconnect($host,$user,$pass,$database='')
{
	$db = mysql_connect($host,$user,$pass);
	if($database != '')
	{
		mysql_select_db($database,$db);
	}
	return($db);
}

/**
Get a variable and make it safe from injection attacks
$var_name -> name of the variable you seek
$default -> Default value if the variable is not set
$method -> options are POST,REQUEST,SERVER, etc.
*/
function get_safe($var_name,$default="",$method="REQUEST")
{
	eval('$return = (isset($_'.$method.'["'.$var_name.'"])) ? get_htmlentities(trim($_'.$method.'["'.$var_name.'"])) : $default;');
	return $return;
}

/**
Get a variable and make sure that it is numeric. If not the default value is returned.
$var_name -> name of the variable you seek
$default -> Default value if the variable is not set
$method -> options are POST,REQUEST,SERVER, etc.
*/
function get_number($var_name,$default=0,$method="REQUEST")
{
	eval('$return = (isset($_'.$method.'["'.$var_name.'"])) ? SecurityNumericCheck(trim($_'.$method.'["'.$var_name.'"])) : $default;');
	return $return;
}

/**
Get a variable raw data is NOT protected from injection attacks
$var_name -> name of the variable you seek
$default -> Default value if the variable is not set
$method -> options are POST,REQUEST,SERVER, etc.
*/
function get_raw($var_name,$default="",$method="REQUEST")
{
	eval('$return = (isset($_'.$method.'["'.$var_name.'"])) ? $_'.$method.'["'.$var_name.'"] : $default;');
	return $return;
}

function strTime($s)
{
	$str = '';
	$d = intval($s/86400);
	$s -= $d*86400;
	
	$h = intval($s/3600);
	$s -= $h*3600;
	
	$m = intval($s/60);
	$s -= $m*60;
	
	if ($d) $str = $d . 'd ';
	if ($h) $str .= $h . 'h ';
	if ($m) $str .= $m . 'm ';
	if ($s) $str .= $s . 's';
	
	return $str;
}

/**
useful for timing things like script execution time
*/
function microtime_float()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

/**
Returns a formatted datetime string from a mysql datetime
$datetime -> datetime returned from mysql
$format -> php date() format string
$seconds_add -> seconds to add to the returned mysql datetime (can be negative)
*/
function mysql_datetime($datetime, $format, $seconds_add=0)
{
	$pattern = "/^(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2}):(\d{2})$/i";
	if(preg_match($pattern, $datetime, $dt) && checkdate($dt[2], $dt[3], $dt[1]))
	{
		return date($format, (mktime($dt[4], $dt[5], $dt[6], $dt[2], $dt[3], $dt[1]) + $seconds_add));
	}
	return $datetime;
}

/**
take a mysql date (yyyy-mm-dd) and return a php usable date()
*/
function GetTimeStamp($MySqlDate) 
{ 
	$date_array = explode("-",$MySqlDate); // split the array 
	 
	$var_year = $date_array[0]; 
	$var_month = $date_array[1]; 
	$var_day = $date_array[2]; 
	
	$var_timestamp = mktime(0,0,0,$var_month,$var_day,$var_year); 
	return($var_timestamp); // return it to the user 
}

function SecurityNumericCheck($number)
{
	if(is_numeric($number))
	{
		return($number);
	}
	else
	{
		return("");
	}
}

/**
Cleans US/Canadian phone numbers into the format NXX-XXX-XXXX
*/
function CleanPhoneNumber($phonenumber)
{
	if(strlen(ereg_replace("[^0-9]",'',$phonenumber)) == 10)
	{
		$phonenumber = ereg_replace("[^0-9]",'',$phonenumber);
		$phonenumber = substr($phonenumber,0,3).'-'.substr($phonenumber,3,3).'-'.substr($phonenumber,6,4);
	}
	return($phonenumber);
}

/**
Checks an email address for a valid format.
If $validate_domain is true, it will look to see if the domain used in the email address is a real domain.
*/
function CheckEmail($email,$validate_domain=false)
{
	if (!ereg('^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+'.'@'.'[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.'.'[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$', $email))
	{
		return false;
	}
	else
	{
		if($validate_domain)
		{
			// take a given email address and split it into the username and domain.
			list($userName, $mailDomain) = split("@", $email);
			if(checkdnsrr($mailDomain, "MX"))
			{
				// this is a valid email domain!
				return true;
			}
			else
			{
				// this email domain doesn't exist!
				return false;
			}
		}
		else
		{
			return true;
		}
	}
}

function get_htmlentities($given, $quote_style = ENT_QUOTES)
{
	return htmlentities(html_entity_decode( stripslashes($given), $quote_style ), $quote_style );
}

/**
Force a number to be a specified length. Usually used to add leading zeros.
$num -> number you want to manipulate
$length -> desired length of the result
*/
function inc_length ($num,$length)
{
	$return = $num;
	if(is_numeric($num))
	{
		$return = (strlen($num) >= $length) ? $num : inc_length (0 . $num , $length);
	}
	return $return;
}

/**
Clean some text up to remove common curse words.
*/
function CurseFilter($text,$replacement='@#$%')
{
	$curse_words = array("shit","fuck","asshole","ass hole","bitch","whore","bastard","ass","a$$","asses","damn");
	foreach($curse_words as $value)
	{
		$match_value = "/\b".$value."\b/i";
		$text = preg_replace($match_value,$replacement,$text);
	}
	return($text);
}

/**
returns the index value in the global $print_lookup_array
*/
function cl($index)
{
	global $print_lookup_array;
	if(!empty($print_lookup_array[$index]))
	{
		return $print_lookup_array[$index];
	}
	else
	{
		return "";
	}
}

/**
Recursively read voicemail.conf (and any included files)
This function is called by getVoicemailConf()
this function taken from the call monitor portion of FreePBX.
*/
function parse_voicemailconf($filename, &$vmconf, &$section)
{
	if(is_null($vmconf))
	{
		$vmconf = array();
	}
	if (is_null($section))
	{
		$section = "general";
	}
	
	if (file_exists($filename))
	{
		$fd = fopen($filename, "r");
		while ($line = fgets($fd, 1024))
		{
			if(preg_match("/^\s*(\d+)\s*=>\s*(\d*),(.*),(.*),(.*),(.*)\s*([;#].*)?/",$line,$matches))
			{
				// "mailbox=>password,name,email,pager,options"
				// this is a voicemail line	
				$vmconf[$section][ $matches[1] ] = array("mailbox"=>$matches[1],
									"pwd"=>$matches[2],
									"name"=>$matches[3],
									"email"=>$matches[4],
									"pager"=>$matches[5],
									"options"=>array(),
									);
								
				// parse options
				//output($matches);
				foreach (explode("|",$matches[6]) as $opt)
				{
					$temp = explode("=",$opt);
					//output($temp);
					if (isset($temp[1]))
					{
						list($key,$value) = $temp;
						$vmconf[$section][ $matches[1] ]["options"][$key] = $value;
					}
				}
			}
			else if (preg_match("/^\s*(\d+)\s*=>\s*dup,(.*)\s*([;#].*)?/",$line,$matches))
			{
				// "mailbox=>dup,name"
				// duplace name line
				$vmconf[$section][ $matches[1] ]["dups"][] = $matches[2];
			}
			else if(preg_match("/^\s*#include\s+(.*)\s*([;#].*)?/",$line,$matches))
			{
				// include another file
				
				if ($matches[1][0] == "/")
				{
					// absolute path
					$filename = $matches[1];
				}
				else
				{
					// relative path
					$filename =  dirname($filename)."/".$matches[1];
				}
				
				parse_voicemailconf($filename, $vmconf, $section);
				
			}
			else if (preg_match("/^\s*\[(.+)\]/",$line,$matches))
			{
				// section name
				$section = strtolower($matches[1]);
			}
			else if (preg_match("/^\s*([a-zA-Z0-9-_]+)\s*=\s*(.*?)\s*([;#].*)?$/",$line,$matches))
			{
				// name = value
				// option line
				$vmconf[$section][ $matches[1] ] = $matches[2];
			}
		}
		fclose($fd);
	}
}
?>