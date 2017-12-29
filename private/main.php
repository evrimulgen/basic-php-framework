<?php

const PRIVATE_DIR = __DIR__;

// get current environment
if(!file_exists(__DIR__."/env.php"))
{
	error_log("No env.php file found. Creating development env.php.");
	copy(__DIR__."/env.php.example", $env_loc);
}
include(__DIR__."/env.php");
if(empty($environment))
{
	error_log("No environment variable found. Using \"development\" as default");
	$environment = "development";
}

//load credentials
$credentials = json_decode(file_get_contents(__DIR__."/credentials.json"), true)[$environment];

//connect to database
$mysql_creds = $credentials["mysql"];
if(!empty($mysql_creds) && !empty($mysql_creds["host"]))
{
	$mysql = new mysqli($mysql_creds["host"], $mysql_creds["username"], $mysql_creds["password"], $mysql_creds["schema"], $mysql_creds["port"]);
	if($mysql->connect_errno)
	{
		error_log("unable to connect to mysql: ".$mysql->connect_error);
		http_response_code(500);
		exit;
	}
}

//require functions
$func_files = scandir(__DIR__."/functions");
foreach($func_files as $file)
{
	$filepath = __DIR__."/functions/".$file;
	if(preg_match('/.*\.php/', $file)===1 && file_exists($filepath))
	{
		require_once($filepath);
	}
}

//require classes
$class_files = scandir(__DIR__."/classes");
foreach($class_files as $file)
{
	$filepath = __DIR__."/classes/".$file;
	if(preg_match('/.*\.php/', $file)===1 && file_exists($filepath))
	{
		require_once($filepath);
	}
}

//resume User session
User::resume_session();

