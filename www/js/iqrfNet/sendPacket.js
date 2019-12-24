/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

'use strict';

/**
 * Parse DPA packet
 * @param {string} packet DPA packet to parse
 * @returns {{nadrLo: string, nadrHi: string, pnum: string, pcmd: string, hwpidLo: string, hwpidHi: string, pdata: string[]}} Parsed DPA packet
 */
function parsePacket(packet) {
	let packetArray = packet.split('.');
	return {
		nadrLo: packetArray.shift(),
		nadrHi: packetArray.shift(),
		pnum: packetArray.shift(),
		pcmd: packetArray.shift(),
		hwpidLo: packetArray.shift(),
		hwpidHi: packetArray.shift(),
		pdata: (packetArray.join('.')).split('.')
	};
}

/**
 * Validate DPA packet
 * @param {string} packet DPA packet to validate
 * @returns {boolean} Is valid DPA packet?
 */
function validatePacket(packet) {
	let re = new RegExp('^([0-9a-fA-F]{1,2}.){4,62}[0-9a-fA-F]{1,2}(.|)$', 'i');
	return packet.match(re) !== null;
}

/**
 * Detect timeout from parsed DPA packet
 * @param {{nadrLo: string, nadrHi: string, pnum: string, pcmd: string, hwpidLo: string, hwpidHi: string, pdata: string[]}} packet Parsed DPA packet
 * @returns {Number}
 */
function detectTimeout(packet) {
	let timeout = null;
	if (packet.pnum === '00' && packet.pcmd === '04') {
		timeout = 12000;
	} else if (packet.pnum === '00' && packet.pcmd === '07') {
		timeout = 0;
	} else if (packet.pnum === '0D' && packet.pcmd === '00') {
		timeout = 6000;
	}
	return timeout;
}

/**
 * Set DPA timeout
 * @param {{nadrLo: string, nadrHi: string, pnum: string, pcmd: string, hwpidLo: string, hwpidHi: string, pdata: string[]}} packet Parsed DPA packet
 */
function setTimeout(packet) {
	let timeoutValue = detectTimeout(packet);
	let enabledTimeout = document.getElementById('frm-sendRawForm-timeoutEnabled');
	let timeout = document.getElementById('frm-sendRawForm-timeout');
	if (timeoutValue === null) {
		enabledTimeout.checked = false;
		timeout.disabled = true;
		timeout.value = null;
	} else {
		enabledTimeout.checked = true;
		timeout.disabled = false;
		timeout.value = timeoutValue;
	}
}

/**
 * Set DPA packet
 * @param {string} packet DPA packet to set
 */
function setPacket(packet) {
	if (validatePacket(packet)) {
		setTimeout(parsePacket(packet));
		document.getElementById('frm-sendRawForm-packet').value = packet;
	} else {
		document.getElementById('frm-sendRawForm-timeout').disabled = true;
	}
}

// Select DPA packet form list of macros from IQRF IDE
let packets = document.getElementsByClassName('btn-packet');
for (let i = 0; i < packets.length; i++) {
	packets[i].addEventListener('click', function (event) {
		setPacket(event.currentTarget.dataset.packet);
	});
}

// Disable custom NADR input
let nadr = document.getElementById('frm-sendRawForm-address');
if (nadr !== null) {
	nadr.disabled = true;
}

// Validate and fix DPA packet and set DPA timeout
let packet = document.getElementById('frm-sendRawForm-packet');
if (packet !== null) {
	packet.addEventListener('keypress', function (event) {
		setPacket(event.currentTarget.value);
	});
}

// Enable or disable DPA timeout
let timeoutEnabled = document.getElementById('frm-sendRawForm-timeoutEnabled');
if (timeoutEnabled !== null) {
	timeoutEnabled.addEventListener('click', function (event) {
		document.getElementById('frm-sendRawForm-timeout').disabled = !event.currentTarget.checked;
	});
}

// Enable or disable overwrite NADR
let overwriteNadr = document.getElementById('frm-sendRawForm-overwriteAddress');
if (overwriteNadr !== null) {
	overwriteNadr.addEventListener('click', function (event) {
		document.getElementById('frm-sendRawForm-address').disabled = !event.currentTarget.checked;
	});
}

// Disable DPA timeout by default
let timeout = document.getElementById('frm-sendRawForm-timeout');
if (timeout !== null) {
	timeout.disabled = true;
}
