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

import { ModemState } from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { Language } from '@iqrf/iqrf-ui-common-types';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

import ModemStateBadge from '@/components/ip-network/modems/ModemStateBadge.vue';
import i18n from '@/plugins/i18n';
import { pluginFactory } from '@/tests/factories/pluginFactory';


describe('ModemStateBadge', (): void => {

	/// Test case type
	interface TestCase {
		/// Modem state
		state: ModemState;
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
			state: ModemState.connected,
			color: 'success',
			text: 'Connected',
		},
		{
			state: ModemState.connecting,
			color: 'secondary',
			text: 'Connecting',
		},
		{
			state: ModemState.disabled,
			color: 'secondary',
			text: 'Disabled',
		},
		{
			state: ModemState.disabling,
			color: 'secondary',
			text: 'Disabling',
		},
		{
			state: ModemState.disconnecting,
			color: 'secondary',
			text: 'Disconnecting',
		},
		{
			state: ModemState.enabled,
			color: 'secondary',
			text: 'Enabled',
		},
		{
			state: ModemState.enabling,
			color: 'secondary',
			text: 'Enabling',
		},
		{
			state: ModemState.failed,
			color: 'error',
			text: 'Modem is unusable',
		},
		{
			state: ModemState.initializing,
			color: 'secondary',
			text: 'Initializing',
		},
		{
			state: ModemState.locked,
			color: 'warning',
			text: 'Locked',
		},
		{
			state: ModemState.registered,
			color: 'info',
			text: 'Registered to network',
		},
		{
			state: ModemState.searching,
			color: 'secondary',
			text: 'Searching network',
		},
		{
			state: ModemState.unknown,
			color: 'warning',
			text: 'Unknown',
		},
	];

	beforeEach((): void => {
		// @ts-ignore Accessing ComputedRef
		i18n.global.locale.value = Language.English;
	});

	test.each(cases)('$text', ({ state, color, text }: TestCase): void => {
		expect.assertions(2);
		const wrapper = mount(ModemStateBadge, {
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
		const wrapper = mount(ModemStateBadge, {
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
