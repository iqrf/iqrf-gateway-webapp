/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
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
		let timeout: number|null = null;
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
	static parse(packet: string): Packet {
		const packetArray = packet.split('.');
		if (packetArray.length < 6) {
			throw new Error('Invalid DPA packet length');
		}
		const nadrLo = packetArray.shift()!;
		const nadrHi = packetArray.shift()!;
		const nadr = parseInt(nadrHi + nadrLo, 16);
		const pnum = parseInt(packetArray.shift()!, 16);
		const pcmd = parseInt(packetArray.shift()!, 16);
		const hwpidLo = packetArray.shift()!;
		const hwpidHi = packetArray.shift()!;
		const hwpid = parseInt(hwpidHi + hwpidLo, 16);
		let pdata: Array<number> = [];
		if (packetArray.length > 0 && packetArray[0] !== '') {
			pdata = packetArray.map(hex => parseInt(hex, 16));
		}
		return new Packet(nadr, pnum, pcmd, hwpid, pdata);
	}

	/**
	 * Returns DPA packet string
	 * @param {boolean} withPdata With PDATA
	 * @returns {string} DPA packet as string
	 */
	toString(withPdata = true): string {
		let packet = [
			this.nadr & 255, this.nadr >> 8, this.pnum, this.pcmd,
			this.hwpid & 255, this.hwpid >> 8,
		];
		if (withPdata) {
			packet = packet.concat(this.pdata);
		}
		return packet.map(int => int.toString(16).padStart(2, '0')).join('.');
	}

	/**
	 * Updates NADR in DPA packet
	 * @param request DPA request to modify
	 * @param address New NADR
	 * @returns Modified DPA request
	 */
	static updateNadr(request: string, address: number): string {
		const packet = Packet.parse(request);
		packet.nadr = address;
		return packet.toString();
	}

	/**
	 * Validates DPA packet
	 * @param packet DPA packet to validate
	 * @returns Is valid DPA packet?
	 */
	static validatePacket(packet: string): boolean {
		const re = new RegExp('^[0-9a-f]{2}\\.00\\.[0-9a-f]{2}\\.[0-7][0-9a-f]\\.([0-9a-f]{2}\\.){1,58}[0-9a-f]{2}(\\.|)$', 'i');
		return packet.match(re) !== null;
	}

}

export default Packet;
