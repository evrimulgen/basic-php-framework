<?php

require_once(__DIR__.'/private/main.php');

//home page
define_route('^/$', function(){
	display_page('home');
});

//Do the routing
$result = perform_routing();
if(!$result)
{
	http_response_code(404);
}

