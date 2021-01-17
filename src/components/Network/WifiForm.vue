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

import {IAccessPoint} from '../../interfaces/network';
import {Dictionary} from 'vue-router/types/router';

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

	created(): void {
		extend('required', required);
	}

	private hideModal(): void {
		this.$emit('hide-modal');
	}

	private createConnection(): void {
		let connectionData = {
			ssid: this.ap.ssid,
			psk: this.password,
		};
		this.$emit('create-connection');
	}

}
</script>
