<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
		<v-select
			v-model='interface'
			:label='$t("network.connection.interface").toString()'
			:placeholder='$t("network.connection.errors.interface").toString()'
			:items='options'
			:success='touched ? valid : null'
			:error-messages='errors'
		/>
	</ValidationProvider>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';
import {extend, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {required} from 'vee-validate/dist/rules';

import {AxiosError} from 'axios';
import {ISelectItem} from '@/interfaces/Vuetify';
import {Modem} from '@iqrf/iqrf-gateway-webapp-client/types/Network/Modem';
import {useApiClient} from '@/services/ApiClient';

/**
 * GSM modem select field
 */
@Component({
	components: {
		ValidationProvider,
	},
})
export default class GsmModemInput extends Vue {

	/**
	 * @property {string} interface Edited interface
	 */
	@VModel({required: true}) interface!: string;

	/**
	 * @property {Array<IOption>} options Interface select options
	 */
	private options: Array<ISelectItem> = [];

	/**
	 * Initializes validation rules
	 */
	protected created(): void {
		extend('required', required);
	}

	protected mounted(): void {
		useApiClient().getNetworkServices().getModemService().list()
			.then((modems: Array<Modem>) => {
				modems.forEach((item: Modem) => {
					let label = item.interface;
					if (item.manufacturer !== null && item.model !== null) {
						label += ' (' + item.manufacturer + ' ' + item.model + ')';
					}
					this.options.push({text: label, value: item.interface});
				});
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'network.connection.messages.interfacesFetchFailed');
				this.$router.push('/ip-network/mobile');
			});
	}

}
</script>
