<?php

include ("../../inc/includes.php");
include ("../../inc/config.php");
Session::checkLoginUser();

if (!defined("GLPI_MOD_DIR")) {
   define("GLPI_MOD_DIR", GLPI_ROOT."/plugins/mod");
}



$plugin = new Plugin();

function imgBack(){
	copy(GLPI_ROOT.'/pics/bg/back-def.jpg',GLPI_ROOT.'/pics/bg/back.jpg');
}

function imgLogo(){	
	copy(GLPI_ROOT.'/pics/login_logo_glpi-def.png',GLPI_ROOT.'/pics/login_logo_glpi.png');
}

function imgLogo_int(){	
	copy(GLPI_ROOT.'/pics/bg/fd_logo-def.png',GLPI_ROOT.'/pics/fd_logo.png');
}


//index 
function indexMod(){
	copy(GLPI_ROOT.'/index.php',GLPI_ROOT.'/index.php.bak');
	copy('./src/index.php',GLPI_ROOT.'/index.php');
}

function indexDef(){	

	copy(GLPI_ROOT.'/index.php.bak',GLPI_ROOT.'/index.php');
}


//indicadores
function indOff(){
	rename(GLPI_MOD_DIR.'/scripts/ind.js', GLPI_MOD_DIR.'/scripts/ind.js.bak');		
}

function indOn(){
	rename(GLPI_MOD_DIR.'/scripts/ind.js.bak', GLPI_MOD_DIR.'/scripts/ind.js');
}


if ($plugin->isActivated("mod")) {

	if(isset($_REQUEST['act'])) {
		$action = $_REQUEST['act'];
	}
	else { $action = '';}

   Html::header('Plugin Modifications', "", "plugins", "mod");      
		  
    echo "<div class='center' style='height:1100px; width:80%; background:#fff; margin:auto; float:none;'><br><p>\n"; 
        echo "<div id='config' class='center-interno'><br><p>
                    <span style='color:#1B2F62; font-weight:bold; font-size:18pt;'>".__('Plugin Modifications')."</span><br><br><p>\n"; 
          
   // Index page
	echo "<table class='tab_cadrehov' border='0'><tbody>\n";										
	echo     "<tr><td colspan='5'>".__('Index Page').": </td></tr>\n";

	echo "	<tr>
					<td width='15'>".__('Default').":</td>
					<td>
					<form action='config.php?act=def' method='post'> ";
				   	if ($action == 'def') {
        			 		indexDef();						
    				 	}
	echo "			<input class='submit' type='submit' value='".__('Send')."' />";
					Html::closeForm(); 
	echo "	</td><td></td></tr>\n";
	
	echo "	<tr><td>".__('Mod').":</td>
				<td >
					<form action='config.php?act=mod' method='post'> ";
				   	if ($action == 'mod') {
        			 		indexMod();						
    				 	}
	echo "		<input class='submit' type='submit' value='".__('Send')."' />";
					Html::closeForm(); 
    echo "	</td><td></td></tr>\n";
    echo "</tbody></table>\n";	
    
    //Index Page


	// Indicators
	echo "<table class='tab_cadrehov' border='0'>
			<tbody>\n";										
	echo "<tr>
				<td width='70'>".__('Indicators').": </td>\n
				<td width='50'> <img src='src/indicators.png' height='32' alt='ind'> </td>\n";
	echo "	<td width='100'>
					<form action='config.php?act=indon' method='post'> ";
				   	if ($action == 'indon') {
        			 		indOn();						
    				 	}
	echo "			<input class='submit' type='submit' value='"._x('button','Enable')."' />";
					Html::closeForm(); 
	echo "	</td>\n";
	
	echo "	<td width='500'>
					<form action='config.php?act=indoff' method='post'> ";
				   	if ($action == 'indoff') {
        			 		indOff();						
    				 	}
	echo "			<input class='submit' type='submit' value='".__('Disable')."' />";
					Html::closeForm(); 
	echo "	</td>
			</tr>\n";
	//Indicators		    
    

   // Background
	echo "<table class='tab_cadrehov' border='0'>
    <tbody>\n";		
	echo "<tr>
	        <td colspan='5'>Background: </td> 
	    </tr>\n";
	echo "<tr>				
	        <td width='210'> <img src='../../pics/bg/back.jpg?v=".Date("Y.m.d.G.i.s")." width='180' height='130' /> </td>
	        <td width='100'>".__('Upload').":</td>
	        <td>
	            <form action='upfile.php' method='post' enctype='multipart/form-data' class='fileupload'>
	                <input type='file' name='photo' size='25' /><p><br>
	                <input class='submit' type='submit' name='submit' value='".__('Send')."' />";
	            Html::closeForm(); 
	echo "	</td>
	        <td>
	            <form action='config.php?act=back' method='post'> ";
	                if ($action == 'back') {
	                     imgBack();						
	                 }
	echo "		<input class='submit' type='submit' value='".__('Default')."' />";
	            Html::closeForm(); 
	echo "	</td>
	    </tr>\n";
	// Background	
	
	// Logo index		
	echo "<tr><td colspan='5'></td></tr>
	<tr>
	    <td colspan='5'>Logo - Index (png 150 X 150px): </td> 
	</tr>\n";
	
	echo "<tr>				
	    <td width='210'> <img src='../../pics/login_logo_glpi.png?v=".Date("Y.m.d.G.i.s")." width='180' height='130' /> </td>
	    <td width='100'>".__('Upload').":</td>
	    <td>
	        <form action='uplogo.php' method='post' enctype='multipart/form-data' class='fileupload'>
	            <input type='file' name='photo' size='25' /><p><br>
	            <input class='submit' type='submit' name='submit' value='".__('Send')."' />";
	        Html::closeForm(); 
	echo "	</td>
	    <td>
	        <form action='config.php?act=logo' method='post'> ";
	            if ($action == 'logo') {
	                 imgLogo();						
	             }
	echo "		<input class='submit' type='submit' value='".__('Default')."' />";
	        Html::closeForm(); 
	echo "	</td>
	</tr>\n";
	// Logo index		
	
	// Logo internal 100x55		
	echo "<tr><td colspan='5'></td></tr>
	<tr>
	    <td colspan='5'>Logo (png 100 X 55px): </td> 
	</tr>\n";
	
	echo "<tr>				
	    <td width='210' style='background:#c9c9c9;'> <img src='../../pics/fd_logo.png?v=".Date("Y.m.d.G.i.s")." height='100' /> </td>
	    <td width='100'>".__('Upload').":</td>
	    <td>
	        <form action='uplogo_int.php' method='post' enctype='multipart/form-data' class='fileupload'>
	            <input type='file' name='photo2' size='25' /><p><br>
	            <input class='submit' type='submit' name='submit' value='".__('Send')."' />";
	        Html::closeForm(); 
	echo "	</td>
	    <td>
	        <form action='config.php?act=logo_int' method='post'> ";
	            if ($action == 'logo_int') {
	                 imgLogo_int();						
	             }
	echo "		<input class='submit' type='submit' value='".__('Default')."' />";
	        Html::closeForm(); 
	echo "	</td>
	</tr>\n";
	// Logo internal	
	
	echo "</tbody></table>\n";
	
	echo "		<div id='back' class='center' style='margin-top:30px;'>
							<a class='vsubmit' type='submit' onclick=\"window.location.href = '". $CFG_GLPI['root_doc'] ."/front/plugin.php';\" >  ".__('Back')." </a> 
						
				</div>
	</div>\n";
                

	
}

?>
