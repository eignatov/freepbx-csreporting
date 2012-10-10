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
if( basename($_SERVER['SCRIPT_NAME']) == "common_file.php")
{
	header("Location: " . "http://" . cl('http_host'));
	exit(0);
}

function DetectFileType($file_name)
{
	$file_name = strtolower($file_name);
  if(substr($file_name,-4)=='.pdf')
  {
	  return "pdf";
	}
  else if(substr($file_name,-4)=='.zip')
  {
	  return "zip";
	}
  else if(substr($file_name,-4)=='.jpg')
  {
	  return "img";
	}
  else if(substr($file_name,-4)=='.bmp')
  {
	  return "img";
	}
  else if(substr($file_name,-4)=='.gif')
  {
	  return "img";
	}
  else if(substr($file_name,-4)=='.tif')
  {
	  return "img";
	}
  else if(substr($file_name,-4)=='.png')
  {
	  return "img";
	}
  else if(substr($file_name,-4)=='.txt')
  {
	  return "txt";
	}
  else if(substr($file_name,-4)=='.csv')
  {
	  return "csv";
	}
  else if(substr($file_name,-4)=='.doc')
  {
	  return "doc";
	}
  else if(substr($file_name,-5)=='.docx')
  {
	  return "docx";
	}
  else if(substr($file_name,-4)=='.xls')
  {
	  return "xls";
	}
  else if(substr($file_name,-5)=='.xlsx')
  {
	  return "xlsx";
	}
  else if(substr($file_name,-4)=='.odt')
  {
	  return "odt";
	}
  else if(substr($file_name,-4)=='.ods')
  {
	  return "ods";
	}
  else if(substr($file_name,-4)=='.wav')
  {
	  return "wav";
	}
  else if(substr($file_name,-4)=='.gsm')
  {
	  return "gsm";
	}
	else
	{
	  return "unk";
	}
}

/**
Copies a dir to another. Optionally caching the dir/file structure, used to synchronize similar destination dir (web farm).

@param $src_dir str Source directory to copy (without ending slash). 
@param $dst_dir str Destination directory to copy to (without ending slash). 
@param $verbose bool Show or hide file copied messages
@param $use_cached_dir_trees bool Set to true to cache src/dst dir/file structure. Used to sync to web farms
                (avoids loading the same dir tree in web farms; making sync much faster). 
@return Number of files copied/updated.
@example 
	To copy a dir: 
		dircopy("c:\max\pics", "d:\backups\max\pics"); 

	To sync to web farms (webfarm 2 to 4 must have same dir/file structure (run once with cache off to make sure if necessary)): 
		dircopy("//webfarm1/wwwroot", "//webfarm2/wwwroot", false, true); 
		dircopy("//webfarm1/wwwroot", "//webfarm3/wwwroot", false, true); 
		dircopy("//webfarm1/wwwroot", "//webfarm4/wwwroot", false, true); 
*/
function dircopy($src_dir, $dst_dir, $verbose = false, $use_cached_dir_trees = false) 
{    
	$num = 0;
	static $cached_src_dir;
	static $src_tree; 
	static $dst_tree; 
	
	if(!$use_cached_dir_trees || !isset($src_tree) || $cached_src_dir != $src_dir)
	{
		$src_tree = get_dir_tree($src_dir);
		$cached_src_dir = $src_dir; 
	}
	if (!$use_cached_dir_trees || !isset($dst_tree))
	{
		$dst_tree = get_dir_tree($dst_dir);
		if (!is_dir($dst_dir)) mkdir($dst_dir, 0777, true);  
	}
	
	foreach ($src_tree as $file => $src_mtime) 
	{
		if (!isset($dst_tree[$file]) && $src_mtime === false) // dir
		{
			mkdir("$dst_dir/$file");
		}
		elseif (!isset($dst_tree[$file]) && $src_mtime || isset($dst_tree[$file]) && $src_mtime > $dst_tree[$file])  // file
		{
			if (copy("$src_dir/$file", "$dst_dir/$file")) 
			{
				if($verbose)
				{
					echo "Copied '$src_dir/$file' to '$dst_dir/$file'<br>\r\n";
				}
				touch("$dst_dir/$file", $src_mtime); 
				$num++; 
			}
			else
			{
				echo "<font color='red'>File '$src_dir/$file' could not be copied!</font><br>\r\n";
			}
		}
	}
	
	return $num;
}

/**
Creates a directory / file tree of a given root directory

@param $dir str Directory or file without ending slash
@param $root bool Must be set to true on initial call to create new tree. 
@return Directory & file in an associative array with file modified time as value. 
*/
function get_dir_tree($dir, $root = true) 
{
	static $tree;
	static $base_dir_length; 
	
	if ($root)
	{ 
		$tree = array();  
		$base_dir_length = strlen($dir) + 1;  
	}
	
	if (is_file($dir)) 
	{
		//if (substr($dir, -8) != "/CVS/Tag" && substr($dir, -9) != "/CVS/Root"  && substr($dir, -12) != "/CVS/Entries")
		$tree[substr($dir, $base_dir_length)] = filemtime($dir); 
	}
	elseif (is_dir($dir) && $di = dir($dir)) // add after is_dir condition to ignore CVS folders: && substr($dir, -4) != "/CVS"
	{
		if(!$root)
		{
			$tree[substr($dir, $base_dir_length)] = false;
		}
		while(($file = $di->read()) !== false) 
		{
			if ($file != "." && $file != "..")
			{
				get_dir_tree("$dir/$file", false);
			}
		} 
		$di->close(); 
	}
	
	if ($root)
	{
		return $tree;
	}
}
    
/**
* rm() -- Vigorously erase files and directories.
* 
* @param $fileglob mixed If string, must be a file name (foo.txt), glob pattern (*.txt), or directory name.
*                        If array, must be an array of file names, glob patterns, or directories.
*/
function rm($fileglob)
{
	if (is_string($fileglob))
	{
		if (is_file($fileglob))
		{
			return unlink($fileglob);
		}
		else if(is_dir($fileglob))
		{
			$ok = rm("$fileglob/*");
			if(! $ok)
			{
				return false;
			}
			return rmdir($fileglob);
		}
		else
		{
			$matching = glob($fileglob);
			if($matching === false)
			{
				trigger_error(sprintf('No files match supplied glob %s', $fileglob), E_USER_WARNING);
				return false;
			}       
			$rcs = array_map('rm', $matching);
			if (in_array(false, $rcs))
			{
				return false;
			}
		}       
	}
	else if(is_array($fileglob))
	{
		$rcs = array_map('rm', $fileglob);
		if(in_array(false, $rcs))
		{
			return false;
		}
	}
	else
	{
		trigger_error('Param #1 must be filename or glob pattern, or array of filenames or glob patterns', E_USER_ERROR);
		return false;
	}
	
	return true;
}

function chmod_R($path, $filemode)
{ 
	if(!is_dir($path))
	{
		return chmod($path, $filemode);
	}

	$dh = opendir($path);
	while ($file = readdir($dh))
	{
		if($file != '.' && $file != '..')
		{
			$fullpath = $path.'/'.$file;
			if(!is_dir($fullpath))
			{
				if (!chmod($fullpath, $filemode))
				{
					return FALSE;
				}
			}
			else
			{
				if (!chmod_R($fullpath, $filemode))
				{
					return FALSE;
				}
			}
		}
	}
	
	closedir($dh);
	
	if(chmod($path, $filemode))
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

/**
Send a .wav or .gsm file to a users voicemail

$user -> extension number of the user
$orig_file -> full path to the original sound file
$timestamp -> unix timestamp for the call time
$length -> length in seconds of the recording
$caller_id -> Caller ID of the caller
$folder -> Folder of the user to deliver the message to
*/
function SendToVoicemail($user, $orig_file, $timestamp, $length, $caller_id='"Review Call"', $folder='INBOX') 
{
	if(!@file_exists($orig_file))
	{
		return false;
	}
	
	$dstPath = cl('boxbase').'/'.$user.'/'.$folder;
	
	umask("0000");
	if(!@file_exists($dstPath))
	{
   	if(!@mkdir($dstPath, cl('dirMode')))
		{
			return false;
		}
		chown($dstPath,'asterisk');
 	}
	
	//figure out what number this message will be.
	for($i=0; $i <= cl('MAXMSG'); $i++) 
	{
		$dstMessage = sprintf("msg%04d", $i);
		if(!@file_exists($dstPath.'/'.$dstMessage.'.txt'))
		{
			break;
		}
	
		// folder is full
		if($i >= cl('MAXMSG'))
		{
			return false;
		}
	}

	// copy files
	$dst = $dstPath.'/'.$dstMessage.".".DetectFileType($orig_file);
	if(!@copy($orig_file, $dst))
	{
		return false;
	}
	
	//generate message text file.
	$msg_file = ";
; Message Information file
;
[message]
context=macro-vm
macrocontext=ext-local
exten=s-NOMESSAGE
priority=1
callerid=".$caller_id."
origdate=".date("D M j G:i:s T Y",$timestamp)."
origtime=".$timestamp."
category=
duration=".$length."
";
	$fp = fopen($dstPath.'/'.$dstMessage.".txt", 'w');
	fwrite($fp, $msg_file);
	fclose($fp);
	
	return true;
}
?>