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

function noojee_destinations()
{
	return array(
	array(
		'destination' => 'njr-operator,njr-inbound,1', 
		'description' => 'Send call to Noojee Receptionist 4.x',
	),
	array(
		'destination' => 'reception,njr-inbound,1', 
		'description' => 'Send call to Noojee Receptionist 3.x',
	),

	/**
		I've disabled this for now as its not ready for prime time.
	We need to configure t.38 and change the NJ Fax installer to work with this module.

	array(
	'destination' => 'Noojee-Fax-Receive,s,1',
	'description' => 'Send call to Noojee Fax',
	),
	*/
	);
}


function noojee_get_config($engine)
{
	switch($engine)
	{
		case 'asterisk':

			generate_receptionist_40_dialplan();
			generate_receptionist_30_dialplan();
			 //  TODO: we removed this as it is dangerous. See comments below.
			//  generate_fax_40_dialplan();
			break;
	}
}

function generate_receptionist_40_dialplan()
{
	global $ext;
	global $db;

	//  Noojee Receptionist 4.0
	$id = 'njr-operator';  //  The context to be used
	$ext->addInclude('from-internal-additional', $id);

	$ext->add($id, 'njr-inbound', '', new ext_answer(''));
	$ext->add($id, 'njr-inbound', '', new ext_ringing(''));
	$ext->add($id, 'njr-inbound', 'start', new ext_wait(3));
	$ext->add($id, 'njr-inbound', '', new extension('Verbose("njr-running=${njr-running} channel=${CHANNEL} channel=${UNIQUEID}")'));
	$ext->add($id, 'njr-inbound', '', new ext_gotoIf('$["${njr-running}" = ""]', 'return'));
	$ext->add($id, 'njr-inbound', '', new ext_wait(5));
	$ext->add($id, 'njr-inbound', '', new ext_goto('start'));
	$ext->add($id, 'njr-inbound', 'return' ,new ext_setvar('NJRSTATUS','FAILURE'));
	$ext->add($id, 'njr-inbound', '', new ext_return(''));

	$ext->add($id, 'njr-transfer', '', new ext_answer(''));
	$ext->add($id, 'njr-transfer', 'start', new ext_dial('${dest}' ,'60,r'));
	$ext->add($id, 'njr-transfer', '', new ext_wait(10));
	$ext->add($id, 'njr-transfer', '', new ext_goto('start'));

	$ext->add($id, 'njr-music', '', new ext_answer(''));
	$ext->add($id, 'njr-music', 'start', new ext_musiconhold(''));
	$ext->add($id, 'njr-music', '', new ext_wait(30));
	$ext->add($id, 'njr-music', '', new ext_goto('start'));

	$ext->add($id, 'njr-meetme', '', new ext_answer(''));
	$ext->add($id, 'njr-meetme', '', new ext_meetme('${room}','${options}'));
	$ext->add($id, 'njr-meetme', '', new ext_hangup(''));

	$ext->add($id, 'njr-hold', '', new ext_answer(''));
	$ext->add($id, 'njr-hold', 'start', new ext_ringing(''));
	$ext->add($id, 'njr-hold', '', new ext_wait(30));
	$ext->add($id, 'njr-hold', '', new ext_goto('start'));

	$ext->add($id, 'njr-acknowledged', '', new ext_answer(''));
	$ext->add($id, 'njr-acknowledged', 'start', new ext_musiconhold(''));
	$ext->add($id, 'njr-acknowledged', '', new ext_wait(30));
	$ext->add($id, 'njr-acknowledged', '', new ext_goto('start'));

	$ext->add($id, 'njr-voicemail', '', new ext_answer(''));
	$ext->add($id, 'njr-voicemail', '', new ext_vm('${mailbox}'));
	$ext->add($id, 'njr-voicemail', '', new ext_hangup(''));

	$ext->add($id, 'njr-park', '', new ext_answer(''));
	$ext->add($id, 'njr-park', '', new extension('ParkAndAnnounce(pbx-transfer:PARKED|60|Console/dsp|'
	. $id . ',njr-inbound,1'));
	$ext->add($id, 'njr-park', '', new ext_hangup(''));

	$ext->add($id, 'njr-connect', '', new ext_dial('${dest}','6,r'));
	$ext->add($id, 'njr-connect', '', new ext_userevent('NJR','action: connectFailed,channel: ${CHANNEL},uniqueid: ${UNIQUEID}'));
	$ext->add($id, 'njr-connect', '', new ext_wait(1));
	$ext->add($id, 'njr-connect', '', new ext_goto(1, 'njr-connect'));


	$ext->add($id, 'njr-bridge', '', new ext_wait(3000));
	$ext->add($id, 'njr-bridge', '', new ext_hangup(''));
}

function generate_receptionist_30_dialplan()
{
	global $ext;
	global $db;

	//  Noojee Receptionist 3.0
	$id = 'reception';  //  The context to be used
	$ext->addInclude('from-internal-additional', $id);

	$ext->add($id, 'njr-inbound', '', new ext_answer(''));
	$ext->add($id, 'njr-inbound', '', new ext_ringing(''));
	$ext->add($id, 'njr-inbound', 'start', new ext_wait(3));
	$ext->add($id, 'njr-inbound', '', new extension('Verbose("njr-running=${njr-running} channel=${CHANNEL} channel=${UNIQUEID}")'));
	$ext->add($id, 'njr-inbound', '', new ext_gotoIf('$["${njr-running}" = ""]', 'return'));
	$ext->add($id, 'njr-inbound', '', new ext_wait(5));
	$ext->add($id, 'njr-inbound', '', new ext_goto('start'));
	$ext->add($id, 'njr-inbound', 'return' ,new ext_setvar('NJRSTATUS','FAILURE'));
	$ext->add($id, 'njr-inbound', '', new ext_return(''));

	$ext->add($id, 'njr-transfer', '', new ext_answer(''));
	$ext->add($id, 'njr-transfer', 'start', new ext_dial('${dest}' ,'60,r'));
	$ext->add($id, 'njr-transfer', '', new ext_wait(10));
	$ext->add($id, 'njr-transfer', '', new ext_goto('start'));

	$ext->add($id, 'njr-music', '', new ext_answer(''));
	$ext->add($id, 'njr-music', 'start', new ext_musiconhold(''));
	$ext->add($id, 'njr-music', '', new ext_wait(30));
	$ext->add($id, 'njr-music', '', new ext_goto('start'));

	$ext->add($id, 'njr-meetme', '', new ext_answer(''));
	$ext->add($id, 'njr-meetme', '', new ext_meetme('${room}','${options}'));
	$ext->add($id, 'njr-meetme', '', new ext_hangup(''));

	$ext->add($id, 'njr-hold', '', new ext_answer(''));
	$ext->add($id, 'njr-hold', 'start', new ext_ringing(''));
	$ext->add($id, 'njr-hold', '', new ext_wait(30));
	$ext->add($id, 'njr-hold', '', new ext_goto('start'));

	$ext->add($id, 'njr-voicemail', '', new ext_answer(''));
	$ext->add($id, 'njr-voicemail', '', new ext_vm('${mailbox}'));
	$ext->add($id, 'njr-voicemail', '', new ext_hangup(''));

	$ext->add($id, 'njr-park', '', new ext_answer(''));
	$ext->add($id, 'njr-park', '', new extension('ParkAndAnnounce(pbx-transfer:PARKED|60|Console/dsp|'
	. $id . ',njr-inbound,1'));
	$ext->add($id, 'njr-park', '', new ext_hangup(''));

	$ext->add($id, '1', '', new ext_read('dest'));
	$ext->add($id, '1', '', new ext_userevent('meetmetransfer','data: ${njr-managedChannel}&${dest}'));
	$ext->add($id, '1', '', new ext_wait(2));
	$ext->add($id, '1', '', new ext_hangup(''));

	$ext->add($id, 't', '', new extension('Verbose("timeout on manual transfer")'));
	$ext->add($id, 't', '', new extension('Playback(beep)'));
	$ext->add($id, 't', '', new ext_goto(1,1));
}

function generate_fax_40_dialplan()
{
	global $ext;
	global $db;

	/**
	 *  Now the Noojee Fax dialplan
	 */
	$id = 'Noojee-Fax-Receive';  //  The context to be used

	$ext->addInclude('from-internal-additional', $id);

	$sql = "select FaxReturnNumber, ReceiptSpool from tblNoojeeModule";
	$res =& $db->query($sql);
	//  Always check that result is not an error
	if (PEAR::isError($res))
	freepbx_log("Noojee", "error", "Attempting to obtain data from SQL tblNoojeeModule" . $res);

	$res->fetchInto($row);

	$ext->add($id, 's', '', new ext_wait(1));
	$ext->add($id, 's', '', new ext_answer(''));
	$ext->add($id, 's', '', new ext_wait(1));
	$ext->add($id, 's', '' ,new ext_setvar('localstationid',$row['FaxReturnNumber']));
	$ext->add($id, 's', '' ,new ext_setvar('FAXFILE',$row['ReceiptSpool'] . '/${UNIQUEID}.tif'));
	$ext->add($id, 's', '', new ext_wait(1));
	$ext->add($id, 's', '' ,new ext_setvar('DID','${DID}${FROM_DID}'));
	$ext->add($id, 's', '', new extension('Verbose("DID=${DID}")'));
	$ext->add($id, 's', '', new ext_goto('${DID}',1));
	$ext->add($id, 's', '', new extension('Verbose("Oops shouldn\'t get here")'));

	 //  TODO: the following code is dangerous as it results in all inbound calls
	 //  being routed to Noojee Fax. The problem is that this context gets included in the 
	 //  main from-internal (I think that's the name) and since _X. matches any number
	 // all inbound calls are routed to Noojee Fax. We need to find a way of avoiding this
	 // before we redploy this module.
	$ext->add($id, '_X.', '', new extension('RxFax(${FAXFILE})'));
	$ext->add($id, '_X.', '', new extension('Verbose(REMOTESTATIONID ${remotestationid})'));
	$ext->add($id, '_X.', '', new extension('Verbose(FAXPAGES ${faxpages}'));
	$ext->add($id, '_X.', '', new extension('Verbose(FAXBITRATE ${faxbitrate}'));
	$ext->add($id, '_X.', '', new extension('Verbose(FAXRESOLUTION ${faxresolution}'));
	$ext->add($id, '_X.', '', new ext_hangup(''));

	$ext->add($id, 'h', '', new ext_gotoIf('$["${phasestatus}" = "SUCCESS"]', 'complete'));
	$ext->add($id, 'h', '', new ext_userevent('FaxPartial','exten: ${DID},callerId: \'${callerID(num)}\',remoteStationId: \'${remotestationid}\',localStationId: \'${localstationid}\',pagesTransferred: \'${faxpages}\',resolution: \'${faxresolution}\',transferRate: \'${faxrate}\',filename: ${FAXFILE}'));
	$ext->add($id, 'h', '', new ext_hangup(''));
	$ext->add($id, 'h', 'complete', new ext_userevent('FaxComplete','exten: ${DID},callerId: \'${callerID(num)}\',remoteStationId: \'${remotestationid}\',localStationId: \'${localstationid}\',pagesTransferred: \'${faxpages}\',resolution: \'${faxresolution}\',transferRate: \'${faxrate}\',filename: ${FAXFILE}'));
	$ext->add($id, 'h', '', new ext_hangup(''));
}
?>