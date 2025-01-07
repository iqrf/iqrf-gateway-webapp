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
	<v-select
		v-model='stack'
		:label='$t("network.wireguard.tunnels.form.stack").toString()'
		:items='stackOptions'
	/>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';
import {ISelectItem} from '@/interfaces/Vuetify';
import {
	WireGuardIpStack
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';

/**
 * IP stack selector
 */
@Component
export default class IpStackSelector extends Vue {

	/**
	 * Selected stack
	 */
	@VModel({required: true}) stack!: WireGuardIpStack;

	/**
	 * @constant {Array<IOption>} stackOptions Stack select options
	 */
	private readonly stackOptions: Array<ISelectItem> = [
		{
			text: this.$t('network.wireguard.tunnels.form.stackTypes.ipv4'),
			value: WireGuardIpStack.IPV4
		},
		{
			text: this.$t('network.wireguard.tunnels.form.stackTypes.ipv6'),
			value: WireGuardIpStack.IPV6
		},
		{
			text: this.$t('network.wireguard.tunnels.form.stackTypes.both'),
			value: WireGuardIpStack.DUAL
		},
	];

}
</script>
