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


class Ini
{
	/**
	 * Stores the ini properties.
	 */
	private $ini_array = array();
	private $inipath = "";

	function  __construct($inipath)
	{
		$this->inipath = $inipath;
	}

	/**
	 * Reads an ini file preserving comments and order.
	 *
	 * @param string $file name of the ini file to open.
	 * @param array[][] $ini_array - a two dimensional array which contains the ini files settings.
	 * 			The first dimension contains the 'section' name and the second dimension the key.
	 * To access a value use $valueOfKeyname = $ini_array['sectionname']['keyname'];
	 *
	 * Comments are also stored into the associative array with a key of [$section][Comment_<nn>] where <nn> is the line number.
	 *
	 * @return number 1 on success, -2 if the file can't be opened
	 */
	function read()
	{
		$null = "";
		$this->ini_array=$null;
		$first_char = "";
		$section=$null;
		$comment_chars=";";
		$include_char="#";
		$num_comments = "0";
		$num_includes = "0";
		$num_newline = "0";

		//Read to end of file with the newlines still attached into $inipath
		$file = @file($this->inipath);
		if ($file === false)
		{
			return -2;
		}
		// Process all lines from 0 to count($file)
		for ($lineno=0; $lineno<@count($file); $lineno++)
		{
			$line=@trim($file[$lineno]);
			$first_char = @substr($line,0,1);
			if ($line)
			{
				// Check for a section
				if ((@substr($line,0,1)=="[") and (@substr($line,-1,1))=="]")
				{
					$section=@substr($line,1,@strlen($line)-2);
					$num_comments = 0;
					$num_newline = 0;
				}
				// Check for a comment
				else if ((stristr($comment_chars, $first_char) == true))
				{
					$this->ini_array[$section]["Comment_".$num_comments]=$line;
					$num_comments = $num_comments +1;
				}
				// Check for a #include
				else if ((stristr($include_char, $first_char) == true))
				{
					$this->ini_array[$section]["Include_".$num_includes]=$line;
					$num_includes = $num_includes +1;
				}
				else
				{
					// Look for the = char to allow us to split the section into key and value
					$line=@explode("=",$line);
					$key=@trim($line[0]);
					unset($line[0]);
					$value=@trim(@implode("=",$line));
					// look for the new lines
					if ((@substr($value,0,1)=="\"") and (@substr($value,-1,1)=="\"")) {
						$value=@substr($value,1,@strlen($value)-2);
					}

					$this->ini_array[$section][$key]=$value;

				}
			}
			else
			{
				$this->ini_array[$section]["Newline_".$num_newline]=$line;
				$num_newline = $num_newline +1;
			}
		}
		return 1;
	}

	function write()
	{
		$content = "";

		foreach ($this->ini_array as $key=>$elem)
		{
			if (is_array($elem))
			{
				if ($key != '')
				{
					$content .= "[".$key."]\r\n";
				}

				foreach ($elem as $key2=>$elem2)
				{
					if ($this->beginsWith($key2,'Comment_') == 1 && $this->beginsWith($elem2,';'))
					{
						$content .= $elem2."\r\n";
					}
					else if ($this->beginsWith($key2,'Include_') == 1 && $this->beginsWith($elem2,'#'))
					{
						$content .= $elem2."\r\n";
					}
					else if ($this->beginsWith($key2,'Newline_') == 1 && ($elem2 == ''))
					{
						$content .= $elem2."\r\n";
					}
					else
					{
						$content .= $key2."=".$elem2."\r\n";
					}
				}
			}
			else
			{
				$content .= $key."=".$elem."\r\n";
			}
		}

		$tmpfile = $this->inipath . rand();

		if (!$handle = fopen($tmpfile, 'w'))
		{
			return -2;
		}
		if (!fwrite($handle, $content))
		{
			return -2;
		}
		fclose($handle);

		// Try to create a new backup file
		for ($i = 0; $i < 1000; $i++)
		{
			$backup = $this->inipath.".".$i.".bak";
			if (file_exists($backup) == false)
			{
				// backup the original ini file before we over-write it.
				copy($this->inipath, $backup);
				break;
			}
		}
		
		if (!rename($tmpfile, $this->inipath))
		{
			return -2;
		}

		return 1;
	}

	function beginsWith( $str, $sub )
	{
		return ( substr( $str, 0, strlen( $sub ) ) === $sub );
	}

	/**
	 * Searches for a given key in the given section which has been commented out.
	 * If it is found the comment character is removed and the key is created with its
	 * previously commented out value.
	 * @param string $section
	 * @param string $key
	 * @param array $ini_array the array read using read_ini_file
	 *
	 * @return boolean true if the key was found and restored otherwise false.
	 */
	function uncomment($section, $key)
	{
		$found = false;
		if ($this->ini_array != null && count($this->ini_array) > 0)
		{
			$section_array = $this->ini_array[$section];
			if ($section_array != null && count($section_array) > 0)
			{
				foreach ($section_array  as  $key2=>$value)
				{
					if ($this->beginsWith($key2,'Comment_') == 1 && $this->beginsWith($value,';'.$key))
					{
						// We found our key so lets un-comment it.
						// We need to replace the exiting 'Comment_' key with the new key but maintain its position.
						$this->replaceKey($section, $key2, $key);
						$found = true;
						break;
					}
				}
			}
		}
		// else the section doen't exists so there is nothing to uncomment.
		return $found;
	}

	/**
	 * Tests if the given key exists in the given section.
	 * @param unknown_type $section
	 * @param unknown_type $key
	 */
	function keyExists($section, $key)
	{
		$exists = true;
		
		return (array_key_exists($section, $this->ini_array)
		&& array_key_exists($section, $this->ini_array[$section])
		&& $this->ini_array[$section][$key] != null
		&& $this->ini_array[$section][$key] != "");
	}
	
	function setKeyValue($section, $key, $value)
	{
		$this->ini_array[$section][$key] = $value;
	}

	function getKeyValue($section, $key)
	{
		return $this->ini_array[$section][$key];
	}
	
	
	function getSection($section)
	{
		return new Section($this, $section);
	}

	function replaceKey($section, $oldKey, $newKey)
	{
		$section_array = $this->ini_array[$section];
		$keys = array_keys($section_array);
		$values = array_values($section_array);

		foreach ($keys as $keyIndex => $v)
		{
			if ($keys[$keyIndex] == $oldKey)
			{
				$keys[$keyIndex] = $newKey;
				// strip the comment char from the value
				$decommented = trim(substr($values[$keyIndex], 1));
				// now remove the key
				$dekeyed = trim(substr($decommented, strlen($newKey)));
				// now remove the equals sign
				$values[$keyIndex] = trim(substr($dekeyed, 1));
				break;
			}
			$keyIndex++;
		}
		// Now replace the updated section
		$this->ini_array[$section] = array_combine($keys, $values);
	}

	function dump()
	{
		print_r($this->ini_array);
	}
	
	/*
	* Test code.
	*/
	static function test()
	{
		$ini_file = new Ini("test/resources/manager2.conf");
	
		$ini_file->read();
	
		$general = $ini_file->getSection("general");
	
		$general->uncomment("enabled");
		$general->uncomment("bindaddr");
		$general->uncomment("bindport");
		$general->uncomment("prefix");
	
		$general->setKeyValue("enabled", "yes");
	
		// We try not to change existing settings.
		if (!$general->keyExists("bindport"))
			$general->setKeyValue("bindport", "8080");
	
		if (!$general->keyExists("bindaddr") || $general->getKeyValue("bindaddr") == "127.0.0.1" )
			$general->setKeyValue("bindaddr", "0.0.0.0");
	
		if (!$general->keyExists("prefix"))
			$general->setKeyValue("prefix", "asterisk");
	
		$ini_file->dump();
		$ini_file->write();
	}
	

}

class Section
{
	private $sectionName;
	private $ini;

	public function __construct($ini, $sectionName)
	{
		$this->sectionName = $sectionName;
		$this->ini = $ini;
	}


	/**
	 * Tests if the given key exists in the given section.
	 * @param unknown_type $section
	 * @param unknown_type $key
	 */
	function keyExists($key)
	{
		return $this->ini->keyExists($this->sectionName, $key);
	}
	
	function setKeyValue($key, $value)
	{
		$this->ini->setKeyValue($this->sectionName, $key, $value);
	}

	function getKeyValue($key)
	{
		return $this->ini->getKeyValue($this->sectionName, $key);
	}
	
	function uncomment($key)
	{
		$this->ini->uncomment($this->sectionName, $key);
	}
	
	
}

// Run the test scripts.
//Ini::test();

?>