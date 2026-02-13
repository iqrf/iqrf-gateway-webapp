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

import { NetworkInterfaceState } from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { Language } from '@iqrf/iqrf-ui-common-types';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

import InterfaceStateBadge from '@/components/ip-network/interfaces/InterfaceStateBadge.vue';
import i18n from '@/plugins/i18n';
import { pluginFactory } from '@/tests/factories/pluginFactory';


describe('InterfaceStateBadge', (): void => {

	/// Test case type
	interface TestCase {
		/// Modem state
		state: NetworkInterfaceState;
		/// Expected badge color
		color: string;
		/// Expected badge text
		text: string;
	}

	/**
	 * @const {TestCase[]} cases Test cases
	 */
	const cases: TestCase[] = [
		{
			state: NetworkInterfaceState.CheckingIpConnectivity,
			color: 'primary',
			text: 'Checking IP connectivity',
		},
		{
			state: NetworkInterfaceState.Configuring,
			color: 'primary',
			text: 'Configuring',
		},
		{
			state: NetworkInterfaceState.Connected,
			color: 'success',
			text: 'Connected',
		},
		{
			state: NetworkInterfaceState.Deactivating,
			color: 'warning',
			text: 'Deactivating',
		},
		{
			state: NetworkInterfaceState.Disconnected,
			color: 'error',
			text: 'Disconnected',
		},
		{
			state: NetworkInterfaceState.Failed,
			color: 'secondary',
			text: 'Failed',
		},
		{
			state: NetworkInterfaceState.GettingIpConfiguration,
			color: 'primary',
			text: 'Getting IP configuration',
		},
		{
			state: NetworkInterfaceState.NeedAuthentication,
			color: 'primary',
			text: 'Need authentication',
		},
		{
			state: NetworkInterfaceState.Prepare,
			color: 'primary',
			text: 'Prepare',
		},
		{
			state: NetworkInterfaceState.StartingSecondaries,
			color: 'primary',
			text: 'Starting secondary connections',
		},
		{
			state: NetworkInterfaceState.Unavailable,
			color: 'secondary',
			text: 'Unavailable',
		},
		{
			state: NetworkInterfaceState.Unknown,
			color: 'secondary',
			text: 'Unknown',
		},
		{
			state: NetworkInterfaceState.Unmanaged,
			color: 'secondary',
			text: 'Unmanaged',
		},
	];

	beforeEach((): void => {
		// @ts-ignore Accessing ComputedRef
		i18n.global.locale.value = Language.English;
	});

	test.each(cases)('$text', ({ state, color, text }: TestCase): void => {
		expect.assertions(2);
		const wrapper = mount(InterfaceStateBadge, {
			props: {
				state: state,
			},
			global: {
				plugins: pluginFactory(i18n),
			},
		});

		// @ts-ignore Accessing private property
		expect(wrapper.vm.color).toStrictEqual(color);
		// @ts-ignore Accessing private property
		expect(wrapper.vm.text).toStrictEqual(text);
	});

	test('Invalid modem state', (): void => {
		expect.assertions(2);
		const wrapper = mount(InterfaceStateBadge, {
			props: {
				// @ts-ignore Invalid modem state
				state: 'Invalid',
			},
			global: {
				plugins: pluginFactory(i18n),
			},
		});

		// @ts-ignore Accessing private property
		expect(wrapper.vm.color).toStrictEqual('secondary');
		// @ts-ignore Accessing private property
		expect(wrapper.vm.text).toStrictEqual('');
	});

});
