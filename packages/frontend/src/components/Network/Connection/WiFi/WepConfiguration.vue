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
		<ValidationProvider
			v-slot='{errors, touched, valid}'
			rules='required|wepKeyType'
			:custom-messages='{
				required: $t("network.wireless.errors.wepKeyType"),
				wepKeyType: $t("network.wireless.errors.wepKeyType"),
			}'
		>
			<v-select
				v-model='connection.wifi.security.wep.type'
				:items='keyOptions'
				:label='$t("network.wireless.form.wep.type").toString()'
				:placeholder='$t("network.wireless.errors.wepKeyType").toString()'
				:success='touched ? valid : null'
				:error-messages='errors'
			/>
		</ValidationProvider>
		<v-select
			v-if='connection.wifi.security.wep.type === WepKeyType.KEY'
			v-model='keyLength'
			:items='keyLengthOptions'
			:label='$t("network.wireless.form.wep.length").toString()'
		/>
		<ValidationProvider
			v-slot='{errors, touched, valid}'
			rules='required|integer|between:0,3|wepIndex'
			:custom-messages='{
				required: $t("network.wireless.errors.wepIndex"),
				integer: $t("forms.errors.integer"),
				between: $t("network.wireless.errors.wepIndexInvalid"),
				wepIndex: $t("network.wireless.errors.wepIndexKeyMissing"),
			}'
		>
			<v-text-field
				v-model.number='connection.wifi.security.wep.index'
				type='number'
				min='0'
				max='3'
				:label='$t("network.wireless.form.wep.index").toString()'
				:success='touched ? valid : null'
				:error-messages='errors'
			/>
		</ValidationProvider>
		<ValidationProvider
			v-for='(key, index) of connection.wifi.security.wep.keys'
			:key='index'
			v-slot='{errors, touched, valid}'
			:rules='{
				wepKey: connection.wifi.security.wep.type === WepKeyType.KEY,
			}'
			:custom-messages='{
				wepKey: (keyLength === WepKeyLen.BIT64 ?
					$t("network.wireless.errors.wepKey64Invalid") :
					$t("network.wireless.errors.wepKey128Invalid")),
			}'
		>
			<PasswordInput
				v-model='connection.wifi.security.wep.keys[index]'
				:label='$t("network.wireless.form.wep.keyNum", {index: index}).toString()'
				:success='touched ? valid : null'
				:error-messages='errors'
			/>
		</ValidationProvider>
	</div>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';
import {extend, ValidationProvider} from 'vee-validate';
import PasswordInput from '@/components/Core/PasswordInput.vue';

import {between, integer, required} from 'vee-validate/dist/rules';

import {ISelectItem} from '@/interfaces/Vuetify';
import {
	NetworkConnectionConfiguration
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import {
	WepKeyLen,
	WepKeyType
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';

/**
 * WEP configuration options
 */
@Component({
	components: {
		PasswordInput,
		ValidationProvider,
	},
	data: () => ({
		WepKeyLen,
		WepKeyType,
	}),
})
export default class WepConfiguration extends Vue {

	/**
	 * Edited connection.
	 */
	@VModel({required: true}) connection!: NetworkConnectionConfiguration;

	/**
	 * @constant {Array<ISelectItem>} keyOptions Wep key type select options
	 */
	private readonly keyOptions: Array<ISelectItem> = [
		{
			text: this.$t('network.wireless.form.wep.types.key'),
			value: WepKeyType.KEY
		},
		{
			text: this.$t('network.wireless.form.wep.types.passphrase'),
			value: WepKeyType.PASSPHRASE
		},
	];

	/**
	 * @var {WepKeyLen} keyLength WEP key length
	 */
	private keyLength = WepKeyLen.BIT64;

	/**
	 * @constant {Array<ISelectItem>} keyLengthOptions Wep key length select options
	 */
	private readonly keyLengthOptions: Array<ISelectItem> = [
		{
			text: this.$t('network.wireless.form.wep.lengths.64bit'),
			value: WepKeyLen.BIT64,
		},
		{
			text: this.$t('network.wireless.form.wep.lengths.128bit'),
			value: WepKeyLen.BIT128,
		},
	];

	/**
	 * Initializes validation rules
	 */
	protected created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		extend('wepIndex', (index: number) => {
			return this.connection.wifi?.security.wep.keys[index] !== '';
		});
		extend('wepKey', (key: string) => {
			if (this.keyLength === WepKeyLen.BIT64) {
				return /^(\w{5}|[0-9a-fA-F]{10})$/.test(key);
			}
			return /^(\w{13}|[0-9a-fA-F]{26})$/.test(key);
		});
		extend('wepKeyType', (key: string) => {
			return key !== WepKeyType.UNKNOWN;
		});
	}

}
</script>
