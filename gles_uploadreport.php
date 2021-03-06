<?php
	/* 		
		*
		* OpenGL ES hardware capability database server implementation
		*
		* Upload and convert a glESCapsViewer report and insert into database if not present
		*	
		* Copyright (C) 2011-2017 by Sascha Willems (www.saschawillems.de)
		*	
		* This code is free software, you can redistribute it and/or
		* modify it under the terms of the GNU Affero General Public
		* License version 3 as published by the Free Software Foundation.
		*	
		* Please review the following information to ensure the GNU Lesser
		* General Public License version 3 requirements will be met:
		* http://www.gnu.org/licenses/agpl-3.0.de.html
		*	
		* The code is distributed WITHOUT ANY WARRANTY; without even the
		* implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
		* PURPOSE.  See the GNU AGPL 3.0 for more details.		
		*
	*/

	include './dbconfig.php';

	// Check for valid file
	$path='./uploads/';

	// Reports are pretty small, so limit file size for upload (128 KByte will be more than enough)
	$MAX_FILESIZE = 64 * 1024;

	$file = $_FILES['data']['name'];
	
	$IP = $_SERVER["REMOTE_ADDR"];	

	// Skip if file exceeds size of 128 KBytes
	if ($_FILES['data']['size'] > $MAX_FILESIZE) 
	{
		echo "File exceeds size limitation of 128 KByte!";    
		header('HTTP/ 433 File too big!');
		exit();  
	}

	// Skip if not XML
	$ext = pathinfo($_FILES['data']['name'], PATHINFO_EXTENSION); 
	if ($ext != 'xml') 
	{
		echo "Report '$file' is not a valid XML file!";    
		header('HTTP/ 433 Not a valid XML file!');
		exit();  
	} 

	$msg = $_FILES['data']['name'];
	
	// Upload files
	if (!move_uploaded_file($_FILES['data']['tmp_name'], $path.$_FILES['data']['name'])) {
		mail($mailto, 'Error glESCapsViewer report upload', $path.$_FILES['data']['name']); 			
		die('');
	}; 

	$xml = file_get_contents($path.$_FILES['data']['name']);
	
	// Connect to DB 
	dbConnect();
	
	$xmlstring = str_replace("'", "\\'", $xml);
	
	// Check if report already exists
	$sqlresult = mysql_query("select check_glesreport('$xmlstring');");
	$sqlrow = mysql_fetch_row($sqlresult);
	
	$res = explode("|", $sqlrow[0]);
	
	if ($res[0] == "duplicate") {
		mail($mailto, 'Duplicate report for '.$res[1], "Duplicate report submitted :\nDevice = $res[1]\nIP = $IP"); 	
		header('HTTP/1.0 200 res_duplicate');
		die('');
	}		
		
	$sqlresult = mysql_query("call import_glesreport('$xmlstring');");	
		
    if (!$sqlresult) {
		$error = mysql_error();
		mail($mailto, 'glESCapsViewer error', "An uploaded report raised a mysql error :\nError = $error\nIP = $IP\nXML = $xmlstring"); 	
		header('HTTP/1.0 404 Error while saving report to database!');
		die('');
	};	
	
	$sqlstr = "SELECT Max(ID) as ReportID FROM reports;";   
	$sqlresult = mysql_query($sqlstr);

	$sqlrow = mysql_fetch_assoc($sqlresult);
	$reportID = $sqlrow["ReportID"];	

	header('HTTP/1.0 200 res_uploaded');	

	$sqlresult = mysql_query("select device, GL_RENDERER, GL_VERSION, os, cpuarch, submitter from reports where id = ".$reportID) or die('');
	$reportDetails = mysql_fetch_row($sqlresult);

	$msgtitle = "New OpenGL ES report for ".$reportDetails[0]." (".$reportDetails[1].")";

	$msg = "New OpenGL ES hardware report uploaded to the database\n\n";
	$msg .= "Link : http://opengles.gpuinfo.org/gles_generatereport.php?reportID=$reportID \n";	
	$msg .= "Devicename = ".$reportDetails[0]."\n";
	$msg .= "Renderer = ".$reportDetails[1]."\n";
	$msg .= "Version = ".$reportDetails[2]."\n";
	$msg .= "Android = ".$reportDetails[3]."\n";
	$msg .= "Architecture = ".$reportDetails[4]."\n";
	$msg .= "Submitter = ".$reportDetails[5]."\n";
	
	mail($mailto, $msgtitle, $msg); 	

	dbDisconnect();	 
?>