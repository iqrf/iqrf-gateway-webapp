<template>
	<CCard>
		<CCardHeader>{{ $t("config.iqmesh.otaUpload.title") }}</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm @submit.prevent='saveInstance'>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{required: "config.iqmesh.otaUpload.form.messages.instance"}'
					>					
						<CInput
							v-model='configuration.instance'
							:label='$t("config.iqmesh.otaUpload.form.instance")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{required: "config.iqmesh.otaUpload.form.messages.uploadPath"}'
					>	
						<CInput
							v-model='configuration.uploadPath'
							:label='$t("config.iqmesh.otaUpload.form.uploadPath")'
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

<script>
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';

export default {
	name: 'OtaUpload',
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		ValidationObserver,
		ValidationProvider,
	},
	data() {
		return {
			componentName: 'iqrf::OtaUploadService',
			configuration: {
				instance: null,
				uploadPath: null,
				uploadPathSuffix: null,
			},
			instance: null,
		};
	},
	created() {
		extend('required', required);
		this.getInstance();
	},
	methods: {
		getInstance() {
			this.$store.commit('spinner/SHOW');
			DaemonConfigurationService.getComponent(this.componentName)
				.then((response) => {
					this.$store.commit('spinner/HIDE');
					if (response.data.instances.length > 0) {
						this.configuration = response.data.instances[0];
						this.instance = this.configuration.instance;
					}
				})
				.catch((error) => FormErrorHandler.configError(error));
		},
		saveInstance() {
			this.$store.commit('spinner/SHOW');
			if (this.instance !== null) {
				DaemonConfigurationService.updateInstance(this.componentName, this.instance, this.configuration)
					.then(() => this.successfulSave())
					.catch((error) => FormErrorHandler.configError(error));
			} else {
				DaemonConfigurationService.createInstance(this.componentName, this.configuration)
					.then(() => this.successfulSave())
					.catch((error) => FormErrorHandler.configError(error));
			}
		},
		successfulSave() {
			this.$store.commit('spinner/HIDE');
			this.$toast.success(this.$t('config.success').toString());
		},
	},
	metaInfo: {
		title: 'config.iqmesh.otaUpload.title'
	}
};
</script>
