<template>
	<CCard class='border-0 card-margin-bottom'>
		<CCardHeader>
			{{ $t("config.daemon.misc.otaUpload.title") }}
		</CCardHeader>
		<CCardBody>
			<CElementCover v-if='loadFailed'>
				{{ $t('config.daemon.misc.messages.failedElement') }}
			</CElementCover>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='saveInstance'>
					<fieldset :disabled='loadFailed'>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: "config.daemon.misc.otaUpload.errors.instance"
							}'
						>					
							<CInput
								v-model='configuration.instance'
								:label='$t("forms.fields.instanceName")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CInput
							v-model='configuration.uploadPath'
							:label='$t("config.daemon.misc.otaUpload.form.uploadPath")'
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
import {CButton, CCard, CCardBody, CCardHeader, CElementCover, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {required} from 'vee-validate/dist/rules';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';

import {AxiosError, AxiosResponse} from 'axios';
import {IOtaUpload} from '../../interfaces/iqmeshServices';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CElementCover,
		CForm,
		CInput,
		ValidationObserver,
		ValidationProvider,
	}
})

/**
 * OtaUpload card for IqmeshServices component
 */
export default class OtaUpload extends Vue {

	/**
	 * @constant {string} componentName name of daemon component
	 */
	private componentName = 'iqrf::OtaUploadService'

	/**
	 * @var {string} instance name of daemon componenent instance
	 */
	private instance = ''

	/**
	 * @var {IOtaUpload} configuration OtaUpload instance configuration
	 */
	private configuration: IOtaUpload = {
		component: '',
		instance: '',
		uploadPath: '',
		uploadPathSuffix: '',
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
		this.getInstance();
	}

	/**
	 * Retrieves instance of OtaUpload daemon component
	 */
	private getInstance(): Promise<void> {
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				if (response.data.instances.length > 0) {
					this.configuration = response.data.instances[0];
					this.instance = this.configuration.instance;
				}
				this.$emit('fetched', {name: 'otaUpload', success: true});
			})
			.catch(() => {
				this.loadFailed = true;
				this.$emit('fetched', {name: 'otaUpload', success: false});
			});
	}

	/**
	 * Updates configuration of OtaUpload instance and creates one if it does not exist
	 */
	private saveInstance(): void {
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
	 * Handles REST API success
	 */
	private successfulSave(): void {
		this.getInstance().then(() => this.$toast.success(this.$t('config.success').toString()));
	}

}
</script>
