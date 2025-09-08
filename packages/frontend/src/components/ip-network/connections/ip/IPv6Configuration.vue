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
-->

<template>
	<div>
		<h2 class='mb-3 text-h6 text-grey'>
			{{ $t("components.ipNetwork.connections.form.ipv6.title") }}
		</h2>
		<IPv6ConfigurationMethodInput
			v-model='configuration.ipv6.method'
			:rules='[
				(v: string|null) => ValidationRules.required(v, $t("components.ipNetwork.connections.errors.ipv6.method.required")),
				(v: IPv6ConfigurationMethod) => {
					if (v === IPv6ConfigurationMethod.DISABLED && configuration.ipv4.method === IPv4ConfigurationMethod.DISABLED) {
						return $t("components.ipNetwork.connections.errors.ip.disabledBothIpStacks");
					}
					return true;
				},
			]'
			:type='configuration.type'
			@update:model-value='onConfigurationMethodChange'
		/>
		<div v-if='configuration.ipv6.method === IPv6ConfigurationMethod.MANUAL'>
			<v-row
				v-for='(address, index) in configuration.ipv6.addresses'
				:key='`ipv6.address.${index}`'
				dense
			>
				<v-col :cols='12' :md='8' :sm='7'>
					<v-text-field
						v-model='address.address'
						:label='$t("components.ipNetwork.connections.form.ipv6.address")'
						:rules='[
							(v: string|null) => ValidationRules.required(v, $t("components.ipNetwork.connections.errors.ipv6.address.required")),
							(v: string) => ValidationRules.ipv6Address(v, $t("components.ipNetwork.connections.errors.ipv6.address.ipv6Address")),
						]'
						required
						:prepend-inner-icon='mdiIpNetwork'
					/>
				</v-col>
				<v-col :cols='12' :md='4' :sm='5'>
					<v-text-field
						v-model.number='address.prefix'
						:label='$t("components.ipNetwork.connections.form.ipv6.prefix")'
						:rules='[
							(v: unknown) => ValidationRules.required(v, $t("components.ipNetwork.connections.errors.ipv6.prefix.required")),
							(v: number) => ValidationRules.between(v, 0, 128, $t("components.ipNetwork.connections.errors.ipv6.prefix.between")),
						]'
						required
						:prepend-inner-icon='mdiLan'
					>
						<template #append-inner>
							<v-btn
								color='success'
								size='small'
								variant='flat'
								@click='() => addAddress()'
							>
								<v-icon :icon='mdiPlus' />
							</v-btn>
							<v-btn
								color='red'
								:disabled='configuration.ipv6.addresses.length === 1'
								size='small'
								variant='flat'
								@click='() => removeAddress(index)'
							>
								<v-icon :icon='mdiMinus' />
							</v-btn>
						</template>
					</v-text-field>
				</v-col>
			</v-row>
			<v-text-field
				v-model='configuration.ipv6.gateway'
				:label='$t("components.ipNetwork.connections.form.ipv6.gateway")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.ipNetwork.connections.errors.ipv6.gateway.required")),
					(v: string) => ValidationRules.ipv6Address(v, $t("components.ipNetwork.connections.errors.ipv6.gateway.ipv6Address")),
				]'
				required
				:prepend-inner-icon='mdiWan'
			/>
			<h3 class='mb-3 text-h6 text-grey'>
				{{ $t("components.ipNetwork.connections.form.ipv6.dns.title") }}
			</h3>
			<v-text-field
				v-for='(server, index) in configuration.ipv6.dns'
				:key='`ipv6.dns.${index}`'
				v-model='server.address'
				:label='$t("components.ipNetwork.connections.form.ipv6.dns.address")'
				:rules='[
					(v: string|null) => ValidationRules.requiredIf(v, noIpv4Dns, $t("components.ipNetwork.connections.errors.ipv6.dns.required")),
					(v: string) => ValidationRules.ipv6Address(v, $t("components.ipNetwork.connections.errors.ipv6.dns.ipv6Address")),
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
						:disabled='configuration.ipv6.dns.length === 1'
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
	IPv4ConfigurationMethod,
	IPv6ConfigurationMethod,
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
import { computed, ComputedRef, type PropType } from 'vue';
import { z } from 'zod';

import IPv6ConfigurationMethodInput
	from '@/components/ip-network/connections/ip/IPv6ConfigurationMethodInput.vue';
import ValidationRules from '@/helpers/ValidationRules';

/// Network connection configuration
const configuration = defineModel({
	type: Object as PropType<NetworkConnectionConfiguration>,
	required: true,
});
/// No IPv4 DNS servers are specified
const noIpv4Dns: ComputedRef<boolean> = computed((): boolean =>
	configuration.value.ipv4.method === IPv4ConfigurationMethod.DISABLED ||
	(configuration.value.ipv4.method === IPv4ConfigurationMethod.MANUAL &&
	configuration.value.ipv4.dns.filter((server: DnsServerConfiguration): boolean => {
		const ipv4Validator: z.ZodString = z.string().ip({ version: 'v4' });
		return server.address !== '' && ipv4Validator.safeParse(server.address).success;
	}).length === 0),
);

/**
 * Adds a new IPv6 address object to configuration
 */
function addAddress(): void {
	configuration.value.ipv6.addresses.push({ address: '', prefix: 64 });
}

/**
 * Adds a new IPv6 DNS object to configuration
 */
function addDnsServer(): void {
	configuration.value.ipv6.dns.push({ address: '' });
}

/**
 * Removes an IPv6 address object from configuration
 * @param {number} index Index of the address object to remove
 */
function removeAddress(index: number): void {
	configuration.value.ipv6.addresses.splice(index, 1);
}

/**
 * Removes an IPv6 DNS object from configuration
 * @param {number} index Index of the DNS object to remove
 */
function removeDnsServer(index: number): void {
	configuration.value.ipv6.dns.splice(index, 1);
}

/**
 * Populates IPv6 configuration object if empty on method change
 */
function onConfigurationMethodChange(): void {
	if ([
		IPv6ConfigurationMethod.AUTO,
		IPv6ConfigurationMethod.DHCP,
		IPv6ConfigurationMethod.DISABLED,
	].includes(configuration.value.ipv6.method)) {
		return;
	}
	if (configuration.value.ipv6.method === IPv6ConfigurationMethod.MANUAL) {
		configuration.value.ipv6.addresses = configuration.value.ipv6.current?.addresses ?? [];
		configuration.value.ipv6.dns = configuration.value.ipv6.current?.dns ?? [];
		configuration.value.ipv6.gateway = configuration.value.ipv6.current?.gateway ?? '';
	}
	if (configuration.value.ipv6.addresses.length === 0) {
		addAddress();
	}
	if (configuration.value.ipv6.dns.length === 0) {
		addDnsServer();
	}
}
</script>
