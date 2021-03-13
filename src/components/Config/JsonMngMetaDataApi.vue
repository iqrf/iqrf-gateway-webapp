<template>
	<CCard class='border-0 card-margin-bottom'>
		<CCardHeader>
			{{ $t('config.daemon.misc.jsonMngMetaDataApi.title') }}
		</CCardHeader>
		<CCardBody>
			<CElementCover v-if='loadFailed'>
				{{ $t('config.daemon.misc.messages.failedElement') }}
			</CElementCover>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='saveConfig'>
					<fieldset :disable='loadFailed'>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{required: "config.daemon.misc.jsonMngMetaDataApi.errors.instance"}'
						>
							<CInput
								v-model='configuration.instance'
								:label='$t("forms.fields.instanceName")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CInputCheckbox
							:checked.sync='configuration.metaDataToMessages'
							:label='$t("config.daemon.misc.jsonMngMetaDataApi.form.metaDataToMessages")'
						/>
						<CButton type='submit' color='primary' :disabled='invalid'>
							{{ $t('forms.save') }}
						</CButton>
					</fieldset>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CElementCover, CForm, CInput, CInputCheckbox} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {required} from 'vee-validate/dist/rules';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';

interface JsonMngMetaDataApiConfig {
	instance: string
	metaDataToMessages: boolean
}

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CElementCover,
		CForm,
		CInput,
		CInputCheckbox,
		ValidationObserver,
		ValidationProvider
	}
})

/**
 * JSON MetaData component configuration
 */
export default class JsonMngMetaDataApi extends Vue {
	/**
	 * @constant {string} componentName JSON MetaData component name
	 */
	private componentName = 'iqrf::JsonMngMetaDataApi'

	/**
	 * @var {string} instance JSON MetaData component instance name
	 */
	private instance = ''

	/**
	 * @var {JsonMngMetaDataApiConfig} configuration JSON MetaData component instance configuration
	 */
	private configuration: JsonMngMetaDataApiConfig = {
		instance: '',
		metaDataToMessages: false,
	}

	/**
	 * @var {boolean} loadFailed Indicates whether configuration fetch failed
	 */
	private loadFailed = false

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves configuration of JSON MetaData component
	 */
	private getConfig(): Promise<void> {
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				if (response.data.instances.length > 0) {
					this.configuration = response.data.instances[0];
					this.instance = this.configuration.instance;
				}
				this.$emit('fetched', {name: 'jsonMngMetaDataApi', success: true});
			})
			.catch(() => {
				this.loadFailed = true;
				this.$emit('fetched', {name: 'jsonMngMetaDataApi', success: false});
			});
	}
	
	/**
	 * Saves new or updates existing configuration of JSON MetaData configuration instance
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
		this.getConfig().then(() => this.$toast.success(this.$t('config.success').toString()));
	}
}
</script>
