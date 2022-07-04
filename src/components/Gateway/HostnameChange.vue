<template>
	<ValidationObserver v-slot='{invalid}'>
		<v-dialog
			v-model='render'
			width='50%'
		>
			<template #activator='{ on, attrs }'>
				<v-btn
					color='primary'
					small
					v-bind='attrs'
					v-on='on'
				>
					<v-icon small>
						mdi-pencil
					</v-icon>
					{{ $t('forms.edit') }}
				</v-btn>
			</template>
			<v-card>
				<v-card-title>{{ $t('gateway.hostname.title') }}</v-card-title>
				<v-card-text>
					<form @submit.prevent='save'>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='hostnamePattern|maxLen:64|required'
							:custom-messages='{
								hostnamePattern: $t("gateway.hostname.errors.hostnameInvalid"),
								maxLen: $t("gateway.hostname.errors.hostnameLen"),
								required: $t("gateway.hostname.errors.hostnameMissing"),
							}'
						>
							<v-text-field
								v-model='config.hostname'
								:label='$t("gateway.info.hostname")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
					</form>
				</v-card-text>
				<v-card-actions>
					<v-spacer />
					<v-btn color='primary' :disabled='invalid' @click='save'>
						{{ $t('forms.save') }}
					</v-btn>
					<v-btn color='error' @click='hide'>
						{{ $t('forms.cancel') }}
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
	</ValidationObserver>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {max, required} from 'vee-validate/dist/rules';

import GatewayService from '@/services/GatewayService';

import {IHostname} from '@/interfaces/gatewayInfo';
import {AxiosError} from 'axios';


@Component({
	components: {
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
