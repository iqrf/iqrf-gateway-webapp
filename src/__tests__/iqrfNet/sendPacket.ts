/**
 * Copyright 2021 Roman Ondráček <xondra58@stud.fit.vutbr.cz>
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

import Packet from '@/iqrfNet/sendPacket';

test('Update Network address in DPA packet', () => {
	const expected = '01.00.02.00.ff.ff';
	const packet = '00.00.02.00.ff.ff';
	expect(Packet.updateNadr(packet, 1)).toBe(expected);
});

test('Detect 12 s timeout for Local bonding DPA packet', () => {
	const packet = new Packet(0, 0, 4, 65535, [0, 0]);
	const expected = 12000;
	expect(packet.detectTimeout()).toBe(expected);
});

test('Detect disabled timeout for IQMESH Discovery DPA packet', () => {
	const packet = new Packet(0, 0, 7, 65535, [7, 0]);
	const expected = 0;
	expect(packet.detectTimeout()).toBe(expected);
});

test('Detect 6 s timeout for FRC DPA packets', () => {
	const packet = new Packet(0, 0x0d, 0, 65535, [0, 0, 0]);
	const expected = 6000;
	expect(packet.detectTimeout()).toBe(expected);
});

test('Detect no timeout override for DPA packet', () => {
	const packet = new Packet(0, 2, 0, 65535, []);
	expect(packet.detectTimeout()).toBeNull();
});

test('Deserialize invalid DPA packet from string', () => {
	const packet = '01.00.02.00.ff';
	expect(() => Packet.parse(packet)).toThrow('Invalid DPA packet length');
});

test('Deserialize DPA packet (without PDATA) from string', () => {
	const packet = '01.00.02.00.ff.ff';
	const expected = new Packet(1, 2, 0, 65535, []);
	expect(Packet.parse(packet)).toEqual(expected);
});

test('Deserialize DPA packet (with PDATA) from string', () => {
	const packet = '00.00.00.04.ff.ff.00.00';
	const expected = new Packet(0, 0, 4, 65535, [0, 0]);
	expect(Packet.parse(packet)).toEqual(expected);
});

test('Serialize DPA packet (without PDATA) into string', () => {
	const packet = new Packet(1, 2, 0, 65535, []);
	const expected = '01.00.02.00.ff.ff';
	expect(packet.toString()).toBe(expected);
});

test('Serialize DPA packet (with PDATA) into string (with PDATA)', () => {
	const packet = new Packet(0, 0, 4, 65535, [0, 0]);
	const expected = '00.00.00.04.ff.ff.00.00';
	expect(packet.toString()).toBe(expected);
});

test('Serialize DPA packet (with PDATA) into string (without PDATA)', () => {
	const packet = new Packet(0, 0, 4, 65535, [0, 0]);
	const expected = '00.00.00.04.ff.ff';
	expect(packet.toString(false)).toBe(expected);
});

test('Validate DPA packet', () => {
	let packet = '01.00.02.00.ff.ff';
	expect(Packet.validatePacket(packet)).toBeTruthy();
	packet += '.';
	expect(Packet.validatePacket(packet)).toBeTruthy();
	packet = 'nonsense';
	expect(Packet.validatePacket(packet)).toBeFalsy();
});

