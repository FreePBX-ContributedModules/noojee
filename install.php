<?php

require_once ("Ini.php");
require_once ("NoojeeDatabase.php");
require_once ("NJLogging.php");
require_once ("NJManager.php");
/** Create a Manager Account for each of our products **/

$noojee_installer = new NoojeeInstaller();
$noojee_installer->upgrade();

class NoojeeInstaller
{

	function upgrade()
	{
		$noojeeDB = new NoojeeDatabase();
		$noojeeDB->upgradeDatabase();
		$noojeeManager = new NJManager();

		if ($noojeeManager->findAccount("NJReception") == null)
		{
			$noojeeManager->addAccount("NJReception");
		}
		if ($noojeeManager->findAccount("NJFax") == null)
		{
			$noojeeManager->addAccount("NJFax", false);
		}
		if ($noojeeManager->findAccount("NJClick") == null)
		{
			$noojeeManager->addAccount("NJClick");
		}
		$this->enableAJAM();

		$this->enableParking();

		needreload();
	}

	function enableAJAM()
	{

		$ini = new Ini("/etc/asterisk/http.conf");
		$ini->read();
		$general = $ini->getSection("general");

		$general->uncomment("enabled");
		$general->uncomment( "bindaddr");
		$general->uncomment( "bindport");
		$general->uncomment("prefix");

		$general->setKeyValue("enabled", "yes");

		// We try not to change existing settings.
		if (!$general->keyExists("bindport"))
		{
			$general->setKeyValue("bindport", "8088");
		}

		if (!$general->keyExists("bindaddr") || $general->getKeyValue("bindaddr") == "127.0.0.1" )
		{
			$general->setKeyValue("bindaddr", "0.0.0.0");
		}

		if (!$general->keyExists("prefix"))
		{
			$general->setKeyValue("prefix", "asterisk");
		}

		if ($ini->write() != 1)
		{
			echo "<script>javascript:alert('"._("Error writing the http.conf file.")."');</script>";
		}

		// We also need to make certain that it is enabled in manager.conf
		$ini = new Ini("/etc/asterisk/manager.conf");
		$ini->read();
		$general = $ini->getSection("general");

		$general->uncomment("enabled");
		$general->uncomment("webenabled");
		$general->setKeyValue("enabled", "yes");
		$general->setKeyValue("webenabled", "yes");

		if ($ini->write() != 1)
		{
			echo "<script>javascript:alert('"._("Error writing the manager.conf file.")."');</script>";
		}
		needreload();
	}

	function enableParking()
	{
		$parkingArray = parking_getconfig();

		error_log("ParkingEnabled ='" . $parkingArray['parkingenabled'] . "'");

		if (!isset($parkingArray['parkingenabled']) || $parkingArray['parkingenabled'] == '')
		{
			error_log('Enabling the parking lot');
			parking_add('s', '700', '8', '45', 'parkedcalls', '', '', '', 'njr-operator,njr-inbound,1');
		}
		else
		{
			// yes we already have a packing lot so no work to do.
			error_log('Parking lot already enabled');
		}
	}

}

?>