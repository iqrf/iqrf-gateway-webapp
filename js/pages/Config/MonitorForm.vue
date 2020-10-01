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
						v-model='monitor.instance'
						:label='$t("config.monitor.form.instance")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='$t(errors[0])'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{ errors, touched, valid }'
					rules='integer|required'
					:custom-messages='{
						required: "config.monitor.form.messages.reportPeriod",
						integer: "forms.messages.integer"
					}'
				>
					<CInput
						v-model.number='monitor.reportPeriod'
						type='number'
						:label='$t("config.monitor.form.reportPeriod")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='$t(errors[0])'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{ errors, touched, valid }'
					rules='integer|required'
					:custom-messages='{
						required: "config.monitor.form.messages.WebsocketPort",
						integer: "forms.messages.integer"
					}'
				>
					<CInput
						v-model.number='webSocket.WebsocketPort'
						type='number'
						:label='$t("config.monitor.form.WebsocketPort")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='$t(errors[0])'
					/>
				</ValidationProvider>
				<CInputCheckbox
					:checked.sync='webSocket.acceptOnlyLocalhost'
					:label='$t("config.monitor.form.acceptOnlyLocalhost")'
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
				monitor: 'iqrf::MonitorService',
				webSocket: 'shape::WebsocketCppService',
			},
			instances: {
				monitor: null,
				webSocket: null,
			},
			monitor: {
				instance: null,
				reportPeriod: null,
				RequiredInterfaces: []
			},
			webSocket: {
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
			DaemonConfigurationService.getInstance(this.componentNames.monitor, this.instance)
				.then((response) => {
					this.monitor = response.data;
					this.instances.monitor = this.instance;
					this.instances.webSocket = this.monitor.RequiredInterfaces[0].target.instance;
					DaemonConfigurationService.getInstance(this.componentNames.webSocket, this.instances.webSocket)
						.then((response) => {
							this.webSocket = response.data;
							this.$store.commit('spinner/HIDE');
						});
				})
				.catch((error) => {
					this.$router.push('/config/monitor/');
					FormErrorHandler.configError(error);
				});
		},
		saveConfig() {
			this.$store.commit('spinner/SHOW');
			this.webSocket.instance = this.monitor.instance;
			if (this.monitor.RequiredInterfaces.length === 0) {
				this.monitor.RequiredInterfaces[0] = {
					name: 'shape::IWebsocketService',
					target: {
						instance: this.webSocket.instance,
					},
				};
			} else {
				this.monitor.RequiredInterfaces[0].target.instance = this.monitor.instance;
			}
			if (this.instance === null) {
				Promise.all([
					DaemonConfigurationService.createInstance(this.componentNames.webSocket, this.webSocket),
					DaemonConfigurationService.createInstance(this.componentNames.monitor, this.monitor),
				])
					.then(() => this.successfulSave())
					.catch((error) => FormErrorHandler.configError(error));
			} else {
				Promise.all([
					DaemonConfigurationService.updateInstance(this.componentNames.webSocket, this.instances.webSocket, this.webSocket),
					DaemonConfigurationService.updateInstance(this.componentNames.monitor, this.instances.monitor, this.monitor),
				])
					.then(() => this.successfulSave())
					.catch((error) => FormErrorHandler.configError(error));
			}
		},
		successfulSave() {
			this.$router.push('/config/monitor/');
			this.$store.commit('spinner/HIDE');
			if (this.$route.path === '/config/monitor/add') {
				this.$toast.success(
					this.$t('config.monitor.messages.add.success', {instance: this.monitor.instance})
						.toString()
				);
			} else {
				this.$toast.success(
					this.$t('config.monitor.messages.edit.success', {instance: this.monitor.instance})
						.toString()
				);
			}
		},
	},
	metaInfo() {
		return {
			title: this.$route.path === '/config/monitor/add' ?
				'config.monitor.add' :
				'config.monitor.edit',
		};
	},
};
</script>
