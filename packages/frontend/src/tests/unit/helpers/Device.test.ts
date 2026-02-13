/**
 * Copyright 2017-2026 IQRF Tech s.r.o.
 * Copyright 2019-2026 MICRORISC s.r.o.
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

import {
	mdiCheck,
	mdiClose,
	mdiHomeOutline,
	mdiSignalCellularOutline,
} from '@mdi/js';
import { describe, expect, test } from 'vitest';

import Device from '@/helpers/device';

describe('IQRF network device', (): void => {

	test('coordinator', (): void => {
		expect.assertions(8);
		const device: Device = new Device(0, true, false, false, false);
		expect(device.address).toStrictEqual(0);
		expect(device.coordinator).toBe(true);
		expect(device.bonded).toBe(false);
		expect(device.discovered).toBe(false);
		expect(device.online).toBe(false);
		expect(device.getIcon()).toStrictEqual(mdiHomeOutline);
		expect(device.getIconColor()).toStrictEqual('info');
		expect(device.hasLink()).toBe(true);
	});

	test('bonded', (): void => {
		expect.assertions(8);
		const device: Device = new Device(1, false, true, false, false);
		expect(device.address).toStrictEqual(1);
		expect(device.coordinator).toBe(false);
		expect(device.bonded).toBe(true);
		expect(device.discovered).toBe(false);
		expect(device.online).toBe(false);
		expect(device.getIcon()).toStrictEqual(mdiCheck);
		expect(device.getIconColor()).toStrictEqual('info');
		expect(device.hasLink()).toBe(true);
	});

	test('bonded online', (): void => {
		expect.assertions(8);
		const device: Device = new Device(1, false, true, false, true);
		expect(device.address).toStrictEqual(1);
		expect(device.coordinator).toBe(false);
		expect(device.bonded).toBe(true);
		expect(device.discovered).toBe(false);
		expect(device.online).toBe(true);
		expect(device.getIcon()).toStrictEqual(mdiCheck);
		expect(device.getIconColor()).toStrictEqual('success');
		expect(device.hasLink()).toBe(true);
	});

	test('discovered', (): void => {
		expect.assertions(8);
		const device: Device = new Device(2, false, true, true, false);
		expect(device.address).toStrictEqual(2);
		expect(device.coordinator).toBe(false);
		expect(device.bonded).toBe(true);
		expect(device.discovered).toBe(true);
		expect(device.online).toBe(false);
		expect(device.getIcon()).toStrictEqual(mdiSignalCellularOutline);
		expect(device.getIconColor()).toStrictEqual('info');
		expect(device.hasLink()).toBe(true);
	});

	test('discovered online', (): void => {
		expect.assertions(8);
		const device: Device = new Device(2, false, true, true, true);
		expect(device.address).toStrictEqual(2);
		expect(device.coordinator).toBe(false);
		expect(device.bonded).toBe(true);
		expect(device.discovered).toBe(true);
		expect(device.online).toBe(true);
		expect(device.getIcon()).toStrictEqual(mdiSignalCellularOutline);
		expect(device.getIconColor()).toStrictEqual('success');
		expect(device.hasLink()).toBe(true);
	});

	test('none', (): void => {
		expect.assertions(8);
		const device: Device = new Device(3, false, false, false, false);
		expect(device.address).toStrictEqual(3);
		expect(device.coordinator).toBe(false);
		expect(device.bonded).toBe(false);
		expect(device.discovered).toBe(false);
		expect(device.online).toBe(false);
		expect(device.getIcon()).toStrictEqual(mdiClose);
		expect(device.getIconColor()).toStrictEqual('error');
		expect(device.hasLink()).toBe(false);
	});

});
