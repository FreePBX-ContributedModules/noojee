<?php

/** Copyright (C) 2012 Noojee IT Pty Ltd
*
* Author: S. Brett Sutton
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*/

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