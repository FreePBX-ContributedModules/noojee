<?php
require_once("NoojeeDatabase.php");
require_once("NJLogging.php");

$noojeeSetup = new NoojeeReceptionistSetup();
$noojeeSetup->processRequest();

// array of manager properties.
$noojeeManager = $noojeeSetup->getManager();

class NoojeeReceptionistSetup
{
	private $manager;

	function processRequest()
	{
		$this->manager = $this->getNJRManager();
		
		NJLogging::debug_log("Receptionist default version=".$this->getNJRMajorVersion());

		$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';

		//if submitting manager settings.
		if ($action == 'save')
		{
			$permit = $_REQUEST['permit'];
			$secret = $_REQUEST['secret'];
			$njrMajorVersion = $_REQUEST['version'];
				

			$this->manager['permit'] = $permit;
			$this->manager['secret'] = $secret;
			// delete and re-add the manager connection.
			manager_del($this->manager['name']);
			manager_add($this->manager['name'],$this->manager['secret'],$this->manager['deny'],$this->manager['permit'],$this->manager['read'],$this->manager['write']);
			manager_gen_conf();
			
			$noojeeDb = new NoojeeDatabase();
			$noojeeDb->updateNJRMajorVersion($njrMajorVersion);
			needreload();
		}
	}

	function getNJRManager()
	{
		$manager = null;

		if(($managers = manager_list()) !== null)
		{
			$managerFound = false;

			//Search for NJ Receptionist manager
			foreach($managers as $manager)
			{
				if($manager['name'] == "NJReception" )
				{
					$managerFound = true;
					// Now get the manager details:
					$manager = manager_get($manager['name']);
					break;
				}
			}
		}
		return $manager;
	}

	/**
	 * Retrieves the users selected version of Noojee Receptionist
	 * Enter description here ...
	 */
	function getNJRMajorVersion()
	{
		$noojeeDb = new NoojeeDatabase();
		return $noojeeDb->getNJRMajorVersion();
	}

	function getManager()
	{
		return $this->manager;
	}
}

?>
<script type="text/javascript">
<!--
function switchVersion()
{
	var versionDiv4 = document.getElementById("version4");
	var versionDiv3 = document.getElementById("version3");
	var selectVersion3 = document.getElementById("selectVersion3");
	var selectVersion4 = document.getElementById("selectVersion4");
	
	if (versionDiv4.style.display == "none")
	{	
		versionDiv4.style.display = "block";
		versionDiv3.style.display = "none";
		selectVersion4.selectedIndex = 1;
	}
	else
	{	
		versionDiv3.style.display = "block";
		versionDiv4.style.display = "none";
		selectVersion3.selectedIndex = 0;
	}
}
-->
</script>
<H1>Noojee Receptionist</H1>
<H2>Overview</H2>
<p>Noojee Receptionist™ for Asterisk is designed to improve your
	Receptionist's performance. Often referred to as an Operator Console or
	Switchboard, Noojee Receptionist is an affordable replacement for the
	traditional button bank or hardware based switchboard. Noojee
	Receptionist delivers a raft of features and productivity improvements
	simply not possible with a traditional switchboard. Unlike many
	software based operator consoles Noojee Receptionist is optimised for
	speed. For instance an operator can answer and transfer a call with
	five keystrokes all from the numeric keypad.</p>
<H2>Download</H2>
You can download a trial version of Noojee Receptionist from our
<a target="_blank" href="https://wiki.noojee.com.au/Noojee_Receptionist">wiki</a>
.
<br>
You can purchase Noojee Receptionist from our online
<a target="_blank" href="http://www.noojee.com.au/store/software/noojee-receptionist">store</a>
.

<H2>Module Details</H2>
<p>Noojee's FreePBX module provides all of the core configuration
	required by Noojee Receptionist to operate with your PBX.</p>
<p>
	1) Creates the dial plan required by Noojee Receptionist.<br> 2)
	Creates a 'Noojee Receptionist' Destination making it simple to set up
	an Inbound Route which delivers calls to Noojee Receptionist.<br> 3)
	Creates a Asterisk API account for Noojee Receptionist to
	connect to.<br>
</p>

<p>If Noojee Receptionist is having trouble connecting to your Asterisk
	server then you should start by checking that each of the following
	settings. Each setting, excluding 'Manager permit', must match the
	configuration details of the 'Asterisk' tab within the Noojee
	Receptionist Configuration dialog.</p>

<H2>Configuration</H2>

<div id="version4" style="display: <?php echo (($noojeeSetup->getNJRMajorVersion() == 4) ? 'block' : 'none') ?>">
	<form name="general" action="config.php" method="post">
		<select id="selectVersion4" name="version" onchange="javascript:switchVersion();">
			<option <?php echo (($noojeeSetup->getNJRMajorVersion() == 3) ? 'selected' : '')?> value="3">Noojee
				Receptionist 3.x</option>
			<option <?php  echo (($noojeeSetup->getNJRMajorVersion() == 4) ? 'selected' : '')?> value="4">Noojee
				Receptionist 4.x</option>
		</select> <input type="hidden" name="display"
			value="noojee_receptionist_setup" /> <input type="hidden"
			name="action" value="save" />
		<table>
			<tr>
				<td><a href=# class="info">
				<?php echo _("Asterisk Hostname:")?> <span>
				<?php echo _("The hostname or IP address of your Asterisk Server."); ?> </span>
				</a></td>
				<td>
				<?php echo $_SERVER['SERVER_ADDR'];?>
				</td>
			</tr>
			<tr>
				<td><a href=# class="info">
				<?php echo _("Manager Port:")?> <span>
				<?php echo _("The port number used by Noojee Receptionist to connect to the Asterisk API account."); ?>
					</span> </a></td>
				<td>
				<?php echo $amp_conf['ASTMANAGERPORT']?>
				</td>
			</tr>
			<tr>
				<td><a href=# class="info">
				<?php echo _("Manager Username:")?> <span> <?php echo _("The username for the Asterisk API account used by Noojee Receptionist to connect to Asterisk."); ?>
					</span> </a></td>
				<td>
				<?php echo $noojeeManager["name"];?>
				</td>
			</tr>
			<tr>
				<td><a href=# class="info">
				<?php echo _("Manager Secret:")?> <span> <?php echo _("The secret (password) for the Asterisk API account used by Noojee Receptionist to connect to Asterisk."); ?>
					</span> </a></td>
				<td><input name="secret"
					value='<?php echo $noojeeManager["secret"];?>'></td>
			</tr>
			<tr>
				<td colspan="2"></td>
			</tr>
			<tr>
				<td>Management context:</td>
				<td>njr-operator</td>
			</tr>
			<tr>
				<td>Dial context:</td>
				<td>from-internal</td>
			</tr>
			<tr>
				<td>Acknowledged Extension:</td>
				<td>njr-acknowledeged</td>
			</tr>
			<tr>
				<td>Bridge Extension:</td>
				<td>njr-bridge</td>
			</tr>
			<tr>
				<td>Connect Extension:</td>
				<td>njr-connect</td>
			</tr>
			<tr>
				<td>Inbound Extension:</td>
				<td>njr-inbound</td>
			</tr>
			<tr>
				<td>Hold Extension:</td>
				<td>njr-hold</td>
			</tr>
			<tr>
				<td>Transfer Extension:</td>
				<td>njr-transfer</td>
			</tr>
			<tr>
				<td>Meetme Extension:</td>
				<td>njr-meetme</td>
			</tr>
			<tr>
				<td>Music Extension:</td>
				<td>njr-music</td>
			</tr>
			<tr>
				<td>Voicemail Extension:</td>
				<td>njr-voicemail</td>
			</tr>
			<tr>
				<td>Park Extension:</td>
				<td>njr-park</td>
			</tr>

			<tr>
				<td><a href=# class="info">
				<?php echo _("Manager Permit:")?> <span> <?php echo _("The default permit range includes all private IP address
			ranges, which means that Noojee Receptionist should have no trouble connecting to your Asterisk PBX.<br>
			If you use public IP address ranges internally you may need to
			add the receptionist PC's IP address to the permit
				list. The Noojee Receptionist installer will display the required
				details during the install process. If you have multiple
				Receptionist PC's then you can add multiple entries by separating
				them with an amplisand '&amp;'.<br> The entry should be of the form
				&lt;ip address&gt;/&lt;subnet mask&gt;<br> e.g.
				10.10.0.123/255.255.255.0<br> If you are unsure what the subnet mask
				should be then just use 255.255.255.0."); ?> </span> </a></td>
				<td><input name="permit"
					value='<?php echo $noojeeManager["permit"];?>'
					size="40"></td>
			</tr>


		</table>
		<h6>
			<input name="Submit" type="submit"
				value='<?php echo _(" SubmitChanges")?>'>
		</h6>
	</form>

</div>
<div id="version3" style="display: <?php echo (($noojeeSetup->getNJRMajorVersion() == 3) ? 'block' : 'none') ?>">
	<form name="general" action="config.php" method="post">
		<input type="hidden" name="display" value="noojee_receptionist_setup" />
		<input type="hidden" name="action" value="save" /> 
		<select
			id="selectVersion3" name="version" onchange="javascript:switchVersion();">
			<option <?php echo (($noojeeSetup->getNJRMajorVersion() == 3) ? 'selected' : '')?> value="3">Noojee
				Receptionist 3.x</option>
			<option <?php  echo (($noojeeSetup->getNJRMajorVersion() == 4) ? 'selected' : '')?> value="4">Noojee
				Receptionist 4.x</option>
		</select>

		<table>
			<tr>
				<td><a href=# class="info">
				<?php echo _("Asterisk Server IP address or hostname:")?> <span>
				<?php echo _("The IP address of your asterisk server."); ?> </span>
				</a></td>
				<td>
				<?php echo $_SERVER['SERVER_ADDR'];?>
				</td>
			</tr>
			<tr>
				<td><a href=# class="info">
				<?php echo _("Asterisk server manager port number:")?> <span>
				<?php echo _("The port number used by Noojee Receptionist to connect to the Asterisk Manager Interface account."); ?>
					</span> </a></td>
				<td>
				<?php echo $amp_conf['ASTMANAGERPORT']?>
				</td>
			</tr>
			<tr>
				<td><a href=# class="info">
				<?php echo _("Manager username:")?> <span> <?php echo _("The username for the Asterisk Manager Interface account used by Noojee Receptionist to connect to Asterisk."); ?>
					</span> </a></td>
				<td>
				<?php echo $noojeeManager["name"];?>
				</td>
			</tr>
			<tr>
				<td><a href=# class="info">
				<?php echo _("Manager password:")?> <span> <?php echo _("The password (secret) for the Asterisk Manager Interface account used by Noojee Receptionist to connect to Asterisk."); ?>
					</span> </a></td>
				<td><input name="secret"
					value='<?php echo $noojeeManager["secret"];?>'></td>
			</tr>
			<tr>
				<td colspan="2"></td>
			</tr>
			<tr>
				<td>Reception context:</td>
				<td>reception</td>
			</tr>
			<tr>
				<td>Dial context:</td>
				<td>from-internal</td>
			</tr>
			<tr>
				<td>Dial in extension:</td>
				<td>njr-inbound</td>
			</tr>
			<tr>
				<td>Hold Extension:</td>
				<td>njr-hold</td>
			</tr>
			<tr>
				<td>Transfer extension:</td>
				<td>njr-transfer</td>
			</tr>
			<tr>
				<td>Meetme extension:</td>
				<td>njr-meetme</td>
			</tr>
			<tr>
				<td>Music on hold extension:</td>
				<td>njr-music</td>
			</tr>
			<tr>
				<td>Voicemail extension:</td>
				<td>njr-voicemail</td>
			</tr>
			<tr>
				<td>Park extension:</td>
				<td>njr-park</td>
			</tr>

			<tr>
				<td><a href=# class="info">
				<?php echo _("Manager permit:")?> <span> <?php echo _("The default permit range includes all private IP address
			ranges, which means that Noojee Receptionist should have no trouble connecting to your Asterisk PBX.<br>
			If you use public IP address ranges internally you may need to
			add the receptionist PC's IP address to the permit
				list. The Noojee Receptionist installer will display the required
				details during the install process. If you have multiple
				Receptionist PC's then you can add multiple entries by separating
				them with an amplisand '&amp;'.<br> The entry should be of the form
				&lt;ip address&gt;/&lt;subnet mask&gt;<br> e.g.
				10.10.0.123/255.255.255.0<br> If you are unsure what the subnet mask
				should be then just use 255.255.255.0."); ?> </span> </a></td>
				<td><input name="permit"
					value='<?php echo $noojeeManager["permit"];?>'
					size="40"></td>
			</tr>


		</table>
		<h6>
			<input name="Submit" type="submit"
				value='<?php echo _(" SubmitChanges")?>'>
		</h6>
	</form>

</div>

