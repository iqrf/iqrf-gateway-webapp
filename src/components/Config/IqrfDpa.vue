<template>
	<CCard>
		<CCardHeader>
			{{ $t('config.daemon.interfaces.iqrfDpa.title') }}
		</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm @submit.prevent='saveConfig'>
					<ValidationProvider
						v-if='powerUser'
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{required: "config.daemon.interfaces.iqrfDpa.errors.instance"}'
					>
						<CInput
							v-model='configuration.instance'
							:label='$t("config.daemon.interfaces.iqrfDpa.form.instance")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='integer|required|min:0'
						:custom-messages='{
							integer: "forms.messages.integer",
							min: "config.daemon.interfaces.iqrfDpa.errors.DpaHandlerTimeout",
							required: "config.daemon.interfaces.iqrfDpa.errors.DpaHandlerTimeout"
						}'
					>
						<CInput
							v-model.number='configuration.DpaHandlerTimeout'
							type='number'
							min='0'
							:label='$t("config.daemon.interfaces.iqrfDpa.form.DpaHandlerTimeout")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CButton type='submit' color='primary' :disabled='invalid'>
						{{ $t('forms.save') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {AxiosError, AxiosResponse} from 'axios';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import {IIqrfDpa} from '../../interfaces/iqrfInterfaces';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		ValidationObserver,
		ValidationProvider
	}
})

/**
 * IQRF DPA component configuration
 */
export default class IqrfDpa extends Vue {
	/**
	 * @constant {string} componentName IQRF DPA component name
	 */
	private componentName = 'iqrf::IqrfDpa'

	/**
	 * @var {IIqrfDpa} configuration IQRF DPA component instance configuration
	 */
	private configuration: IIqrfDpa = {
		component: '',
		instance: '',
		DpaHandlerTimeout: 500,
	}

	/**
	 * @var {string} instance IQRF DPA component instance name
	 */
	private instance = ''

	/**
	 * @var {boolean} powerUser Indicates whether user role is power user
	 */
	private powerUser = false

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		if (this.$store.getters['user/getRole'] === 'power') {
			this.powerUser = true;
		}
		this.getConfig();
	}

	/**
	 * Retrieves configuration of IQRF DPA component
	 */
	private getConfig() {
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				if (response.data.instances.length > 0) {
					this.configuration = response.data.instances[0];
					this.instance = this.configuration.instance;
				}
			})
			.catch((error: AxiosError) => FormErrorHandler.configError(error));
	}
	
	/**
	 * Saves new or updates existing configuration of IQRF DPA component instance
	 */
	private saveConfig(): void {
		this.$store.commit('spinner/SHOW');
		if (this.instance !== '') {
			DaemonConfigurationService.updateInstance(this.componentName, this.instance, this.configuration)
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => FormErrorHandler.configError(error));
		} else {
			DaemonConfigurationService.createInstance(this.componentName, this.configuration)
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => FormErrorHandler.configError(error));
		}
	}

	/**
	 * Handles successful REST API response
	 */
	private successfulSave(): void {
		this.$store.commit('spinner/HIDE');
		this.$toast.success(this.$t('config.success').toString());
	}
}
</script>
