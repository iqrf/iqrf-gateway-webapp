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

import { IqrfGatewayDaemonWsTransportModes } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { Language } from '@iqrf/iqrf-ui-common-types';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

import WebSocketTransportModeBadge from '@/components/config/daemon/connections/websocket/WebSocketTransportModeBadge.vue';
import i18n from '@/plugins/i18n';
import { pluginFactory } from '@/tests/factories/pluginFactory';

describe('WebSocketTransportModeBadge', (): void => {

	interface TestCase {
		/// WebSocket transport mode
		mode: IqrfGatewayDaemonWsTransportModes;
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
			mode: IqrfGatewayDaemonWsTransportModes.Plain,
			color: 'red',
			text: 'Plain',
		},
		{
			mode: IqrfGatewayDaemonWsTransportModes.Both,
			color: 'warning',
			text: 'Plain+TLS',
		},
		{
			mode: IqrfGatewayDaemonWsTransportModes.Tls,
			color: 'success',
			text: 'TLS',
		},
	];

	beforeEach((): void => {
		// @ts-ignore Accessing ComputedRef
		i18n.global.locale.value = Language.English;
	});

	test.each(cases)('$text', ({ mode, color, text }: TestCase): void => {
		expect.assertions(2);
		const wrapper = mount(WebSocketTransportModeBadge, {
			props: {
				mode: mode,
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

	test('Invalid WebSocket transport mode', (): void => {
		expect.assertions(2);
		const wrapper = mount(WebSocketTransportModeBadge, {
			props: {
				// @ts-ignore Invalid WebSocket transport mode
				mode: 'Invalid',
			},
			global: {
				plugins: pluginFactory(i18n),
			},
		});

		// @ts-ignore Accessing private property
		expect(wrapper.vm.color).toStrictEqual('grey');
		// @ts-ignore Accessing private property
		expect(wrapper.vm.text).toStrictEqual('');
	});

});
