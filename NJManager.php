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

class NJManager
{
	// FreePBX 2.10 + uses a class for "SOME" of the manager interface.
	
	function findAccount($name)
	{
		$result = null;
		// Get the list of managers
		if(($managers = manager_list()) !== null)
		{
			//Search for manager connection
			foreach($managers as $manager)
			{
				if($manager['name'] == $name )
				{
					$result = $manager;
					break;
				}
			}
		}
		return $result;
	}
	
	function createAccount($name)
	{
		$manager = $this->findAccount($name);
		if ($manager == null)
		{
			$this->addAccount($name);
		}
	}

	function deleteAccount($name)
	{
		$manager = $this->findAccount($name);
		if ($manager != null)
		{
			manager_del($name);
			needreload();
		}
	}
	


	function addAccount($name, $public=true)
	{
		// Create a default manager account
		$name = $name;
		$secret = $this->generatePassword(12);
		$deny = '0.0.0.0/0.0.0.0';
		if ($public == true)
		$permit = '127.0.0.1/255.255.255.0&10.0.0.0/255.0.0.0&172.16.0.0/255.240.0.0&192.168.0.0/255.255.0.0';
		else
		$permit = '127.0.0.1/255.255.255.0';
		// 	$read = 'system,call,log,verbose,command,agent,user,config,command,dtmf,reporting,cdr,dialplan,originate';
		// 	$write = 'system,call,log,verbose,command,agent,user,config,command,dtmf,reporting,cdr,dialplan,originate';

		$read = 'call,command,command,dtmf,dialplan,originate';
		$write = 'system,call,command,user,command,dtmf,dialplan,originate';

		manager_add($name,$secret,$deny,$permit,$read,$write);
		needreload();
	}

	function generatePassword ($length = 8)
	{
		// start with a blank password
		$password = "";
	
		// define possible characters - any character in this string can be
		// picked for use in the password, so if you want to put vowels back in
		// or add special characters such as exclamation marks, this is where
		// you should do it
		$possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";
	
		// we refer to the length of $possible a few times, so let's grab it now
		$maxlength = strlen($possible);
	
		// check for length overflow and truncate if necessary
		if ($length > $maxlength) {
			$length = $maxlength;
		}
	
		// set up a counter for how many characters are in the password so far
		$i = 0;
	
		// add random characters to $password until $length is reached
		while ($i < $length)
		{
			// pick a random character from the possible ones
			$char = substr($possible, mt_rand(0, $maxlength-1), 1);
	
			// have we already used this character in $password?
			if (!strstr($password, $char))
			{
				// no, so it's OK to add it onto the end of whatever we've already got...
				$password .= $char;
				// ... and increase the counter by one
				$i++;
			}
	
		}
	
		// done!
		return $password;
	
	}
	

}