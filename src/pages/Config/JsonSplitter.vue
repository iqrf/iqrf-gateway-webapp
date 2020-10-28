<template>
	<div>
		<h1>{{ $t('config.jsonSplitter.title') }}</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{ invalid }'>
					<CForm @submit.prevent='saveConfig'>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "config.jsonSplitter.form.messages.instance"}'
						>
							<CInput
								v-model='configuration.instance'
								:label='$t("config.jsonSplitter.form.instance")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CInputCheckbox
							:checked.sync='configuration.validateJsonResponse'
							:label='$t("config.jsonSplitter.form.validateJsonResponse")'
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
import {required} from 'vee-validate/dist/rules';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';

interface JsonSplitterConfig {
	instance: string|null
	validateJsonResponse: boolean
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
		title: 'config.jsonSplitter.title',
	},
})

/**
 * JSON Splitter component configuration
 */
export default class JsonSplitter extends Vue {
	/**
	 * @constant {string} componentName JSON Splitter component name
	 */
	private componentName = 'iqrf::JsonSplitter'

	/**
	 * @var {string|null} instance JSON Splitter component instance name
	 */
	private instance: string|null = null

	/**
	 * @var {JsonSplitterConfig} configuration JSON Splitter component instance configuration
	 */
	private configuration: JsonSplitterConfig = {
		instance: null,
		validateJsonResponse: false,
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('required', required);
		this.getConfig();
	}
	
	/**
	 * Retrieves configuration of JSON splitter component
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
	 * Saves new or updates existing configuration of JSON Splitter component instance
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
