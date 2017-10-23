<?php

function get_client_ip()
{
	if(!empty($_SERVER['HTTP_CLIENT_IP']))
	{
		return $_SERVER['HTTP_CLIENT_IP'];
	}
	else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
	{
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	return $_SERVER['REMOTE_ADDR'];
}

function set_meta_value($name, $value)
{
	global $mysql;
	$escaped_name = $mysql->real_escape_string($name);
	$escaped_value = $mysql->real_escape_string($value);
	
	$mysql->query('insert into meta (name,value) values("'.$escaped_name.'", "'.$escaped_value.'") '.
					'on duplicate key update value="'.$escaped_value.'"');
}

function get_meta_value($name, $default_value=null)
{
	$mysql = $GLOBALS["mysql"];
	$escaped_name = $mysql->real_escape_string($name);
	
	$result = $mysql->query('select * from meta where name="'.$escaped_name.'"');
	if($result==false)
	{
		return $default_value;
	}
	else if($result->num_rows==0)
	{
		return $default_value;
	}
	$row = $result->fetch_assoc();
	$value = $row['value'];
	$result->free();
	return $value;
}

function random_hexstring($length=32)
{
	$letters = "abcdefghijklmnopqrstuvwxyz1234567890";
	$string = "";
	for($i=0; $i<$length; $i++)
	{
		$randIndex = rand(0, strlen($letters)-1);
		$string = $string . substr($letters, $randIndex, 1);
	}
	return $string;
}

function str_startswith($haystack, $needle)
{
    return strpos($haystack, $needle) === 0;
}

function str_endswith($haystack, $needle)
{
    return strrpos($haystack, $needle) + strlen($needle) === strlen($haystack);
}

?>
