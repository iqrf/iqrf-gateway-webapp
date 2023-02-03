<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
	<CSelect
		:value.sync='stack'
		:options='stackOptions'
		:label='$t("network.wireguard.tunnels.form.stack").toString()'
	/>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';
import {CSelect} from '@coreui/vue/src';
import {WireguardStack} from '@/enums/Network/Wireguard';
import {IOption} from '@/interfaces/Coreui';

/**
 * IP stack selector
 */
@Component({
	components: {
		CSelect,
	},
})
export default class IpStackSelector extends Vue {

	/**
	 * Selected stack
	 */
	@VModel({required: true}) stack!: WireguardStack;

	/**
	 * @constant {Array<IOption>} stackOptions Array of CoreUI select stack options
	 */
	private stackOptions: Array<IOption> = [
		{
			label: this.$t('network.wireguard.tunnels.form.stackTypes.ipv4'),
			value: WireguardStack.IPV4
		},
		{
			label: this.$t('network.wireguard.tunnels.form.stackTypes.ipv6'),
			value: WireguardStack.IPV6
		},
		{
			label: this.$t('network.wireguard.tunnels.form.stackTypes.both'),
			value: WireguardStack.DUAL
		},
	];

}
</script>
