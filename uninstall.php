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

require_once ("NJManager.php");
require_once ("NJLogging.php");

noojee_uninstall();

function noojee_uninstall()
{
	global $db;

	$sql = _("drop table tblNoojeeModule");
	$sth = $db->prepare($sql);
	$res = $db->execute($sth);
	// Always check that result is not an error
	if (PEAR::isError($res))
	{
		error_log ( "Noojee: error:Attempting to drop SQL table tblNoojeeModule" . $res);
		freepbx_log("Noojee", "error", "Attempting to drop SQL table tblNoojeeModule" + $res);
	}

	$logger = new NJLogging();
	
	$noojeeManager = new NJManager();
	
	$logger->info_log("Deleting Manager Account NJReceptionist");
	$noojeeManager->deleteAccount("NJReceptionist"); //older versions created this.
	$logger->info_log("Deleting Manager Account NJReception");
	$noojeeManager->deleteAccount("NJReception");
	$logger->info_log("Deleting Manager Account NJFax");
	$noojeeManager->deleteAccount("NJFax");
	$logger->info_log("Deleting Manager Account NJClick");
	$noojeeManager->deleteAccount("NJClick");
	
	needreload();
	
}
?>