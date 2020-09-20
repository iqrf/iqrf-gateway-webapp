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

/**
 * DPA packet
 */
class Packet {

	/**
	 * NADR
	 */
	public nadr: number;

	/**
	 * PNUM
	 */
	public pnum: number;

	/**
	 * PCMD
	 */
	public pcmd: number;

	/**
	 * HWPID
	 */
	public hwpid: number;

	/**
	 * PDATA
	 */
	public pdata: number[];

	/**
	 * Constructor
	 * @param nadr NADR
	 * @param pnum PNUM
	 * @param pcmd PCMD
	 * @param hwpid HWPID
	 * @param pdata PDATA
	 */
	constructor(nadr: number, pnum: number, pcmd: number, hwpid: number, pdata: number[]) {
		this.nadr = nadr;
		this.pnum = pnum;
		this.pcmd = pcmd;
		this.hwpid = hwpid;
		this.pdata = pdata;
	}

	/**
	 * Detect timeout from parsed DPA packet
	 * @returns DPA timeout
	 */
	detectTimeout(): number | null {
		let timeout = null;
		if (this.pnum === 0 && this.pcmd === 4) {
			timeout = 12000;
		} else if (this.pnum === 0 && this.pcmd === 7) {
			timeout = 0;
		} else if (this.pnum === 13 && this.pcmd === 0) {
			timeout = 6000;
		}
		return timeout;
	}

	/**
	 * Parses DPA packet
	 * @param {string} packet DPA packet to parse
	 * @returns {Packet} Parsed DPA packet
	 */
	static parse(packet: string) {
		let packetArray = packet.split('.');
		let nadrLo = packetArray.shift()!;
		let nadrHi = packetArray.shift()!;
		let nadr = parseInt(nadrHi + nadrLo, 16);
		let pnum = parseInt(packetArray.shift()!, 16);
		let pcmd = parseInt(packetArray.shift()!, 16);
		let hwpidLo = packetArray.shift()!;
		let hwpidHi = packetArray.shift()!;
		let hwpid = parseInt(hwpidHi + hwpidLo, 16);
		let pdata = packetArray.map(hex => parseInt(hex, 16));
		return new Packet(nadr, pnum, pcmd, hwpid, pdata);
	}

	/**
	 * Returns DPA packet string
	 * @returns {string} DPA packet as string
	 */
	toString() {
		return [
			this.nadr & 255, this.nadr >> 8, this.pnum, this.pcmd,
			this.hwpid & 255, this.hwpid >> 8, ...this.pdata
		].map(int => int.toString(16).padStart(2, '0')).join('.');
	}
}

/**
 * Validate DPA packet
 * @param packet DPA packet to validate
 * @returns Is valid DPA packet?
 */
function validatePacket(packet: string): boolean {
	let re = new RegExp('^([0-9a-fA-F]{1,2}\\.){4,62}[0-9a-fA-F]{1,2}(\\.|)$', 'i');
	return packet.match(re) !== null;
}

/**
 * Updates NADR in DPA packet
 * @param request DPA request to modify
 * @param address New NADR
 * @returns Modified DPA request
 */
function updateNadr(request: string, address: number) {
	let packet = Packet.parse(request);
	packet.nadr = address;
	return packet.toString();
}

export default {Packet, updateNadr, validatePacket};
