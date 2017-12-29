<?php

function safely_display_file($filepath)
{
	if(file_exists($filepath))
	{
		require($filepath);
	}
	else
	{
		error_log('no file found for path: '.$filepath);
		http_response_code(500);
		exit;
	}
}

function display_page($name)
{
	safely_display_file(PRIVATE_DIR.'/pages/'.$name.'.php');
}

function display_template_part($fullname)
{
	safely_display_file(PRIVATE_DIR.'/template-parts/'.$fullname.'.php');
}

function display_header($name="default")
{
	display_template_part('header-'.$name);
}

function display_footer($name="default")
{
	display_template_part('footer-'.$name);
}

