<html><head>
	<meta http-equiv="Content-Type" content="text/html; charset="ISO-8859-1">
	<title>OpenGL ES Hardware Database - � 2013-2015 by Sascha Willems</title>
	<link rel="stylesheet" href="./libs/bootstrap.min.css">
	<link rel="stylesheet" href="./libs/dataTables.bootstrap.css">
	<link rel="stylesheet" href="./libs/dataTables.searchHighlight.css">
	<link rel="stylesheet" type="text/css" href="style_opengles.css">
	<script src="./libs/jquery.min.js"></script>
	<script src="./libs/jquery-ui/jquery-ui.min.js"></script>
	<script src="./libs/jquery.highlight.js"></script>
	<script src="./libs/jquery.dataTables.min.js"></script>
	<script src="./libs/dataTables.bootstrap.js"></script>
	<script src="./libs/dataTables.searchHighlight.min.js"></script>	
	<script src="./libs/bootstrap.min.js"></script>	
	<script>
		$(function() {
			$( "#tabs" ).tabs();
		});
	</script> 	
</head>
<body>
<!-- Bootstrap nav bar -->
	<nav class="navbar navbar-default navbar-fixed-top">
	  <div class="container-fluid">
		<div class="navbar-header">
		  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span> 
		  </button>
		  <a href="http://opengles.gpuinfo.org">
			<img src="./images/opengles.png" height="48px">
		  </a>
		</div>
		<div class="collapse navbar-collapse" id="myNavbar">
		  <ul class="nav navbar-nav">
		
			<li><a href='./gles_listreports.php'>Reports</span></a></li>
			
			  <li class="dropdown">
				  <a class="dropdown-toggle" data-toggle="dropdown" href="#">Extensions
				  <span class="caret"></span></a>
				  <ul class="dropdown-menu">
					<li><a href='./gles_extensions.php'>OpenGL ES</a></li>
					<li><a href='./gles_egl_extensions.php'>EGL</a></li>
				  </ul>
			  </li>
						
			<li><a href='./gles_compressedformats.php'>Compr. formats</a></li>
			<li><a href='./gles_devicefeatures.php'>Device features</a></li>	
			<li><a href='https://play.google.com/store/apps/details?id=de.saschawillems.glescapsviewer' target='_blank'>Download</a></li>			
			<li><a href="./gles_about.php">About</a></li> 			
			
		  </ul>
		  <ul class="nav navbar-nav navbar-right">
			  <li class="dropdown">
				  <a class="dropdown-toggle" data-toggle="dropdown" href="#">gpuinfo.org
				  <span class="caret"></span></a>
				  <ul class="dropdown-menu">
					<li><a href="http://opengl.gpuinfo.org">OpenGL</a></li>
					<li><a href="http://opengles.gpuinfo.org">OpenGL ES</a></li>
					<li><a href="http://vulkan.gpuinfo.org">Vulkan</a></li> 
                    <li role="separator" class="divider"></li>
					<li><a href="http://www.gpuinfo.org">Launchpad</a></li> 
				  </ul>
			  </li>
		  </ul>		  
		</div>
	  </div>
	</nav>
