<template>
	<ValidationObserver v-slot='{invalid}'>
		<CModal
			size='lg'
			color='primary'
			:show='true'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('network.wireless.modal.modalTitle', {accessPoint: ap.ssid}) }}
				</h5>
			</template>
			<CForm>
				<div class='form-group'>
					<b>
						<span>{{ $t('network.wireless.modal.form.security') }}</span>
					</b> {{ ap.security }}
				</div>
				<div v-if='getSecurityType() === "ieee8021x"' class='form-group'>
					<CInput
						v-model='configLeap.username'
						:label='$t("network.wireless.modal.form.username")'
					/>
					<CInput
						v-model='configLeap.password'
						:label='$t("network.wireless.modal.form.password")'
					/>
				</div>
				<div v-if='getSecurityType() === "wep"' class='form-group'>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='required'
						:custom-messages='{
							required: "network.wireless.modal.errors.wepKeyType"
						}'
					>
						<CSelect
							:value.sync='configWep.type'
							:options='wepKeyOptions'
							:label='$t("network.wireless.modal.form.wep.type")'
							:placeholder='$t("network.wireless.modal.errors.wepKeyType")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CSelect
						v-if='configWep.type === wepKeyTypeEnum.KEY'
						:value.sync='wepLen'
						:options='wepLenOptions'
						:label='$t("network.wireless.modal.form.wep.length")'
					/>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='required|integer|between:1,4|wepIndex'
						:custom-messages='{
							required: "network.wireless.modal.errors.wepIndex",
							integer: "forms.errors.integer",
							between: "network.wireless.modal.errors.wepIndexInvalid",
							wepIndex: "network.wireless.modal.errors.wepIndexKeyMissing"
						}'
					>
						<CInput
							v-model.number='configWep.index'
							type='number'
							min='1'
							max='4'
							:label='$t("network.wireless.modal.form.wep.index")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-for='(key, index) of configWep.keys'
						:key='index'
						v-slot='{errors, touched, valid}'
						:rules='configWep.type === "key" ? "wepKey" : ""'
						:custom-messages='{
							wepKey: wepLen === "64bit" ?
								"network.wireless.modal.errors.wepKey64Invalid":
								"network.wireless.modal.errors.wepKey128Invalid"
						}'
					>
						<CInput							
							v-model='configWep.keys[index]'
							:label='$t("network.wireless.modal.form.wep.keyNum", {index: index+1})'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
				</div>
				<div v-if='getSecurityType() === "wpa-eap"' class='form-group'>
					<CSelect
						:value.sync='configEap.phaseOne'
						:options='authOneOptions'
						:label='$t("network.wireless.modal.form.authPhaseOne")'
					/>
					<CInput
						v-model='configEap.anonymousIdentity'
						:label='$t("network.wireless.modal.form.anonymousIdentity")'
					/>
					<CInput
						:label='$t("network.wireless.modal.form.caCert")'
						:disabled='configEap.noCert'
					/>
					<CInputCheckbox
						:checked.sync='configEap.noCert'
						:label='$t("network.wireless.modal.form.noCaCert")'
					/>
					<CSelect
						:value.sync='configEap.phaseTwo'
						:options='authTwoOptions'
						:label='$t("network.wireless.modal.form.authPhaseTwo")'
					/>
					<CInput
						v-model='configEap.username'
						:label='$t("network.wireless.modal.form.username")'
					/>
					<CInput
						v-model='configEap.password'
						:label='$t("network.wireless.modal.form.password")'
					/>
				</div>
				<ValidationProvider
					v-if='getSecurityType() === "wpa-psk"'
					v-slot='{errors, touched, valid}'
					rules='required|wpaPsk'
					:custom-messages='{
						required: "network.wireless.modal.errors.psk",
						wpaPsk: "network.wireless.modal.errors.pskInvalid"
					}'
				>
					<CInput
						v-model='psk'
						:label='$t("network.wireless.modal.form.psk")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='$t(errors[0])'
						:type='passwordVisibility'
					>
						<template #append-content>
							<span @click='passwordVisibility = passwordVisibility === "password" ? "visibility" : "password"'>
								<CIcon :content='passwordVisibility === "password" ? icons.hidden : icons.shown' />
							</span>
						</template>
					</CInput>
				</ValidationProvider>
			</CForm>
			<template #footer>
				<CButton
					color='secondary'
					@click='hideModal'
				>
					{{ $t('forms.cancel') }}
				</CButton> <CButton
					color='primary'
					:disabled='invalid'
					@click='createConnection'
				>
					{{ $t('network.table.connect') }}
				</CButton>
			</template>
		</CModal>
	</ValidationObserver>
</template>

<script lang='ts'>
import {Component, Vue, Prop} from 'vue-property-decorator';
import {CButton, CForm, CInput, CInputFile, CModal} from '@coreui/vue/src';
import {cilLockLocked, cilLockUnlocked} from '@coreui/icons';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required, between, integer} from 'vee-validate/dist/rules';

import {v4 as uuidv4} from 'uuid';
import NetworkConnectionService, {ConnectionType} from '../../services/NetworkConnectionService';

import {IAccessPoint, IWifiLeap} from '../../interfaces/network';
import {IOption} from '../../interfaces/coreui';
import {Dictionary} from 'vue-router/types/router';
import {AxiosResponse} from 'axios';

enum WepKeyLen {
	BIT64 = '64bit',
	BIT128 = '128bit',
}

enum WepKeyType {
	KEY = 'key',
	PASSPHRASE = 'passphrase',
	UNKNOWN = 'unknown',
}

@Component({
	components: {
		CButton,
		CForm,
		CInput,
		CInputFile,
		CModal,
		ValidationObserver,
		ValidationProvider,
	}
})

/**
 * Wifi access point connection form
 */
export default class WifiForm extends Vue {

	/**
	 * @var {IWifiLeap} configLeap Wifi LEAP security configuration
	 */
	private configLeap: IWifiLeap = {
		username: '',
		password: '',
	}

	/**
	 * @var {IWifiWep} configWep Wifi WEP security configuration
	 */
	private configWep = {
		type: WepKeyType.KEY,
		index: 0,
		keys: [
			'', '', '', ''
		]
	}

	private configEap = {
		phaseOne: 'peap',
		anonymousIdentity: '',
		cert: '',
		noCert: false,
		phaseTwo: 'mschapv2',
		username: '',
		password: ''
	}

	/**
	 * @constant {Dictionary<Array<string>>} icons Array of CoreUI icons
	 */
	private icons: Dictionary<Array<string>> = {
		hidden: cilLockLocked,
		shown: cilLockUnlocked
	}

	/**
	 * @var {string} psk Access point pre-shared key for WPA
	 */
	private psk = ''

	/**
	 * @var {string} passwordVisibility Access point password field visibility
	 */
	private passwordVisibility = 'password'

	/**
	 * @var {WepKeyLen} wepLen WEP key length
	 */
	private wepLen = WepKeyLen.BIT64

	/**
	 * @constant {Array<IOption>} authOneOptions CoreUI EAP phase one authentication options
	 */
	private authOneOptions: Array<IOption> = [
		{
			label: this.$t('network.wireless.modal.form.phaseOneAlgorithm.peap'),
			value: 'peap'
		},
	]

	/**
	 * @constant {Array<IOption>} authTwoOptions CoreUI EAP phase two authentication options
	 */
	private authTwoOptions: Array<IOption> = [
		{
			label: this.$t('network.wireless.modal.form.phaseTwoAlgorithm.mschapv2'),
			value: 'mschapv2'
		},
	]

	/**
	 * @constant {enum} wepKeyTypeEnum WEP key type enum
	 */
	private wepKeyTypeEnum = WepKeyType

	/**
	 * @constant {Array<IOption>} wepKeyOptions CoreUI wep key type select options
	 */
	private wepKeyOptions: Array<IOption> = [
		{
			label: this.$t('network.wireless.modal.form.wep.types.key'),
			value: 'key'
		},
		{
			label: this.$t('network.wireless.modal.form.wep.types.passphrase'),
			value: 'passphrase'
		},
	]

	/**
	 * @constant {Array<IOption>} wepLenOptions CoreUI wep key length select options
	 */
	private wepLenOptions: Array<IOption> = [
		{
			label: this.$t('network.wireless.modal.form.wep.lengths.64bit'),
			value: WepKeyLen.BIT64,
		},
		{
			label: this.$t('network.wireless.modal.form.wep.lengths.128bit'),
			value: WepKeyLen.BIT128,
		},
	]

	/**
	 * @property {IAccessPoint} ap Wifi access point
	 */
	@Prop({required: true}) ap!: IAccessPoint

	/**
	 * @property {string} ifname Interface name
	 */
	@Prop({required: true}) ifname!: string

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		extend('wepIndex', (index: number) => {
			return this.configWep.keys[index] !== '';
		});
		extend('wepKey', (key: string) => {
			if (this.wepLen === WepKeyLen.BIT64) {
				return new RegExp(/^(\w{5}|[0-9a-fA-F]{10})$/).test(key);
			}
			return new RegExp(/^(\w{13}|[0-9a-fA-F]{26})$/).test(key);
		});
		extend('wpaPsk', (key: string) => {
			return new RegExp(/^(\w{8,63}|[0-9a-fA-F]{64})$/).test(key);
		});
	}

	/**
	 * Emits event to parent to hide and reset modal
	 */
	private hideModal(): void {
		this.$emit('hide-modal');
	}

	/**
	 * Returns security type code from type string
	 * @returns {string} security type code
	 */
	private getSecurityType(): string {
		const type = this.ap.security;
		if (type === 'Open') {
			return 'open';
		} else if (type === 'WEP') {
			return 'wep';
		} else if (['WPA-Personal', 'WPA2-Personal', 'WPA3-Personal'].includes(type)) {
			return 'wpa-psk';
		} else if (['WPA-Enterprise', 'WPA2-Enterprise', 'WPA3-Enterprise'].includes(type)) {
			return 'wpa-eap';
		}
		return '';
	}

	private createConnection(): void {
		let connectionData = {
			name: this.ap.ssid,
			uuid: uuidv4(),
			type: ConnectionType.WIFI,
			interface: this.ifname,
			autoConnect: {
				enabled: true,
				priority: 0,
				retries: -1
			},
			ipv4: {
				method: 'auto',
				addresses: [],
				gateway: null,
				dns: [],
			},
			ipv6: {
				method: 'auto',
				addresses: [],
				dns: [],
				gateway: null,
			},
			wifi: {
				ssid: this.ap.ssid,
				mode: this.ap.mode,
				security: {
					type: this.getSecurityType(),
					psk: this.psk,
					leap: this.configLeap,
					wep: this.configWep,
				}
			}
		};
		if (this.getSecurityType() !== 'wep') {
			connectionData.wifi.security.wep.type = WepKeyType.UNKNOWN;
		} 
		NetworkConnectionService.add(connectionData)
			.then((response: AxiosResponse) => {
				this.ap.uuid = response.data;
				this.hideModal();
				this.$emit('connection-created', this.ap);
			})
			.catch(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t('network.connection.messages.createFailed', {connection: this.ap.ssid}).toString()
				);
			});
	}
}
</script>
