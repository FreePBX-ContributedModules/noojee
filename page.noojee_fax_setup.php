<?php

if(($managers = manager_list()) !== null)
{
	$managerFound = false;

	//Search for NJ Fax manager
	foreach($managers as $manager)
	{
		if($manager['name'] == "NJFax" )
		{
			$managerFound = true;
			// Now get the manager details:
			$manager = manager_get($manager['name']);
			break;
		}
	}
}

$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';

//if submitting manager settings.
if ($action == 'save')
{
	$permit = $_REQUEST['permit'];
	$secret = $_REQUEST['secret'];
	$faxReturnNumber = $_REQUEST['FaxReturnNumber'];
	$receiptSpool = $_REQUEST['receiptSpool'];

	$manager['permit'] = $permit;
	$manager['secret'] = $secret;
	// delete and re-add the manager connection.
	manager_del($manager['name']);
	manager_add($manager['name'],$manager['secret'],$manager['deny'],$manager['permit'],$manager['read'],$manager['write']);
	manager_gen_conf();

	// We only have one row so we don't need a where clause.
	$sql = "update tblNoojeeModule set (FaxReturnNumber = ?, ReceiptSpool = ?)";
	$data = array($faxReturnNumber, $receiptSpool);
	$sth = $db->prepare($sql);
	$res = $db->execute($sth, $data);
	// Always check that result is not an error
	if (PEAR::isError($res))
		freepbx_log("Noojee", "error", "Attempting update SQL table tblNoojeeModule" + $res);
	needreload();
}


?>
<p>The Noojee module provides all of the core configuration required by
	Noojee Fax to operate with your PBX.</p>
<p>
	1) Creates the dial plan required by Noojee Fax.<br> 2) Creates a
	'Noojee Fax' Destination making it simple to set up an Inbound Route
	which delivers calls to Noojee Fax.<br> 3) Creates a Asterisk Manager
	Interface account for Noojee Fax to connect to.<br>
</p>

<p>If Noojee Fax is having trouble connecting to your Asterisk server
	then you should start by checking that each of the following settings.
	Each setting, excluding 'Manager permit', must match the configuration
	details of the 'NoojeeFax.xml' file.</p>

	<?php
	$row = array();
	$sql = "select FaxReturnNumber, ReceiptSpool from tblNoojeeModule";
	$res =& $db->query($sql);
	// Always check that result is not an error
	if (PEAR::isError($res))
	{
		$errorString = sprintf('[%s: message="%s" code=%d info="%s"]', $res->getType(),
			$res->getMessage(), $res->getCode(), $res->getUserInfo());
		echo "<b>Error: Attempting to obtain data from SQL tblNoojeeModule Error=" . $errorString . "<br>";
		freepbx_log("Noojee", "error", "Attempting to obtain data from SQL tblNoojeeModule Error=" . $errorString);
	}
	else
		$res->fetchInto($row,DB_FETCHMODE_ASSOC);
	
	?>

<form
	name="general" action="config.php" method="post">
	<input type="hidden" name="display" value="noojee_fax_setup" /> <input
		type="hidden" name="action" value="save" />
	<table>
		<tr>
			<td><a href=# class="info"><?php echo _("Asterisk Server IP address or hostname:")?>
					<span> <?php echo _("The IP address of your asterisk server."); ?>
				</span> </a></td>
			<td><?php echo $_SERVER['SERVER_ADDR'];?></td>
		</tr>
		<tr>
			<td><a href=# class="info"><?php echo _("Asterisk server manager port number:")?>
					<span> <?php echo _("The port number used by Noojee Fax to connect to the Asterisk Manager Interface account."); ?>
				</span> </a></td>
			<td> <?php echo $amp_conf['ASTMANAGERPORT']?></td>
		</tr>
		<tr>
			<td><a href=# class="info"><?php echo _("Manager username:")?> <span> <?php echo _("The username for the Asterisk Manager Interface account used by Noojee Fax to connect to Asterisk."); ?>
				</span> </a></td>
			<td><?php echo $manager["name"];?></td>
		</tr>
		<tr>
			<td><a href=# class="info"><?php echo _("Manager password:")?> <span> <?php echo _("The password (secret) for the Asterisk Manager Interface account used by Noojee Fax to connect to Asterisk."); ?>
				</span> </a></td>
			<td><input name="secret" value="<?php echo $manager["secret"];?>"></td>
		</tr>
		<tr>
			<td><a href=# class="info"><?php echo _("Manager permit:")?> <span> <?php echo _("The default permit only allows local connections as 
			Noojee Fax should be installed on the same server as the PBX.<br>
			If Noojee Fax is not installed on the local server then you will need to adjust the permits."); ?>
				</span> </a>
			</td>
			<td><input name="permit" value="<?php echo $manager["permit"];?>"
				size="40"></td>
		</tr>
		<tr>
			<td><a href=# class="info"><?php echo _("Fax Return Number:")?> <span> <?php echo _("The phone number to be presented to the remote fax as our caller id."); ?>
				</span> </a></td>
			<td><input name="FaxReturnNumber"
				value="<?php echo $row["FaxReturnNumber"];?>"></td>
		</tr>
		<tr>
			<td><a href=# class="info"><?php echo _("Receipt Spool Folder:")?> <span> <?php echo _("The path to the folder that holds inbound faxes.<br>If you used the default paths when installing NoojeeFax the displayed value should be correct."); ?>
				</span> </a></td>
			<td><input name="ReceiptSpool"
				value="<?php echo $row["ReceiptSpool"];?>"></td>
		</tr>


	</table>
	<!--	<?php print_r($manager); print_r($amp_conf);?> -->
	<h6>
		<input name="Submit" type="submit"
			value="<?php echo _("Submit Changes")?>">
	</h6>
</form>

