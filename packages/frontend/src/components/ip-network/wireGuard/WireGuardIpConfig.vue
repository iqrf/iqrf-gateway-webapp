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
	<v-row class='align-center' no-gutters>
		<v-col>
			<ITextInput
				v-model='ipAddress.address'
				:label='getAddressFieldName()'
				:rules='[
					(v: string) => validateIp(v, $t("components.ipNetwork.wireGuard.tunnels.configuration.validation.ipAddressInvalid")),
					(v: string) => ValidationRules.required(
						v,
						$t("components.ipNetwork.wireGuard.tunnels.configuration.validation.ipAddressRequired"),
					),
				]'
				@input='updateIp'
			/>
		</v-col>
		<!-- eslint-disable @intlify/vue-i18n/no-raw-text -->
		<span class='mx-2' style='font-size: 150%;'>/</span>
		<!-- eslint-enable @intlify/vue-i18n/no-raw-text -->
		<v-col cols='3'>
			<INumberInput
				v-model='ipAddress.prefix'
				:label='$t("components.ipNetwork.wireGuard.tunnels.configuration.form.ipAddressPrefix")'
				:rules='[
					(v: number) => validatePrefix(v),
					(v: number) => ValidationRules.required(
						v,
						$t("components.ipNetwork.wireGuard.tunnels.configuration.validation.ipAddressPrefixRequired"),
					),
				]'
				@input='updateIp'
			/>
		</v-col>
	</v-row>
</template>

<script setup lang='ts'>
import { WireGuardIpAddress, WireGuardIpStack } from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { INumberInput, ITextInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { computed, PropType, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
const componentProps = defineProps({
	type: {
		type: String as PropType<WireGuardIpStack>,
		required: true,
		validator: (v: WireGuardIpStack) => [WireGuardIpStack.IPV4, WireGuardIpStack.IPV6].includes(v),
	},
	recordId: {
		type: [String, Number] as PropType<string | number>,
		required: true,
	},
	ipAddress: {
		type: Object as PropType<WireGuardIpAddress>,
		required: false,
		default: undefined,
	},
});

const emit = defineEmits<{
	updateIp: [recordId: number | string, value: WireGuardIpAddress, type: WireGuardIpStack];
}>();

const ipAddress: Ref<WireGuardIpAddress> = computed(() => componentProps.ipAddress !== undefined ? componentProps.ipAddress : {
	address: '',
	prefix: componentProps.type === WireGuardIpStack.IPV4 ? 24 : 48,
});

/// i18n language localization
const i18n = useI18n();

/**
 * Validates IP address format and returns error message if does not match.
 * @param {string} address IP address to validate
 * @param {string} error Error to return if validation fails
 * @return {boolean|string} True if validation is successfull, error otherwise
 */
function validateIp(address: string, error: string): boolean | string {
	if (componentProps.type === WireGuardIpStack.IPV4) {
		return ValidationRules.ipv4Address(address, error);
	} else {
		return ValidationRules.ipv6Address(address, error);
	}
}

/**
 * Returns IP field name according to the stack type.
 * @return {string} IP address field name
 */
function getAddressFieldName(): string {
	if (componentProps.type === WireGuardIpStack.IPV4) {
		return i18n.t('components.ipNetwork.wireGuard.tunnels.configuration.form.ipv4address');
	} else {
		return i18n.t('components.ipNetwork.wireGuard.tunnels.configuration.form.ipv6address');
	}
}

/**
 * Validates IP address prefix
 * @param {number} prefix prefix to validate
 * @return {boolean|string} True if validation is successfull, error message otherwise
 */
function validatePrefix(prefix: number): boolean | string {
	if (componentProps.type === WireGuardIpStack.IPV4) {
		const error = i18n.t('components.ipNetwork.wireGuard.tunnels.configuration.validation.ipv4AddressPrefix');
		return ValidationRules.between(prefix, 0, 32, error);
	} else {
		const error = i18n.t('components.ipNetwork.wireGuard.tunnels.configuration.validation.ipv6AddressPrefix');
		return ValidationRules.between(prefix, 0, 128, error);
	}
}

/**
 * Emits IP address update
 */
async function updateIp(): Promise<void> {
	if (
		validateIp(ipAddress.value.address, '') === true &&
		validatePrefix(ipAddress.value.prefix) === true
	) {
		emit('updateIp', componentProps.recordId, ipAddress.value, componentProps.type);
	}
}

</script>
