<?php

require ("Ini.php");


if(($managers = manager_list()) !== null)
{
	$managerFound = false;

	//Search for NJ Click manager
	foreach($managers as $manager)
	{
		if($manager['name'] == "NJClick" )
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
	
	$manager['permit'] = $permit;
	$manager['secret'] = $secret;
	// delete and re-add the manager connection.
	manager_del($manager['name']);
	manager_add($manager['name'],$manager['secret'],$manager['deny'],$manager['permit'],$manager['read'],$manager['write']);
	manager_gen_conf();
	needreload();
}


?>

<H1>Noojee Click</H1>
<H2>Overview</H2>
<p>Noojee Click™ for Asterisk is a free Firefox and Chromium extension which turns phone numbers on EVERY web page into 'Click to Dial' links.<br>
Noojee Click scans web pages for phone numbers as the page loads. Phone numbers are identified using a set of configurable Patterns. Each time a phone number is identified Noojee Click adds a Noojee Click 'Dial icon' straight after the phone number. You can dial a number just by clicking on the Dial Icon.
</p>
<H2>Download</H2>
You can download Noojee Click from our <a target="_blank" href="https://wiki.noojee.com.au/Noojee_Click">wiki</a>. It's Free!

<H2>Module Details</H2>
<p>Noojee's FreePBX module provides all of the core configuration required by Noojee Click to operate with your PBX.</p>
<p>
	1) Creates a Asterisk API account for Noojee Click to connect to.<br>
	</p>

<p>If Noojee Click is having trouble connecting to your Asterisk server then you should start
by checking each of the following settings. Each setting, excluding 'Manager permit', must match the configuration
details of the 'Asterisk' tab within the Noojee Click Configuration dialog.</p>

<?php 
// Determine the http port
$ini = new Ini("/etc/asterisk/http.conf");
$ini->read();
$general = $ini->getSection("general");
$httpport = $general->getKeyValue("bindport");

?>

<H2>Configuration</H2>
<form name="general" action="config.php" method="post">
	<input type="hidden" name="display" value="noojee_click_setup" />
	<input type="hidden" name="action" value="save" />
	<b>Asterisk:</b>
	
	<table>
		<tr>
			<td><a href=# class="info"><?php echo _("Server Type:")?>
					<span> <?php echo _("The connection type used by Noojee Click to connect to this asterisk server."); ?>
				</span> </a></td>
			<td>AJAM (Asterisk 1.4+)</td>
		</tr>
		<tr>
			<td><a href=# class="info"><?php echo _("Asterisk Hostname:")?>
					<span> <?php echo _("The IP address of your asterisk server."); ?>
				</span> </a></td>
			<td><?php echo $_SERVER['SERVER_ADDR'];?></td>
		</tr>
		<tr>
			<td><a href=# class="info"><?php echo _("Manager Port:")?>
					<span> <?php echo _("The port number used by Noojee Click to connect to the Asterisk HTTP mini-server."); ?>
				</span> </a></td>
			<td> <?php echo $httpport;?></td>
		</tr>
		<tr>
			<td><a href=# class="info"><?php echo _("Manager Username:")?>
					<span> <?php echo _("The username for the Asterisk API account used by Noojee Click to connect to Asterisk."); ?>
				</span> </a></td>
			<td><?php echo $manager["name"];?></td>
		</tr>
		<tr>
			<td><a href=# class="info"><?php echo _("Manager Secret:")?>
					<span> <?php echo _("The secret (password) for the Asterisk API account used by Noojee Click to connect to Asterisk."); ?>
				</span> </a></td>
			<td><input name="secret" value="<?php echo $manager["secret"];?>"></td>
		</tr>
		<tr>
			<td><a href=# class="info"><?php echo _("Context:")?>
					<span> <?php echo _("The context to use when dialing."); ?>
				</span> </a></td>
			<td>from-internal</td>
		</tr>

		<tr>
			<td><a href=# class="info"><?php echo _("Use HTTPS:")?>
					<span> <?php echo _("Determins if HTTPS is used to communicate between Noojee Click and Asterisk."); ?>
				</span> </a></td>
			<td>Leave the check box unselected.</td>
		</tr>
		
		<tr>
			<td><a href=# class="info"><?php echo _("Manager permit:")?> <span> <?php echo _("The default permit range includes all private IP address
			ranges, which means that Noojee Click should have no trouble connecting to your Asterisk PBX.<br>
			If you use public IP address ranges internally you may need to
			add the IP address ranges of all PC that will connect with Noojee Click.<br>
			 The entry should be of the form &lt;ip address&gt;/&lt;subnet mask&gt;<br> e.g.
				10.10.0.123/255.255.255.0<br> If you are unsure what the subnet mask
				should be then just use 255.255.255.0."); ?> </span> </a>
			</td>
			<td><input name="permit" value="<?php echo $manager["permit"];?>" size="40"></td>
		</tr>
		
		
	</table>
<!--	<?php print_r($manager); print_r($amp_conf);?> -->
	<h6>
		<input name="Submit" type="submit"
			value="<?php echo _("Submit Changes")?>">
	</h6>
</form>

