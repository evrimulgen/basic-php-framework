<?php

function set_api_params_provider($provider_func)
{
	$GLOBALS["__params_provider_func"] = $provider_func;
}

function get_api_params_provider()
{
	if(!isset($GLOBALS["__params_provider_func"]))
	{
		return null;
	}
	$provider_func = $GLOBALS["__params_provider_func"];
	if($provider_func===null)
	{
		return null;
	}
	return $provider_func;
}

function get_api_params(&$error = null)
{
	$provider_func = get_api_params_provider();
	if($provider_func==null)
	{
		return $_POST;
	}
	return $provider_func($error);
}

function do_api_call($version, $endpoint, $params)
{
	$old_param_provider = get_api_params_provider();
	set_api_params_provider(function(&$error){
		return $GLOBALS["__tmp_api_params"];
	});
	$GLOBALS["__tmp_api_params"] = $params;
	$file_path = PRIVATE_DIR.'/api/'.$version.'/'.$endpoint.'.php';
	$result = null;
	if(file_exists($file_path))
	{
		$result = include($file_path);
	}
	unset($GLOBALS["__tmp_api_params"]);
	set_api_params_provider($old_param_provider);
	return $result;
}

function define_route($pattern, $func)
{
	$routes = array();
	if(isset($GLOBALS['routes']))
	{
		$routes = $GLOBALS['routes'];
	}
	$routes[$pattern] = $func;
	$GLOBALS['routes'] = $routes;
}

function perform_routing()
{
	list($uri) = explode('?', $_SERVER['REQUEST_URI']);
	$routes = array();
	if(isset($GLOBALS['routes']))
	{
		$routes = $GLOBALS['routes'];
	}
	foreach($routes as $pattern => $func)
	{
		$pattern = '/'.str_replace('/', '\/', $pattern).'/';
		if(preg_match($pattern, $uri, $matches)==1)
		{
			$func($matches);
			return true;
		}
	}
	if(preg_match('/^\/api\/v([0-9]+(?:\.[0-9]+)?)\/(.*)$/', $uri, $matches)==1)
	{
		$file_path = PRIVATE_DIR.'/api/v'.$matches[1].'/'.$matches[2].'.php';
		if(file_exists($file_path))
		{
			$result = include($file_path);
			if(isset($result))
			{
				header('Content-Type: application/javascript');
				echo json_encode($result);
			}
			return true;
		}
	}
	return false;
}

?>
