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

import { IqrfGatewayDaemonWsTlsModes, IqrfGatewayDaemonWsTransportModes } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { computed, type ComputedRef } from 'vue';

import i18n from '@/plugins/i18n';
import { type SelectOption } from '@/types/vuetify';

/**
 * Get TLS mode options for vuetify select
 * @return {ComputedRef<SelectOption<IqrfGatewayDaemonWsTlsModes>[]>} TLS mode options
 */
export function getWebSocketTlsModeOptions(): ComputedRef<SelectOption<IqrfGatewayDaemonWsTlsModes>[]> {
	return computed(() => [
		{
			title: i18n.global.t('components.config.daemon.connections.ws.tlsModes.modern'),
			value: IqrfGatewayDaemonWsTlsModes.Modern,
		},
		{
			title: i18n.global.t('components.config.daemon.connections.ws.tlsModes.intermediate'),
			value: IqrfGatewayDaemonWsTlsModes.Intermediate,
		},
		{
			title: i18n.global.t('components.config.daemon.connections.ws.tlsModes.old'),
			value: IqrfGatewayDaemonWsTlsModes.Old,
		},
	]);
}

/**
 * Get a description for TLS mode
 * @param {IqrfGatewayDaemonWsTlsModes} mode TLS mode
 * @return {string} Description string
 */
export function getWebSocketTlsModeDescription(mode: IqrfGatewayDaemonWsTlsModes): string {
	if (mode === IqrfGatewayDaemonWsTlsModes.Modern) {
		return i18n.global.t('components.config.daemon.connections.ws.notes.tlsModes.modern');
	}
	if (mode === IqrfGatewayDaemonWsTlsModes.Intermediate) {
		return i18n.global.t('components.config.daemon.connections.ws.notes.tlsModes.intermediate');
	}
	return i18n.global.t('components.config.daemon.connections.ws.notes.tlsModes.old');
}

/**
 * Get transport mode options for vuetify select
 * @return {ComputedRef<SelectOption<IqrfGatewayDaemonWsTransportModes>[]>} Transport mode options
 */
export function getWebSocketTransportModeOptions(): ComputedRef<SelectOption<IqrfGatewayDaemonWsTransportModes>[]> {
	return computed(() => [
		{
			title: i18n.global.t('components.config.daemon.connections.ws.transportModes.plain'),
			value: IqrfGatewayDaemonWsTransportModes.Plain,
		},
		{
			title: i18n.global.t('components.config.daemon.connections.ws.transportModes.tls'),
			value: IqrfGatewayDaemonWsTransportModes.Tls,
		},
		{
			title: i18n.global.t('components.config.daemon.connections.ws.transportModes.both'),
			value: IqrfGatewayDaemonWsTransportModes.Both,
		},
	]);
}

/**
 * Get a description for transport mode
 * @param {IqrfGatewayDaemonWsTransportModes} mode Transport mode
 * @return {string} Description string
 */
export function getWebSocketTransportModeDescription(mode: IqrfGatewayDaemonWsTransportModes): string {
	if (mode === IqrfGatewayDaemonWsTransportModes.Plain) {
		return i18n.global.t('components.config.daemon.connections.ws.notes.transportModes.plain');
	}
	if (mode === IqrfGatewayDaemonWsTransportModes.Tls) {
		return i18n.global.t('components.config.daemon.connections.ws.notes.transportModes.tls');
	}
	return i18n.global.t('components.config.daemon.connections.ws.notes.transportModes.both');
}
