<template>
	<ValidationObserver v-slot='{invalid}'>
		<CModal
			:show.sync='render'
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
						hostnamePattern: $t("gateway.hostname.errors.hostnameInvalid"),
						maxLen: $t("gateway.hostname.errors.hostnameLen"),
						required: $t("gateway.hostname.errors.hostnameMissing"),
					}'
				>
					<CInput
						v-model='config.hostname'
						:label='$t("gateway.info.hostname")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
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
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CForm, CInput, CModal} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {max, required} from 'vee-validate/dist/rules';

import GatewayService from '@/services/GatewayService';

import {IHostname} from '@/interfaces/gatewayInfo';
import {AxiosError} from 'axios';


@Component({
	components: {
		CButton,
		CForm,
		CInput,
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
	private render = false;

	/**
	 * @var {IHostname} config Hostnamectl configuration
	 */
	private config: IHostname = {
		hostname: ''
	};

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
		this.render = true;
	}

	/**
	 * Hides modal component
	 */
	public hide(): void {
		this.render = false;
		this.config.hostname = '';
	}
}
</script>
