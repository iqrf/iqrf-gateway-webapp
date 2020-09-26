<template>
	<CCard>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm @submit.prevent='saveInstance'>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{required: "config.websocket.form.messages.serviceInstance"}'
					>
						<CInput
							v-model='configuration.instance'
							:label='$t("config.websocket.form.instance")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='integer|required'
						:custom-messages='{
							required: "config.websocket.form.messages.WebsocketPort",
							integer: "forms.messages.integer"
						}'
					>
						<CInput
							v-model.number='configuration.WebsocketPort'
							type='number'
							:label='$t("config.websocket.form.WebsocketPort")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CInputCheckbox
						:checked.sync='configuration.acceptOnlyLocalhost'
						:label='$t("config.websocket.form.acceptOnlyLocalhost")'
					/>
					<CButton type='submit' color='primary' :disabled='invalid'>
						{{ submitButton }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script>
import {CButton, CCard, CCardBody, CForm, CInput, CInputCheckbox} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {integer, required} from 'vee-validate/dist/rules';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';

export default {
	name: 'WebsocketServiceForm',
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
	props: {
		instance: {
			type: String,
			required: false,
			default: null,
		},
	},
	data() {
		return {
			componentName: 'shape::WebsocketCppService',
			configuration: {
				instance: null,
				WebsocketPort: null,
				acceptOnlyLocalhost: false,
			},
		};
	},
	computed: {
		submitButton() {
			return this.$route.path === '/config/websocket/add-service' ? this.$t('forms.add') : this.$t('forms.edit');
		},
	},
	created() {
		extend('integer', integer);
		extend('required', required);
		if (this.instance) {
			this.getInstance();
		}
	},
	methods: {
		getInstance() {
			this.$store.commit('spinner/SHOW');
			DaemonConfigurationService.getInstance(this.componentName, this.instance)
				.then((response) => {
					this.$store.commit('spinner/HIDE');
					this.configuration = response.data;
				})
				.catch((error) => {
					this.$router.push('/config/websocket/');
					FormErrorHandler.configError(error);
				});
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
			this.$router.push('/config/websocket/');
			this.$store.commit('spinner/HIDE');
			if (this.$route.path === '/config/websocket/add-service') {
				this.$toast.success(this.$t('config.websocket.service.messages.addSuccess', {service: this.configuration.instance}).toString());
			} else {
				this.$toast.success(this.$t('config.websocket.service.messages.editSuccess', {service: this.instance}).toString());
			}
		},
	},
	metaInfo() {
		return {
			title: this.$route.path === '/config/websocket/add-service' ? this.$t('config.websocket.service.add') : this.$t('config.websocket.service.edit')
		};
	},
};
</script>
