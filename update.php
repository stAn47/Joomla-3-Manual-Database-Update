<?php

//if this does trouble, change to false... 
//upload to: \administrator\components\com_admin\sql\updates\mysql\update.php
//and run via web or CLI

$path = realpath(__DIR__. DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' .DIRECTORY_SEPARATOR.'..' .DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..');


//to provide out-of-timeout support: 
if (function_exists('ignore_user_abort')) @ignore_user_abort(true);
if (function_exists('set_time_limit')) @set_time_limit(0);

if (file_exists($path.DIRECTORY_SEPARATOR.'configuration.php')) {
	include($path.DIRECTORY_SEPARATOR.'configuration.php'); 
	
}
else {
	die('Config not found in: '.$path.DIRECTORY_SEPARATOR.'configuration.php'); 
}

$config = new JConfig; 
$mysqli = new mysqli($config->host, $config->user, $config->password, $config->db);

if ($mysqli->connect_errno) {
	printf("Connect failed: %s\n", $mysqli->connect_error);
	exit();
}

$files = scandir(__DIR__.DIRECTORY_SEPARATOR); 



$OK = '<span style="color: green;">OK</span>'."<br />\n";
$ERR = '<span style="color: red;">Error {e}</span>'."<br />\n";

foreach ($files as $f) {
	$pa = pathinfo($f); 
	if (empty($pa['extension'])) continue; 
	if ($pa['extension'] !== 'sql') continue; 
	
	//$sql = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.$f); 
	$filename = __DIR__.DIRECTORY_SEPARATOR.$f;
	$q = file_get_contents($filename); 
	
	if (empty($q)) continue; 
	$q = str_replace('#__', $config->dbprefix, $q); 
	
	
	echo $q; 
	
	try {
		
		$res = $mysqli->multi_query($q);
		
		if( $mysqli->errno ) {
			$error = str_replace('{e}', $mysqli->error, $ERR); 
			echo $error; 	
		}
		else {
			echo $OK; 
		}
		
		while($mysqli->more_results() && $mysqli->next_result()) {
			
			if( $mysqli->errno ) {
				$error = str_replace('{e}', $mysqli->error, $ERR); 
				echo $error; 	
			}
			else {
				echo $OK; 
			}
			
			$extraResult = $mysqli->use_result();
			if($extraResult instanceof mysqli_result){
				$extraResult->free();
			}
		}

		$extraResult = $mysqli->use_result();
		if($extraResult instanceof mysqli_result){
			$extraResult->free();
		}
		
		if( $mysqli->errno ) {
			$error = str_replace('{e}', $mysqli->error, $ERR); 
			echo $error; 	
		}
		else {
			echo $OK; 
		}
		
		
		

	}
	catch (Exception $e) {
		$error = str_replace('{e}', (string)$e, $ERR); 
		echo $error; 	
	}
	
	
}



$mysqli = null; 

echo 'Finished'; 
die(0); 