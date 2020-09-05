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
	let re = new RegExp('^([0-9a-fA-F]{1,2}\\.){4,62}[0-9a-fA-F]{1,2}(\\.|)$', 'i');
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

export default {detectTimeout, parsePacket, validatePacket};
