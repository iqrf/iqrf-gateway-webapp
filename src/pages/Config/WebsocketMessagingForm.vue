<template>
	<div>
		<h1 v-if='$route.path === "/config/websocket/add-messaging"'>
			{{ $t('config.websocket.messaging.add') }}
		</h1>
		<h1 v-else>
			{{ $t('config.websocket.messaging.edit') }}
		</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{ invalid }'>
					<CForm @submit.prevent='saveInstance'>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "config.websocket.form.messages.messagingInstance"}'
						>
							<CInput
								v-model='configuration.instance'
								:label='$t("config.websocket.form.instance")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CInputCheckbox
							:checked.sync='configuration.acceptAsyncMsg'
							:label='$t("config.websocket.form.acceptAsyncMsg")'
						/>
						<h4>{{ $t('config.websocket.form.requiredInterfaces') }}</h4>
						<div
							v-for='(iface, i) of configuration.RequiredInterfaces'
							:key='i'
							class='form-group'
						>
							<ValidationProvider
								v-slot='{ errors, touched, valid }'
								rules='required'
								:custom-messages='{required: "config.websocket.form.messages.interfaceName"}'
							>
								<CSelect
									:value.sync='iface.name'
									:label='$t("config.websocket.form.requiredInterface.name")'
									:placeholder='$t("config.websocket.form.messages.interfaceName")'
									:options='[
										{value: "shape::IWebsocketService", label: "shape::IWebsocketService"}
									]'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{ errors, touched, valid }'
								rules='required'
								:custom-messages='{required: "config.websocket.form.messages.interfaceName"}'
							>
								<CSelect
									:value.sync='iface.instance'
									:label='$t("config.websocket.form.requiredInterface.instance")'
									:placeholder='$t("config.websocket.form.messages.interfaceInstance")'
									:options='instances.service'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<CButton
								v-if='configuration.RequiredInterfaces.length > 1'
								color='danger'
								@click='removeInterface(i-1)'
							>
								{{ $t('config.websocket.form.requiredInterface.remove') }}
							</CButton>
							<CButton
								v-if='i === (configuration.RequiredInterfaces.length - 1)'
								color='success'
								:disabled='!iface.name || !iface.instance'
								@click='addInterface'
							>
								{{ $t('config.websocket.form.requiredInterface.add') }}
							</CButton>
						</div>
						<CButton type='submit' color='primary' :disabled='invalid'>
							{{ submitButton }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
	</div>
</template>

<script>
import {CButton, CCard, CCardBody, CForm, CInput, CInputCheckbox, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import {required} from 'vee-validate/dist/rules';

export default {
	name: 'WebsocketMessagingForm',
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		CInputCheckbox,
		CSelect,
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
				service: [],
			},
			configuration: {
				instance: null,
				acceptAsyncMsg: false,
				RequiredInterfaces: [{}],
			}
		};
	},
	computed: {
		submitButton() {
			return this.$route.path === '/config/websocket/add-messaging' ?
				this.$t('forms.add') : this.$t('forms.edit');
		},
	},
	created() {
		extend('required', required);
		if (this.instance) {
			this.getConfig();
		} else {
			this.getServices();
		}
	},
	methods: {
		addInterface() {
			this.configuration.RequiredInterfaces.push({});
		},
		removeInterface(index) {
			this.configuration.RequiredInterfaces.splice(index, 1);
		},
		getConfig() {
			this.$store.commit('spinner/SHOW');
			return Promise.all([
				DaemonConfigurationService.getInstance(this.componentNames.messaging, this.instance),
				DaemonConfigurationService.getComponent(this.componentNames.service),
			])
				.then((responses) => {
					this.$store.commit('spinner/HIDE');
					let interfaces = [];
					let configuration = responses[0].data;
					configuration.RequiredInterfaces.forEach(item => {
						interfaces.push({name: item.name, instance: item.target.instance});
					});
					configuration.RequiredInterfaces = interfaces;
					this.configuration = configuration;
					responses[1].data.instances.forEach(item => {
						this.instances.service.push({value: item.instance, label: item.instance});
					});
				});
		},
		getServices() {
			this.$store.commit('spinner/SHOW');
			DaemonConfigurationService.getComponent(this.componentNames.service)
				.then((response) => {
					this.$store.commit('spinner/HIDE');
					response.data.instances.forEach(item => {
						this.instances.service.push({value: item.instance, label: item.instance});
					});
				});
		},
		saveInstance() {
			let instance = this.configuration;
			let RequiredInterfaces = [];
			this.configuration.RequiredInterfaces.forEach(item => {
				RequiredInterfaces.push({name: item.name, target: {instance: item.instance}});
			});
			instance.RequiredInterfaces = RequiredInterfaces;
			this.$store.commit('spinner/SHOW');
			if (this.instance !== null) {
				DaemonConfigurationService.updateInstance(this.componentNames.messaging, this.instance, instance)
					.then(() => this.successfulSave())
					.catch((error) => FormErrorHandler.configError(error));
			} else {
				DaemonConfigurationService.createInstance(this.componentNames.messaging, instance)
					.then(() => this.successfulSave())
					.catch((error) => FormErrorHandler.configError(error));
			}
		},
		successfulSave() {
			this.$router.push('/config/websocket/');
			this.$store.commit('spinner/HIDE');
			if (this.$route.path === '/config/websocket/add-messaging') {
				this.$toast.success(
					this.$t('config.websocket.messaging.messages.addSuccess', {messaging: this.configuration.instance})
						.toString()
				);
			} else {
				this.$toast.success(
					this.$t('config.websocket.messaging.messages.editSuccess', {messaging: this.instance})
						.toString()
				);
			}
		},
	},
	metaInfo() {
		return {
			title: this.$route.path === '/config/websocket/add-messaging' ?
				this.$t('config.websocket.messaging.add') :
				this.$t('config.websocket.messaging.edit')
		};
	}
};
</script>
