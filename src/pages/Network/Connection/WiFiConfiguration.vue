<template>
	<CRow>
		<CCol md='6'>
			<legend>{{ $t('network.wireless.form.title') }}</legend>
			<div class='form-group'>
				<strong>
					<span>{{ $t('network.wireless.form.security') }}</span>
				</strong> {{ $t(`network.wireless.form.securityTypes.${connection.wifi.security.type}`) }}
			</div>
			<div
				v-if='connection.wifi.security.type === "ieee8021x"'
				class='form-group'
			>
				<CInput
					v-model='connection.wifi.security.leap.username'
					:label='$t("network.wireless.form.username").toString()'
				/>
				<CInput
					v-model='connection.wifi.security.leap.password'
					:label='$t("network.wireless.form.password").toString()'
				/>
			</div>
			<div
				v-else-if='connection.wifi.security.type === "wep"'
				class='form-group'
			>
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
						:options='wepKeyOptions'
						:label='$t("network.wireless.form.wep.type").toString()'
						:placeholder='$t("network.wireless.errors.wepKeyType").toString()'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					/>
				</ValidationProvider>
				<CSelect
					v-if='connection.wifi.security.wep.type === WepKeyType.KEY'
					:value.sync='wepLen'
					:options='wepLenOptions'
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
						wepKey: (wepLen === WepKeyLen.BIT64 ?
							$t("network.wireless.errors.wepKey64Invalid") :
							$t("network.wireless.errors.wepKey128Invalid")),
					}'
				>
					<CInput
						v-model='connection.wifi.security.wep.keys[index]'
						:label='$t("network.wireless.form.wep.keyNum", {index: index}).toString()'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					/>
				</ValidationProvider>
			</div>
			<ValidationProvider
				v-else-if='connection.wifi.security.type === "wpa-psk"'
				v-slot='{errors, touched, valid}'
				rules='required|wpaPsk'
				:custom-messages='{
					required: $t("network.wireless.errors.psk"),
					wpaPsk: $t("network.wireless.errors.pskInvalid"),
				}'
			>
				<CInput
					v-model='connection.wifi.security.psk'
					:type='pskVisible ? "text" : "password"'
					:label='$t("network.wireless.form.psk").toString()'
					:is-valid='touched ? valid : null'
					:invalid-feedback='errors.join(", ")'
				>
					<template #append-content>
						<span @click='pskVisible = !pskVisible'>
							<FontAwesomeIcon
								:icon='(pskVisible ? ["far", "eye-slash"] : ["far", "eye"])'
							/>
						</span>
					</template>
				</CInput>
			</ValidationProvider>
			<div
				v-if='connection.wifi.security.type === "wpa-eap"'
				class='form-group'
			>
				<CSelect
					:value.sync='connection.wifi.security.eap.phaseOneMethod'
					:options='authOneOptions'
					:label='$t("network.wireless.form.authPhaseOne").toString()'
				/>
				<CInput
					v-model='connection.wifi.security.eap.anonymousIdentity'
					:label='$t("network.wireless.form.anonymousIdentity").toString()'
				/>
				<CInput
					v-model='connection.wifi.security.eap.cert'
					:label='$t("network.wireless.form.caCert").toString()'
				/>
				<CSelect
					:value.sync='connection.wifi.security.eap.phaseTwoMethod'
					:options='authTwoOptions'
					:label='$t("network.wireless.form.authPhaseTwo").toString()'
				/>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='required'
					:custom-messages='{
						required: $t("forms.errors.username"),
					}'
				>
					<CInput
						v-model='connection.wifi.security.eap.identity'
						:label='$t("network.wireless.form.username").toString()'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='required'
					:custom-messages='{
						required: $t("forms.errors.password"),
					}'
				>
					<CInput
						v-model='connection.wifi.security.eap.password'
						:label='$t("network.wireless.form.password").toString()'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					/>
				</ValidationProvider>
			</div>
		</CCol>
	</CRow>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';
import {CCol, CInput, CRow, CSelect} from '@coreui/vue/src';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import {extend, ValidationProvider} from 'vee-validate';
import {between, integer, required} from 'vee-validate/dist/rules';

import {WepKeyLen, WepKeyType} from '@/enums/Network/WifiSecurity';

import {IOption} from '@/interfaces/Coreui';
import {IConnection} from '@/interfaces/Network/Connection';

/**
 * Wi-Fi configuration options
 */
@Component({
	components: {
		CCol,
		CInput,
		CRow,
		CSelect,
		FontAwesomeIcon,
		ValidationProvider,
	},
	data: () => ({
		WepKeyLen,
		WepKeyType,
	}),
})
export default class WiFiConfiguration extends Vue {

	/**
	 * Edited connection.
	 */
	@VModel({required: true}) connection!: IConnection;

	/**
	 * @constant {Array<IOption>} authOneOptions CoreUI EAP phase one authentication options
	 */
	private authOneOptions: Array<IOption> = [
		{
			label: this.$t('network.wireless.form.phaseOneAlgorithm.peap'),
			value: 'peap'
		},
	];

	/**
	 * @constant {Array<IOption>} authTwoOptions CoreUI EAP phase two authentication options
	 */
	private authTwoOptions: Array<IOption> = [
		{
			label: this.$t('network.wireless.form.phaseTwoAlgorithm.mschapv2'),
			value: 'mschapv2'
		},
	];

	/**
	 * @constant {Array<IOption>} wepKeyOptions CoreUI wep key type select options
	 */
	private wepKeyOptions: Array<IOption> = [
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
	 * @var {WepKeyLen} wepLen WEP key length
	 */
	private wepLen = WepKeyLen.BIT64;

	/**
	 * @constant {Array<IOption>} wepLenOptions CoreUI wep key length select options
	 */
	private wepLenOptions: Array<IOption> = [
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
	 * @var {boolean} pskVisible Controls visibility of PSK field
	 */
	private pskVisible = false;

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
			if (this.wepLen === WepKeyLen.BIT64) {
				return new RegExp(/^(\w{5}|[0-9a-fA-F]{10})$/).test(key);
			}
			return new RegExp(/^(\w{13}|[0-9a-fA-F]{26})$/).test(key);
		});
		extend('wepKeyType', (key: string) => {
			return key !== WepKeyType.UNKNOWN;
		});
		extend('wpaPsk', (key: string) => {
			return new RegExp(/^([\u0020-\u007e\u0080-\u00ff]{8,63}|[\da-fA-F]{64})$/).test(key);
		});
	}


}
</script>
