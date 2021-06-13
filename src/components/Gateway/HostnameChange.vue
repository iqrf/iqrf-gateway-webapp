<template>
	<ValidationObserver v-slot='{invalid}'>
		<CModal
			:show.sync='renderModal'
			color='primary'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('gateway.hostname.title') }}
				</h5>
			</template>
			<CForm @submit.prevent='save'>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='hostnamePattern|maxLen:64|required'
					:custom-messages='{
						hostnamePattern: "gateway.hostname.errors.hostnameInvalid",
						maxLen: "gateway.hostname.errors.hostnameLen",
						required: "gateway.hostname.errors.hostnameMissing"
					}'
				>
					<CFormInput
						v-model='config.hostname'
						:label='$t("gateway.info.hostname")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='$t(errors[0])'
					/>
				</ValidationProvider>
			</CForm>
			<template #footer>
				<CButton
					color='primary'
					:disabled='invalid'
					@click='save'
				>
					{{ $t('forms.save') }}
				</CButton> <CButton
					color='secondary'
					@click='hide'
				>
					{{ $t('forms.cancel') }}
				</CButton>
			</template>
		</CModal>
	</ValidationObserver>
</template>

<script lang='ts'>
import {Options, Vue} from 'vue-property-decorator';
import {CButton, CForm, CFormInput, CModal} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '../../helpers/errorToast';
import {max, required} from 'vee-validate/dist/rules';

import GatewayService from '../../services/GatewayService';

import {IHostname} from '../../interfaces/gatewayInfo';
import {AxiosError} from 'axios';


@Options({
	components: {
		CButton,
		CForm,
		CFormInput,
		CModal,
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * Hostname changer component
 */
export default class HostnameChange extends Vue {

	/**
	 * @var {boolean} render Controls whether or not modal is rendered
	 */
	private renderModal = false

	/**
	 * @var {IHostname} config Hostnamectl configuration
	 */
	private config: IHostname = {
		hostname: ''
	}

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('hostnamePattern', (hostname: string) => {
			const re = new RegExp('^[0-9a-zA-Z][0-9a-zA-Z\\-]{0,63}$');
			return re.test(hostname);
		});
		extend('maxLen', max);
		extend('required', required);
	}

	/**
	 * Sets new hostname configuration
	 */
	private save(): void {
		this.$store.commit('spinner/SHOW');
		GatewayService.setHostname(this.config)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('gateway.hostname.messages.success').toString()
				);
				this.hide();
				this.$emit('hostname-changed');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'gateway.hostname.messages.failed');
			});
	}

	/**
	 * Shows modal component
	 */
	public show(): void {
		this.renderModal = true;
	}

	/**
	 * Hides modal component
	 */
	public hide(): void {
		this.renderModal = false;
		this.config.hostname = '';
	}
}
</script>
