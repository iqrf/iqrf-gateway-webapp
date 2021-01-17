<template>
	<ValidationObserver v-slot='{invalid}'>
		<CModal
			size='lg'
			color='primary'
			:show='true'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('network.wireless.modal.title', {accessPoint: ap.ssid}) }}
				</h5>
			</template>
			<CForm>
				<div class='form-group'>
					<b>
						<span>{{ $t('network.wireless.modal.form.security') }}</span>
					</b> {{ ap.security }}
				</div>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='required'
					:custom-messages='{
						required: "network.wireless.modal.errors.password"
					}'
				>
					<CInput
						v-model='password'
						:label='$t("network.wireless.modal.form.password")'
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
import {CButton, CForm, CInput, CModal} from '@coreui/vue/src';
import {cilLockLocked, cilLockUnlocked} from '@coreui/icons';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import {v4 as uuidv4} from 'uuid';

import {IAccessPoint} from '../../interfaces/network';
import {Dictionary} from 'vue-router/types/router';
import {ConnectionType} from '../../services/NetworkConnectionService';

@Component({
	components: {
		CButton,
		CForm,
		CInput,
		CModal,
		ValidationObserver,
		ValidationProvider,
	}
})

/**
 * Wifi access point connection form
 */
export default class WifiForm extends Vue {

	private configLeap = {
		username: '',
		password: '',
	}

	private configWep = {
		type: 'unknown',
		index: 0,
		keys: [
			'', '', '', ''
		]
	}

	/**
	 * @constant {Dictionary<Array<string>>} icons Array of CoreUI icons
	 */
	private icons: Dictionary<Array<string>> = {
		hidden: cilLockLocked,
		shown: cilLockUnlocked
	}

	/**
	 * @var {string} password Access point password
	 */
	private password = ''

	/**
	 * @var {string} passwordVisibility Access point password field visibility
	 */
	private passwordVisibility = 'password'

	/**
	 * @property {IAccessPoint} ap Wifi access point
	 */
	@Prop({required: true}) ap!: IAccessPoint

	/**
	 * @property {string} ifname Interface name
	 */
	@Prop({required: true}) ifname!: string

	created(): void {
		extend('required', required);
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
		if (['WPA-Personal', 'WPA2-Personal', 'WPA3-Personal'].includes(type)) {
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
				dns: []
			},
			ipv6: {
				method: 'auto',
				addresses: [],
				dns: []
			},
			wifi: {
				ssid: this.ap.ssid,
				mode: this.ap.mode,
				security: {
					type: this.getSecurityType(),
					psk: this.password,
					leap: this.configLeap,
					wep: this.configWep,
				}
			}
		}; //TODO component object for wifi security
		this.$emit('create-connection', connectionData);
	}
}
</script>
