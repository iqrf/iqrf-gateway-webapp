<template>
	<div>
		<h1>{{ $t('config.iqrfInfo.title') }}</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{ invalid }'>
					<CForm @submit.prevent='saveConfig'>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "config.iqrfInfo.messages.instance"}'
						>
							<CInput
								v-model='configuration.instance'
								:label='$t("config.iqrfInfo.form.instance")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CInputCheckbox
							:checked.sync='configuration.enumAtStartUp'
							:label='$t("config.iqrfInfo.form.enumAtStartUp")'
						/>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							:rules='configuration.enumAtStartUp ? "integer|min:0|required": ""'
							:custom-messages='{
								required: "config.iqrfInfo.messages.enumPeriod",
								min: "config.iqrfInfo.messages.enumPeriod",
								integer: "forms.messages.integer"
							}'
						>
							<CInput
								v-model.number='configuration.enumPeriod'
								type='number'
								min='0'
								:label='$t("config.iqrfInfo.form.enumPeriod")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CInputCheckbox
							:checked.sync='configuration.enumUniformDpaVer'
							:label='$t("config.iqrfInfo.form.enumUniformDpaVer")'
						/>
						<CButton type='submit' color='primary' :disabled='invalid'>
							{{ $t('forms.save') }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {AxiosError, AxiosResponse} from 'axios';
import {CButton, CCard, CCardBody, CForm, CInput, CInputCheckbox} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';

interface IqrfInfoConfig {
	instance: string
	enumAtStartUp: boolean
	enumPeriod: number
	enumUniformDpaVer: boolean
}

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		CInputCheckbox,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo: {
		title: 'config.iqrfInfo.title',
	},
})

/**
 * IQRF Info component configuration
 */
export default class IqrfInfo extends Vue {
	/**
	 * @constant {string} componentName IQRF Info component name
	 */
	private componentName = 'iqrf::IqrfInfo'

	/**
	 * @var {string|null} instance IQRF Info component instance name
	 */
	private instance: string|null = null

	/**
	 * @var {IqfInfoConfig} configuration IQRF Info component instance configuration
	 */
	private configuration: IqrfInfoConfig = {
		instance: '',
		enumAtStartUp: false,
		enumPeriod: 0,
		enumUniformDpaVer: false,
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
		this.getConfig();
	}

	/**
	 * Retrieves configuration of IQRF Info component
	 */
	private getConfig(): void {
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
	 * Saves new or updates existing configuration of IQRF Info component instance
	 */
	private saveConfig(): void {
		this.$store.commit('spinner/SHOW');
		if (this.instance !== null) {
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
