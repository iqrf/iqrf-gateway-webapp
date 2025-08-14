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
	<ValidationProvider
		v-slot='{errors, touched, valid}'
		rules='required'
		:custom-messages='{
			required: $t("network.connection.errors.interface"),
		}'
	>
		<CSelect
			:value.sync='interface'
			:label='$t("network.connection.interface").toString()'
			:placeholder='$t("network.connection.errors.interface").toString()'
			:options='options'
			:is-valid='touched ? valid : null'
			:invalid-feedback='errors.join(", ")'
		/>
	</ValidationProvider>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';
import {CSelect} from '@coreui/vue/src';
import {extend, ValidationProvider} from 'vee-validate';

import {IOption} from '@/interfaces/Coreui';
import {required} from 'vee-validate/dist/rules';
import NetworkInterfaceService from '@/services/NetworkInterfaceService';
import {AxiosError} from 'axios';
import {extendedErrorToast} from '@/helpers/errorToast';
import {IModem} from '@/interfaces/Network/Mobile';

/**
 * GSM modem select field
 */
@Component({
	components: {
		CSelect,
		ValidationProvider,
	},
})
export default class GsmModemInput extends Vue {

	/**
	 * @property {string} interface Edited interface
	 */
	@VModel({required: true}) interface!: string;

	/**
	 * @property {Array<IOption>} options Array of CoreUI interface options
	 */
	private options: Array<IOption> = [];

	/**
	 * Initializes validation rules
	 */
	protected created(): void {
		extend('required', required);
	}

	protected mounted(): void {
		NetworkInterfaceService.listModems()
			.then((modems: Array<IModem>) => {
				modems.forEach((item: IModem) => {
					let label = item.interface;
					if (item.manufacturer !== null && item.model !== null) {
						label += ' (' + item.manufacturer + ' ' + item.model + ')';
					}
					this.options.push({label: label, value: item.interface});
				});
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'network.connection.messages.interfacesFetchFailed');
				this.$router.push('/ip-network/mobile');
			});
	}

}
</script>
