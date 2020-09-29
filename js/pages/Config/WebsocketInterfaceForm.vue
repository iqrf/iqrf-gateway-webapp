<template>
	<CCard body-wrapper>
		<ValidationObserver v-slot='{ invalid }'>
			<CForm @submit.prevent='saveConfig'>
				<ValidationProvider
					v-slot='{ errors, touched, valid }'
					rules='required'
					:custom-messages='{required: "config.mq.form.messages.instance"}'
				>
					<CInput
						v-model='messaging.instance'
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
						v-model.number='service.WebsocketPort'
						type='number'
						:label='$t("config.websocket.form.WebsocketPort")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='$t(errors[0])'
					/>
				</ValidationProvider>
				<CInputCheckbox
					:checked.sync='service.acceptOnlyLocalhost'
					:label='$t("config.websocket.form.acceptOnlyLocalhost")'
				/>
				<CButton type='submit' color='primary' :disabled='invalid'>
					{{ submitButton }}
				</CButton>
			</CForm>
		</ValidationObserver>
	</CCard>
</template>

<script>
import {CButton, CCard, CForm, CInput, CInputCheckbox} from '@coreui/vue';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import {integer, required} from 'vee-validate/dist/rules';

export default {
	name: 'MonitorForm',
	components: {
		CButton,
		CCard,
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
			componentNames: {
				messaging: 'iqrf::WebsocketMessaging',
				service: 'shape::WebsocketCppService',
			},
			instances: {
				messaging: null,
				service: null,
			},
			messaging: {
				instance: null,
				acceptAsyncMsg: false,
				RequiredInterfaces: [],
			},
			service: {
				instance: null,
				WebsocketPort: null,
				acceptOnlyLocalhost: false,
			},
		};
	},
	computed: {
		submitButton() {
			return this.$route.path === '/config/mq/add' ?
				this.$t('forms.add') :
				this.$t('forms.save');
		},
	},
	created() {
		extend('integer', integer);
		extend('required', required);
		if (this.instance !== null) {
			this.getConfig();
		}
	},
	methods: {
		getConfig() {
			this.$store.commit('spinner/SHOW');
			DaemonConfigurationService.getInstance(this.componentNames.messaging, this.instance)
				.then((response) => {
					this.messaging = response.data;
					this.instances.messaging = this.instance;
					this.instances.service = this.messaging.RequiredInterfaces[0].target.instance;
					DaemonConfigurationService.getInstance(this.componentNames.service, this.instances.service)
						.then((response) => {
							this.service = response.data;
							this.$store.commit('spinner/HIDE');
						});
				})
				.catch((error) => {
					this.$router.push('/config/websocket/');
					FormErrorHandler.configError(error);
				});
		},
		saveConfig() {
			this.$store.commit('spinner/SHOW');
			this.service.instance = this.messaging.instance;
			if (this.messaging.RequiredInterfaces.length === 0) {
				this.messaging.RequiredInterfaces[0] = {
					name: 'shape::IWebsocketService',
					target: {
						instance: this.service.instance,
					},
				};
			} else {
				this.messaging.RequiredInterfaces[0].target.instance = this.messaging.instance;
			}
			if (this.instance === null) {
				Promise.all([
					DaemonConfigurationService.createInstance(this.componentNames.service, this.service),
					DaemonConfigurationService.createInstance(this.componentNames.messaging, this.messaging),
				])
					.then(() => this.successfulSave())
					.catch((error) => FormErrorHandler.configError(error));
			} else {
				Promise.all([
					DaemonConfigurationService.updateInstance(this.componentNames.service, this.instances.service, this.service),
					DaemonConfigurationService.updateInstance(this.componentNames.messaging, this.instances.messaging, this.messaging),
				])
					.then(() => this.successfulSave())
					.catch((error) => FormErrorHandler.configError(error));
			}
		},
		successfulSave() {
			this.$router.push('/config/websocket/');
			this.$store.commit('spinner/HIDE');
			if (this.$route.path === '/config/websocket/add') {
				this.$toast.success(this.$t('config.websocket.messages.add.success', {instance: this.messaging.instance}).toString());
			} else {
				this.$toast.success(this.$t('config.websocket.messages.edit.success', {instance: this.messaging.instance}).toString());
			}
		},
	},
	metaInfo() {
		return {
			title: this.$route.path === '/config/websocket/add' ?
				'config.websocket.interface.add' :
				'config.websocket.interface.edit',
		};
	},
};
</script>
