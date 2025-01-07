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
import {Component, Prop, VModel, Vue} from 'vue-property-decorator';
import {extend, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {required} from 'vee-validate/dist/rules';

import {AxiosError} from 'axios';
import {ISelectItem} from '@/interfaces/Vuetify';
import {
	NetworkInterface, NetworkInterfaceType
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import {useApiClient} from '@/services/ApiClient';

/**
 * Network interface select field
 */
@Component({
	components: {
		ValidationProvider,
	},
})
export default class InterfaceInput extends Vue {

	/**
	 * @property {NetworkInterfaceType} type Network interface type
	 */
	@Prop({required: true}) type!: NetworkInterfaceType;

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
		useApiClient().getNetworkServices().getNetworkInterfaceService().list(this.type)
			.then((interfaces: Array<NetworkInterface>) => {
				interfaces.forEach((item: NetworkInterface) => {
					let label = item.name;
					if (item.manufacturer !== null && item.model !== null) {
						label = item.name + ' (' + item.manufacturer + ' ' + item.model + ')';
					}
					this.options.push({text: label, value: item.name});
				});
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'network.connection.messages.interfacesFetchFailed');
				if (this.type === NetworkInterfaceType.ETHERNET) {
					this.$router.push('/ip-network/ethernet');
				} else if (this.type === NetworkInterfaceType.WIFI) {
					this.$router.push('/ip-network/wireless');
				}
			});
	}

}
</script>
