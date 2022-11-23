<template>
	<div class='form-group'>
		<ValidationProvider
			v-slot='{errors, touched, valid}'
			rules='required|wepKeyType'
			:custom-messages='{
				required: $t("network.wireless.errors.wepKeyType"),
				wepKeyType: $t("network.wireless.errors.wepKeyType"),
			}'
		>
			<CSelect
				:value.sync='connection.wifi.security.wep.type'
				:options='keyOptions'
				:label='$t("network.wireless.form.wep.type").toString()'
				:placeholder='$t("network.wireless.errors.wepKeyType").toString()'
				:is-valid='touched ? valid : null'
				:invalid-feedback='errors.join(", ")'
			/>
		</ValidationProvider>
		<CSelect
			v-if='connection.wifi.security.wep.type === WepKeyType.KEY'
			:value.sync='keyLength'
			:options='keyLengthOptions'
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
			<CInput
				v-model.number='connection.wifi.security.wep.index'
				type='number'
				min='0'
				max='3'
				:label='$t("network.wireless.form.wep.index").toString()'
				:is-valid='touched ? valid : null'
				:invalid-feedback='errors.join(", ")'
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
				:is-valid='touched ? valid : null'
				:invalid-feedback='errors.join(", ")'
			/>
		</ValidationProvider>
	</div>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';
import {CInput, CSelect} from '@coreui/vue/src';
import {extend, ValidationProvider} from 'vee-validate';
import {between, integer, required} from 'vee-validate/dist/rules';

import PasswordInput from '@/components/Core/PasswordInput.vue';

import {WepKeyLen, WepKeyType} from '@/enums/Network/WifiSecurity';

import {IOption} from '@/interfaces/Coreui';
import {IConnection} from '@/interfaces/Network/Connection';

/**
 * WEP configuration options
 */
@Component({
	components: {
		CInput,
		CSelect,
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
	@VModel({required: true}) connection!: IConnection;

	/**
	 * @constant {Array<IOption>} keyOptions CoreUI wep key type select options
	 */
	private keyOptions: Array<IOption> = [
		{
			label: this.$t('network.wireless.form.wep.types.key'),
			value: WepKeyType.KEY
		},
		{
			label: this.$t('network.wireless.form.wep.types.passphrase'),
			value: WepKeyType.PASSPHRASE
		},
	];

	/**
	 * @var {WepKeyLen} keyLength WEP key length
	 */
	private keyLength = WepKeyLen.BIT64;

	/**
	 * @constant {Array<IOption>} keyLengthOptions CoreUI wep key length select options
	 */
	private keyLengthOptions: Array<IOption> = [
		{
			label: this.$t('network.wireless.form.wep.lengths.64bit'),
			value: WepKeyLen.BIT64,
		},
		{
			label: this.$t('network.wireless.form.wep.lengths.128bit'),
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
				return new RegExp(/^(\w{5}|[0-9a-fA-F]{10})$/).test(key);
			}
			return new RegExp(/^(\w{13}|[0-9a-fA-F]{26})$/).test(key);
		});
		extend('wepKeyType', (key: string) => {
			return key !== WepKeyType.UNKNOWN;
		});
	}

}
</script>
