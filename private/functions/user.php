<?php

function get_logged_in_user()
{
	if(isset($GLOBALS["__logged_in_user"]))
	{
		return $GLOBALS["__logged_in_user"];
	}
	return null;
}

function is_user_logged_in()
{
	if(get_logged_in_user()!=null)
	{
		return true;
	}
	return false;
}

?>
