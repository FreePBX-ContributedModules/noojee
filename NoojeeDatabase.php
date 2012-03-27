<?php
require_once ("NJLogging.php");
/**
 * Provides a management interface for access to the Noojee specific tables.
 * @author bsutton
 *
 */

/*
 $njdb = new NoojeeDatabase();
print_r($njdb->compareVersion("4.1.1", "1.0.0"));
$njdb->error_log("Starting Noojee DB upgrade");
$njdb->upgradeDatabase();
*/

class NoojeeDatabase
{
	private $expectedVersion = "@version@"; // @version@";

	function upgradeDatabase()
	{
		global $db;

		NJLogging::info_log("Starting Noojee DB upgrade");
		$currentVersion = $this->getVersion();
		NJLogging::debug_log ( "Version=".$currentVersion);
		if ($this->compareVersion($currentVersion, "1.0.0") == 0)
		{
			NJLogging::info_log ("Creating database");
			$this->createDatabase();
		}

		if ($this->compareVersion( "4.1.1", $currentVersion) > 0)
		{
			NJLogging::info_log ("Upgrading to 4.1.1");
			$this->upgradeTo411();
		}

		if ($this->compareVersion( "4.1.3", $currentVersion) > 0)
		{
			NJLogging::info_log ("Upgrading to 4.1.3");
			$this->upgradeTo413();
		}

		if ($this->compareVersion( "4.1.4", $currentVersion) > 0)
		{
			NJLogging::info_log ("Upgrading to 4.1.4");
			$this->upgradeTo414();
		}
		
		$currentVersion = $this->getVersion();
		if ($this->compareVersion($currentVersion, $this->expectedVersion) != 0)
		{
			NJLogging::error_log ("currentVersion " . $currentVersion . " not equal to expected version ".$this->expectedVersion);
			throw new Exception("The database upgrade failed. Final version =".$currentVersion." expected version=".$this->expectedVersion);
		}
	}

	function upgradeTo411()
	{
		global $db;
		$sql = _("alter table tblNoojeeModule
		add column njrMajorVersion int");

		$sth = $db->prepare($sql);
		$res = $db->execute($sth);
		// Always check that result is not an error
		if (PEAR::isError($res))
		{
			$msg = "Noojee: error:Attempting to update SQL table tblNoojeeModule" . getSQLError($res);
			NJLogging::error_log ( $msg );
			throw new Exception ($msg);
		}
		// set the default njr version to 4.
		$this->updateNJRMajorVersion(4);

		$this->updateVersion("4.1.1");

	}
	
	function upgradeTo413()
	{
		$this->updateVersion("4.1.3");
	}

	function upgradeTo414()
	{
		$this->updateVersion("4.1.4");
	}
	
	/**
	 * Creates the 1.0.0 version of the database.
	 */
	function createDatabase()
	{
		global $db;

		$sql = _("create table IF NOT EXISTS tblNoojeeModule
			(
				MajorVersion int,
				MinorVersion int,
				MicroVersion int,
				FaxReturnNumber varchar(32),
				ReceiptSpool varchar(1024)
			)");

		$sth = $db->prepare($sql);
		$res = $db->execute($sth);
		// Always check that result is not an error
		if (PEAR::isError($res))
		{
			$msg = "Noojee: error:Attempting to create SQL table tblNoojeeModule" . getSQLError($res);
			NJLogging::error_log ( $msg);
			throw new Exception ($msg);
		}


		$sql = _("insert into tblNoojeeModule (MajorVersion, MinorVersion, MicroVersion, FaxReturnNumber, ReceiptSpool)
					values (1,0,0,'Noojee Fax', '/var/spool/njfax/incoming')");

		$sth = $db->prepare($sql);
		$res = $db->execute($sth);
		// Always check that result is not an error
		if (PEAR::isError($res))
		{
			$msg =  "Noojee: error:Attempting to load defaults into SQL table tblNoojeeModule" . getSQLError($res);
			NJLogging::error_log ($msg );
			throw new Exception ($msg );
		}
		needreload();
	}

	function getVersion()
	{
		global $db;
		$version = "1.0.0";
		$row = array();
		$sql = "select MajorVersion, MinorVersion, MicroVersion from tblNoojeeModule";
		$res =& $db->query($sql);
		// Always check that result is not an error
		if (PEAR::isError($res))
		{

			if ($res->getCode() != -18) // -18 means  no such table
			{
				echo "<b>Error: Attempting to obtain data from SQL tblNoojeeModule Error=" . getSQLError($res) . "<br>";
			}
			// else must be version 1.0 if the table doesn't exist.
		}
		else
		{
			$res->fetchInto($row,DB_FETCHMODE_ASSOC);
			$major = $row["MajorVersion"];
			$minor = $row["MinorVersion"];
			$micro = $row["MicroVersion"];
			$version = $major.".".$minor.".".$micro;
		}

		return $version;
	}

	function updateVersion($newVersion)
	{
		global $db;
		$version = explode(".", $newVersion);
		if (count($version) != 3)
		{
			throw new Exception("The version (".$newVersion.") does not contain three parts");
		}


		$sql = _("update tblNoojeeModule set MajorVersion = $version[0], MinorVersion=$version[1], MicroVersion=$version[2]");

		$sth = $db->prepare($sql);
		$res = $db->execute($sth);
		// Always check that result is not an error
		if (PEAR::isError($res))
		{
			$msg = "Noojee: error:Attempting to update tblNoojeeModule version number" . getSQLError($res);
			NJLogging::error_log ( $msg );
			throw new Exception ( $msg );
			
		}

	}

	/**
	 * Returns 0 if the two versions are the same.
	 * Returns 1 if the lhs is greater than the rhs
	 * Returns -1 if the lhs is less than the rhs
	 *
	 * @param  $lhs
	 * @param  $rhs
	 */
	function compareVersion($lhs, $rhs)
	{
		$result = 0;
		if ($lhs != $rhs)
		{
			$lhsParts = explode(".", $lhs);
			$rhsParts = explode(".", $rhs);

			if (count($lhsParts) != 3)
			throw new Exception("The version (".$lhs.") does not contain three parts");
			if (count($rhsParts) != 3)
			throw new Exception("The version (".$rhs.") does not contain three parts");

			if ($lhsParts[0] == $rhsParts[0]) // check major
			{
				if ($lhsParts[1] == $rhsParts[1]) // check major
				{
					if ($lhsParts[2] != $rhsParts[2]) // check major
					$result =  ($lhsParts[2] > $rhsParts[2] ? 1 : -1);

				}
				else
				$result =  ($lhsParts[1] > $rhsParts[1] ? 1 : -1);
			}
			else
			$result =  ($lhsParts[0] > $rhsParts[0] ? 1 : -1);

		}
		return $result;

	}

	function getNJRMajorVersion()
	{
		global $db;
		$version = 4;
		$row = array();
		$sql = "select njrMajorVersion from tblNoojeeModule";
		$res =& $db->query($sql);
		// Always check that result is not an error
		if (PEAR::isError($res))
		{
			throw new Exception("Attempting to obtain njrMajorVersion from SQL tblNoojeeModule Error=" . getSQLError($res));
		}
		else
		{
			$res->fetchInto($row,DB_FETCHMODE_ASSOC);
			$version = $row["njrMajorVersion"];
		}

		return $version;

	}

	function updateNJRMajorVersion($njrMajorVersion)
	{
		global $db;
		$sql = _("update tblNoojeeModule set njrMajorVersion = ".$njrMajorVersion);

		$sth = $db->prepare($sql);
		$res = $db->execute($sth);
		// Always check that result is not an error
		if (PEAR::isError($res))
		{
			$msg = "Noojee: error:Attempting to update njrMajorVersion table tblNoojeeModule" . getSQLError($res);
			NJLogging::error_log ( $msg );
			throw new Exception ($msg );
		}
	}

	function getSQLError($res)
	{
		return sprintf('[%s: message="%s" code=%d info="%s"]', $res->getType(),
		$res->getMessage(), $res->getCode(), $res->getUserInfo());

	}



}