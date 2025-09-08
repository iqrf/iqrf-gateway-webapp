<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->+

<template>
	<div>
		<h2 class='mb-3 text-h6 text-grey'>
			{{ $t("components.ipNetwork.connections.form.ipv4.title") }}
		</h2>
		<IPv4ConfigurationMethodInput
			v-model='configuration.ipv4.method'
			:rules='[
				(v: string|null) => ValidationRules.required(v, $t("components.ipNetwork.connections.errors.ipv4.method.required")),
				(v: IPv4ConfigurationMethod) => {
					if (v === IPv4ConfigurationMethod.DISABLED && configuration.ipv6.method === IPv6ConfigurationMethod.DISABLED) {
						return $t("components.ipNetwork.connections.errors.ip.disabledBothIpStacks");
					}
					return true;
				},
			]'
			:type='configuration.type'
			@update:model-value='onConfigurationMethodChange'
		/>
		<div v-if='configuration.ipv4.method === IPv4ConfigurationMethod.MANUAL'>
			<v-text-field
				v-model='configuration.ipv4.addresses[0].address'
				v-maska='maskaOptions'
				:label='$t("components.ipNetwork.connections.form.ipv4.address")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.ipNetwork.connections.errors.ipv4.address.required")),
					(v: string) => ValidationRules.ipv4Address(v, $t("components.ipNetwork.connections.errors.ipv4.address.ipv4Address")),
					() => ipv4SubnetCheck($t("components.ipNetwork.connections.errors.ipv4.address.ipv4Subnet")),
				]'
				required
				:prepend-inner-icon='mdiIpNetwork'
			/>
			<v-text-field
				v-model='configuration.ipv4.addresses[0].mask'
				v-maska='maskaOptions'
				:label='$t("components.ipNetwork.connections.form.ipv4.mask")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.ipNetwork.connections.errors.ipv4.mask.required")),
					(v: string) => ValidationRules.ipv4Address(v, $t("components.ipNetwork.connections.errors.ipv4.mask.ipv4Address")),
				]'
				required
				:prepend-inner-icon='mdiLan'
			/>
			<v-text-field
				v-model='configuration.ipv4.gateway'
				v-maska='maskaOptions'
				:label='$t("components.ipNetwork.connections.form.ipv4.gateway")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.ipNetwork.connections.errors.ipv4.gateway.required")),
					(v: string) => ValidationRules.ipv4Address(v, $t("components.ipNetwork.connections.errors.ipv4.gateway.ipv4Address")),
					() => ipv4SubnetCheck($t("components.ipNetwork.connections.errors.ipv4.gateway.ipv4Subnet")),
				]'
				required
				:prepend-inner-icon='mdiWan'
			/>
			<h3 class='mb-3 text-h6 text-grey'>
				{{ $t("components.ipNetwork.connections.form.ipv4.dns.title") }}
			</h3>
			<v-text-field
				v-for='(server, index) in configuration.ipv4.dns'
				:key='`ipv4.dns.${index}`'
				v-model='server.address'
				v-maska='maskaOptions'
				:label='$t("components.ipNetwork.connections.form.ipv4.dns.address")'
				:rules='[
					(v: string|null) => ValidationRules.requiredIf(v, noIpv6Dns, $t("components.ipNetwork.connections.errors.ipv4.dns.required")),
					(v: string) => ValidationRules.ipv4Address(v, $t("components.ipNetwork.connections.errors.ipv4.dns.ipv4Address")),
				]'
				required
				:prepend-inner-icon='mdiServerNetwork'
			>
				<template #append-inner>
					<v-btn
						color='success'
						size='small'
						variant='flat'
						@click='() => addDnsServer()'
					>
						<v-icon :icon='mdiPlus' />
					</v-btn>
					<v-btn
						color='red'
						:disabled='configuration.ipv4.dns.length === 1'
						size='small'
						variant='flat'
						@click='() => removeDnsServer(index)'
					>
						<v-icon :icon='mdiMinus' />
					</v-btn>
				</template>
			</v-text-field>
		</div>
	</div>
</template>

<script setup lang='ts'>
import {
	DnsServerConfiguration,
	IPv4ConfigurationMethod, IPv6ConfigurationMethod,
	type NetworkConnectionConfiguration,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import {
	mdiIpNetwork,
	mdiLan,
	mdiMinus,
	mdiPlus,
	mdiServerNetwork,
	mdiWan,
} from '@mdi/js';
import { MaskInputOptions } from 'maska';
import { vMaska } from 'maska/vue';
import { computed, ComputedRef, type PropType } from 'vue';
import { z } from 'zod';

import IPv4ConfigurationMethodInput
	from '@/components/ip-network/connections/ip/IPv4ConfigurationMethodInput.vue';
import ValidationRules from '@/helpers/ValidationRules';

/// Network connection configuration
const configuration = defineModel({
	type: Object as PropType<NetworkConnectionConfiguration>,
	required: true,
});
/// IPv4 address mask input options
const maskaOptions: MaskInputOptions = {
	mask: '#00.#00.#00.#00',
	tokens: {
		'0': {
			pattern: /\d/,
			optional:true,
		},
	},
};
/// No IPv6 DNS servers are specified
const noIpv6Dns: ComputedRef<boolean> = computed((): boolean =>
	configuration.value.ipv6.method === IPv6ConfigurationMethod.DISABLED ||
	(configuration.value.ipv6.method === IPv6ConfigurationMethod.MANUAL &&
		configuration.value.ipv6.dns.filter((server: DnsServerConfiguration): boolean => {
			const ipv6Validator: z.ZodString = z.string().ip({ version: 'v6' });
			return server.address !== '' && ipv6Validator.safeParse(server.address).success;
		}).length === 0),
);

/**
 * Converts an IPv4 address string to an integer representation
 * @param {string} address IPv4 address string
 * @return {number} Integer representation of the IPv4 address
 */
function ipv4ToInt(address: string): number {
	return address.split('.').reduce((acc: number, oct: string): number => {
		return (acc * 256) + Number.parseInt(oct, 10);
	}, 0) >>> 0;
}

/**
 * Checks if the subnet is correct
 * @param {string} error Error message
 * @return {boolean|string} True if the subnet is correct, error message otherwise
 */
function ipv4SubnetCheck(error: string): boolean | string {
	if (configuration.value.ipv4.method !== IPv4ConfigurationMethod.MANUAL) {
		return true;
	}
	if (['', null].includes(configuration.value.ipv4.gateway)) {
		return true;
	}
	const address: string = configuration.value.ipv4.addresses[0].address;
	const addressInt: number = ipv4ToInt(address);

	const gateway: string = configuration.value.ipv4.gateway ?? '';
	const gatewayInt: number = ipv4ToInt(gateway);

	const mask: string = configuration.value.ipv4.addresses[0].mask;
	const maskInt: number = ipv4ToInt(mask);

	if ((gatewayInt & maskInt) === (addressInt & maskInt)) {
		return true;
	}
	return error;
}

/**
 * Adds a new IPv4 DNS object to configuration
 */
function addDnsServer(): void {
	configuration.value.ipv4.dns.push({ address: '' });
}

/**
 * Removes an IPv4 DNS object from configuration
 * @param {number} index Index of the DNS object to remove
 */
function removeDnsServer(index: number): void {
	configuration.value.ipv4.dns.splice(index, 1);
}

/**
 * Populates IPv4 configuration object if empty on method change
 */
function onConfigurationMethodChange(): void {
	if ([
		IPv4ConfigurationMethod.AUTO,
		IPv4ConfigurationMethod.DISABLED,
	].includes(configuration.value.ipv4.method)) {
		return;
	}
	if (configuration.value.ipv4.method === IPv4ConfigurationMethod.MANUAL) {
		configuration.value.ipv4.addresses = configuration.value.ipv4.current?.addresses ?? [];
		configuration.value.ipv4.dns = configuration.value.ipv4.current?.dns ?? [];
		configuration.value.ipv4.gateway = configuration.value.ipv4.current?.gateway ?? '';
	}
	if (configuration.value.ipv4.addresses.length === 0) {
		configuration.value.ipv4.addresses.push({ address: '', prefix: 32, mask: '' });
	}
	if (configuration.value.ipv4.dns.length === 0) {
		addDnsServer();
	}
}
</script>
