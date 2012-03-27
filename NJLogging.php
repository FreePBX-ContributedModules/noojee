<?php

class NJLogging
{
	static function error_log($msg)
	{
		error_log("noojee (error): ".$msg);
	}

	static function debug_log($msg)
	{
		global $debug;
		
		if ($debug)
		{
			error_log("noojee (debug): ".$msg);
		}
	}

	static function info_log($msg)
	{
		error_log("noojee (info): ".$msg);
	}
	
	
}

?>