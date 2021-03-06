 	<script>
		function showDiffOnly() {
			$('.same').toggle()
		}
		function toggleDiffCaps() {
			$('.sameCaps').toggle()
		}	
	</script>
<?php
     /*
		*
		* OpenGL ES hardware capability database server implementation
		*
		* Copyright (C) 2013-2015 by Sascha Willems (www.saschawillems.de)
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
 
	include './gles_htmlheader.inc'; 
	include './serverconfig/gles_config.php';	
	
	dbConnect();
	
	// Compare selected reports
	$reportids = array();
	$devicenames = array();
	$reportlimit = false;

	// Get checked report IDs
	foreach ($_GET as $k => $v) 
	{
		if (!is_numeric($k)) 
			continue;
		$reportids[] = $k;	
		if (count($reportids) > 7) 
		{ 
			$reportlimit = true;	 
			break; 
		}
	}   
	
	if ($reportlimit) {echo "<b>Note : </b>You selected more than 8 reports to compare, only displaying 8 reports.\n"; }	
	sort($reportids, SORT_NUMERIC);

	// Get device names
	$repids = implode(",", $reportids);   
	$sql = "SELECT devicename_short(device) as device FROM reports WHERE ID IN (" . $repids . ")";
	$sqlresult = mysql_query($sql) or die(mysql_error());	
	while($row = mysql_fetch_row($sqlresult)) 
	{		
		$devicenames[] = $row[0];
	}
	
	$sqlResult = mysql_query("select count(*) from viewExtensions");
	$sqlCount = mysql_result($sqlResult, 0);
    echo "<div class='header'>";
        echo "<h4 style='margin-left:10px;'>Comparing ".implode(", ", $devicenames)."</h4>";
    echo "</div>";				

	function generate_table($sql) {
	
		$sqlresult = mysql_query($sql) or die(mysql_error());			
		$column    = array();
		$captions  = array();
		
		while($row = mysql_fetch_row($sqlresult)) 
		{		
			$colindex = 0;
			$reportdata = array();		
			foreach ($row as $data) 
			{			
				$reportdata[] = $data;	  
				$captions[] = mysql_field_name($sqlresult, $colindex);			
				$colindex++;
			} 
			$column[] = $reportdata; 
		}
		
		// Generate table from array
		$rowindex = 0;
		for ($i = 0, $arrsize = sizeof($column[0]); $i < $arrsize; ++$i) { 	  
			echo "<tr>";
			echo "<td class='fieldcaption'>".$captions[$i]."</td>\n";
			for ($j = 0, $subarrsize = sizeof($column); $j < $subarrsize; ++$j) {	 
				echo "<td class='value'>".$column[$j][$i]."</td>";
			} 
			echo "</tr>";
			$rowindex++;
		}   
			
	}
	
	function generate_caps_table($sql, $esversion) 
	{
	
		$sqlresult = mysql_query($sql) or die(mysql_error());	
		$column    = array();
		$captions  = array();
		
		while($row = mysql_fetch_row($sqlresult)) 
		{		
			$colindex = 0;
			$reportdata = array();		
			foreach ($row as $data) 
			{			
				$reportdata[] = $data;	  
				$captions[]   = mysql_field_name($sqlresult, $colindex);			
				$colindex++;
			} 
			$column[] = $reportdata; 
		}		
								
		// Generate table from array
		$rowindex = 0;
		for ($i = 0, $arrsize = sizeof($column[0]); $i < $arrsize; ++$i) { 	  
			if ($captions[$i] != 'REPORTID') 
			{
				$className = 'same';
				// Get extremes
				if (is_numeric($column[0][$i])) 
				{
					$minval = $column[0][$i];
					$maxval = $column[0][$i];					
					for ($j = 0, $subarrsize = sizeof($column); $j < $subarrsize; ++$j) 
					{	 			
						if ($column[$j][$i] < $minval) 
						{
							$minval = $column[$j][$i];
						}
						if ($column[$j][$i] > $maxval) 
						{
							$maxval = $column[$j][$i];
						}
					}
				}		
				if ($minval < $maxval) 
				{
					$className = 'diff';
				}
			
				echo "<tr class='$className'>";
				echo "<td class='fieldcaption'>".$captions[$i]."</td>";
				for ($j = 0, $subarrsize = sizeof($column); $j < $subarrsize; ++$j) 
				{	 				
					$valClassName = ($className == 'diff') ? "maxvalue" : "";
					if (is_numeric($column[$j][$i])) 
					{
						if ($column[$j][$i] < $maxval) 
						{
							$valClassName = "lowervalue";
						}
					}						
					echo "<td class='value $valClassName'>".number_format($column[$j][$i], 0, '.', ',')."</td>";
				} 
				echo "</tr>";
			}
			$rowindex++;
		}   
		
	}
	
	function generate_caps_tables($reportids) 
	{	
		$repids = implode(",", $reportids);
		$colspan = count($reportids) + 1;
		generate_caps_table("SELECT * FROM reports_es20caps WHERE ReportID IN (" . $repids . ")", 2);		
		
		$es3 = false;
		$sqlresult = mysql_query("SELECT ESVERSION_MAJOR FROM reports WHERE ID in (" . $repids . ")"); 
		while($row = mysql_fetch_row($sqlresult)) 
		{				
			$esversion = $row[0];
			if ($esversion >= 3) 
			{
				//echo "<td class='value'>&nbsp</td>";
				$es3 = true;
			} else {
				//echo "<td class='value' style='color:#FF0000;'>not supported</td>";
			}
		}
		echo "</tr>";
		
		if ($es3 == true) {
			generate_caps_table("SELECT * FROM reports_es30caps WHERE ReportID IN (" . $repids . ")", 3);		
		}
	
	}
	
	function generate_extension_table($reportids, $sql, $checksql) {
	
		$sqlresult = mysql_query($sql); 
		$extcaption = array(); 

		while($row = mysql_fetch_row($sqlresult)) 
		{	
			foreach ($row as $data) 
			{
				$extcaption[] = $data;	  
			}
		}

		$extarray   = array(); 
		foreach ($reportids as $repid) {
			$sqlresult = mysql_query($checksql."= $repid"); 
			$subarray = array();
			while($row = mysql_fetch_row($sqlresult)) 
			{	
				foreach ($row as $data) 
				{
					$subarray[] = $data;	  
				}
			}
			$extarray[] = $subarray; 
		}
		
		global $devicenames;
        echo "<thead><tr>";
		echo "<td class='value'>Extension</td>"; 
		for ($i = 0; $i < sizeof($devicenames); $i++) 
		{ 	  
			echo "<td>".$devicenames[$i]."</td>";
		}
        echo "</tr></thead><tbody>";
        
		// Extension count 	
		echo "<tr><td class='value'>count</td>"; 
		for ($i = 0, $arrsize = sizeof($extarray); $i < $arrsize; ++$i) 
		{ 	  
			echo "<td class='value'>".count($extarray[$i])."</td>";
		}
		echo "</tr>"; 			
		
		// Generate table
		$arrcount = count($extcaption);
		if ($arrcount > 0) 
			{
			$colspan = count($reportids) + 1;	
			$rowindex = 0;
			foreach ($extcaption as $extension)
				{			
				// Check if missing it at least one report
				$missing = false;
				$index = 0;
				foreach ($reportids as $repid) 
				{
					if (!in_array($extension, $extarray[$index])) 
					{ 
						$missing = true;
					}
					$index++;
				}  			

				$color = ($missing) ? '#FF0000' : '#000000';		
				$className = "same";
				$index = 0;
				foreach ($reportids as $repid) 
				{
					if (!in_array($extension, $extarray[$index])) 
					{ 
						$className = "diff";
					}
					$index++;
				}				
				$index = 0;
				echo "<tr class='$className'><td class='fieldcaption'>$extension</td>";		 
				foreach ($reportids as $repid) 
				{
					if (in_array($extension, $extarray[$index])) 
					{ 
						echo "<td class='value' style='margin-left:10px;'><img src='icon_check.png'/ width=16px></td>";
					} 
					else 
					{
						echo "<td class='value' style='margin-left:10px;'><img src='icon_missing.png'/ width=16px></td>";
					}	
					$index++;
				}  
				$rowindex++;
			echo "</tr>\n"; 	
			}
		}
	
	}
            
    echo "<center>";
	
	//echo "<div>";	
	
    echo "<div id='tabs' style='font-size:12px;'>";
    echo "<ul class='nav nav-tabs'>";
    echo "	<li class='active'><a data-toggle='tab' href='#tabs-implementation'>Implementation</a></li>";
    echo "	<li><a data-toggle='tab' href='#tabs-es-ext'>GL Extensions</a></li>";
    echo "	<li><a data-toggle='tab' href='#tabs-egl-ext'>EGL Extensions</a></li>";
    echo "	<li><a data-toggle='tab' href='#tabs-compressed-formats'>Compr. Formats</a></li>";
    echo "	<li><a data-toggle='tab' href='#tabs-shader-formats'>Shader Formats</a></li>";
    echo "</ul>";	    	
	
    // Implementation

	echo "<div style='padding-top:5px;'>";
	echo "<button onclick='showDiffOnly();' class='btn btn-primary'>Toggle all / diff only</button>";				
	echo "</div>";
	
    echo "<div id='tabs-implementation' class='reporttable'>";    
	echo "	<table id='implementation' class='table table-striped table-bordered table-hover reporttable'>";    	
	echo "  <thead><tr><td></td>";
	for ($i = 0; $i < sizeof($devicenames); $i++) 
	{
		echo "<td>".$devicenames[$i]."</td>";
	}
	echo "	</tr></thead><tbody>";
	// Basic device information
	generate_table("SELECT os, screenwidth, screenheight, cpucores, cpuspeed, cpuarch, submissiondate, submitter FROM reports WHERE ID IN (" . $repids . ")");	
	// OpenGL ES info
	generate_table("SELECT GL_VENDOR, GL_RENDERER, GL_VERSION, GL_SHADING_LANGUAGE_VERSION FROM reports WHERE ID IN (" . $repids . ")");			
	// OpenGL ES caps
	generate_caps_tables($reportids);	
    echo "	</tbody></table>";
	echo "</div>";
	
	// Extensions
	echo "<table class='reporttable'><tr><td>"; 	
    echo "<div id='tabs-es-ext' class='reporttable'>";    
	echo "	<table id='extensions' class='table table-striped table-bordered table-hover reporttable'>";    		
    generate_extension_table(
		$reportids,
        "select DISTINCT name from reports_extensions rext join extensions ext on rext.extensionid = ext.id where rext.reportid IN (" . $repids . ")",
        "select name from reports_extensions rext join extensions ext on rext.extensionid = ext.id where rext.reportid");	
    echo "</tbody></table>";
	echo "</div>";
	echo "</td></tr></table>"; 		
	
    // EGL Extensions
	echo "<table class='reporttable'><tr><td>"; 	
    echo "<div id='tabs-egl-ext'>";    
	echo "	<table id='extensionsegl' class='table table-striped table-bordered table-hover reporttable'>";    		
	generate_extension_table(
		$reportids,
		"select DISTINCT name from reports_eglextensions rext join egl_extensions ext on rext.id = ext.id where rext.reportid IN (" . $repids . ")",
		"select name from reports_eglextensions rext join egl_extensions ext on rext.id = ext.id where rext.reportid");									 
    echo "	</tbody></table>";
	echo "</div>";
	echo "</td></tr></table>"; 		
	
	// Compressed texture formats
	echo "<table class='reporttable'><tr><td>"; 	
    echo "<div id='tabs-compressed-formats'>";    
	echo "	<table id='compressedformats' class='table table-striped table-bordered table-hover reporttable'>";    		
		generate_extension_table(
			$reportids,
			"select DISTINCT name from reports_compressedformats rcr join compressedformats cr on rcr.compressedformatid = cr.id where rcr.reportid IN (" . $repids . ")",
			"select name from reports_compressedformats rcf join compressedformats cf on rcf.compressedformatid = cf.id where rcf.reportid");	
    echo "	</tbody></table>";
	echo "</div>";		
	echo "</td></tr></table>"; 		

	// Shader formats
	echo "<table class='reporttable'><tr><td>"; 	
    echo "<div id='tabs-shader-formats'>";    
	// Binary shader formats
	echo "	<table id='shaderformats' class='table table-striped table-bordered table-hover reporttable'>";    		
	generate_extension_table(
		$reportids,
		"select DISTINCT name from reports_binaryshaderformats bsf join binaryshaderformats bf on bsf.BINARYSHADERFORMATID = bf.ID where bsf.reportid IN (" . $repids . ")",
		"select name from reports_binaryshaderformats bsf join binaryshaderformats bf on bsf.BINARYSHADERFORMATID = bf.id where bsf.reportid");								
    echo "	</tbody></table>";

	// Binary program formats
	echo "	<table id='programformats' class='table table-striped table-bordered table-hover reporttable'>";    		
	generate_extension_table($reportids,
							 "select DISTINCT name from reports_binaryprogramformats bpf join binaryprogramformats bp on bpf.ID = bp.ID where bpf.reportid IN (" . $repids . ")",
							 "select name from reports_binaryprogramformats bpf join binaryprogramformats bp on bpf.ID = bp.ID where bpf.reportid");							 								 
    echo "	</tbody></table>";	
	echo "</td></tr></table>";
	echo "</div>";				
	echo "</center>";
	
	include("./gles_footer.inc");
	?>     
	       
	<script>
    	$(document).ready(function() 
        {
            var tableNames = [ "#implementation", "#extensions", "#extensionsegl", "#compressedformats", "#shaderformats", "#programformats" ];
	        for (var i=0; i < tableNames.length; i++) 
            {           
                $(tableNames[i]).DataTable({
					"paging" : false,
                    "order": [], 
                    "searchHighlight": true,
//					"dom":' <"search"f><"top"l>rt<"bottom"ip><"clear">'
                });
            }
		} );	
	</script>
    
</body>
</html>