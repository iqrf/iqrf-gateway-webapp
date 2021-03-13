<template>
	<CCard class='border-0 card-margin-bottom'>
		<CCardHeader>
			{{ $t('config.daemon.misc.jsonRawApi.title') }}
		</CCardHeader>
		<CCardBody>
			<CElementCover v-if='loadFailed'>
				{{ $t('config.daemon.messages.failedElement') }}
			</CElementCover>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='saveConfig'>
					<fieldset :disabled='loadFailed'>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{required: "config.daemon.misc.jsonRawApi.errors.instance"}'
						>
							<CInput
								v-model='configuration.instance'
								:label='$t("forms.fields.instanceName")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CInputCheckbox
							:checked.sync='configuration.asyncDpaMessage'
							:label='$t("config.daemon.misc.jsonRawApi.form.asyncDpaMessage")'
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
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';

import {AxiosError, AxiosResponse} from 'axios';
import {IJsonRaw} from '../../interfaces/jsonApi';

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
 * JSON RawApi component configuration
 */
export default class JsonRawApi extends Vue {
	/**
	 * @constant {string} componentName JSON RawApi component name
	 */
	private componentName = 'iqrf::JsonDpaApiRaw'

	/**
	 * @var {string} instances JSON RawApi component instance name
	 */
	private instance = ''

	/**
	 * @var {IJsonRaw} configuration JSON RawApi component instance configuration
	 */
	private configuration: IJsonRaw = {
		instance: '',
		asyncDpaMessage: false,
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
	 * Retrieves configuration of JSON RawApi component
	 */
	private getConfig(): Promise<void> {
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				if (response.data.instances.length > 0) {
					this.configuration = response.data.instances[0];
					this.instance = this.configuration.instance;
				}
				this.$emit('fetched', {name: 'jsonRawApi', success: true});
			})
			.catch(() => {
				this.loadFailed = true;
				this.$emit('fetched', {name: 'jsonRawApi', success: false});
			});
	}

	/**
	 * Saves new or updates existing configuration of JSON RawApi component instance
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
