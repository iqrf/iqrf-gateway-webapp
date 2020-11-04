<template>
	<CCard>
		<CCardHeader>
			<h3 v-if='$route.path === "/config/websocket/add"'>
				{{ $t('config.websocket.interface.add') }}
			</h3>
			<h3 v-else>
				{{ $t('config.websocket.interface.edit') }}
			</h3>
		</CCardHeader>
		<CCardBody>
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
						:checked.sync='messaging.acceptAsyncMsg'
						:label='$t("config.websocket.form.acceptAsyncMsg")'
					/>
					<CInputCheckbox
						:checked.sync='service.acceptOnlyLocalhost'
						:label='$t("config.websocket.form.acceptOnlyLocalhost")'
					/>
					<div v-if='versionNew'>
						<CInputCheckbox
							:checked.sync='service.tlsEnabled'
							:label='$t("config.websocket.form.tlsEnabled")'
						/>
						<CSelect
							:value.sync='service.tlsMode'
							:label='$t("config.websocket.form.tlsMode")'
							:options='tlsModeOptions'
							:placeholder='$t("config.websocket.form.messages.tlsMode")'
							:disabled='!service.tlsEnabled'
						/>
						<span v-if='service.tlsMode !== "" && service.tlsMode !== undefined'>{{ $t('config.websocket.form.tlsModes.descriptions.' + service.tlsMode) }}</span>
					</div><br v-if='versionNew'>
					<CButton type='submit' color='primary' :disabled='invalid'>
						{{ submitButton }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput, CInputCheckbox, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import {integer, required} from 'vee-validate/dist/rules';
import {WsMessaging, ModalInstance, IWsService} from '../../interfaces/messagingInterfaces';
import {MetaInfo} from 'vue-meta';
import {AxiosError, AxiosResponse} from 'axios';
import {IOption} from '../../interfaces/coreui';
import compareVersions from 'compare-versions';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		CInputCheckbox,
		CSelect,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo(): MetaInfo {
		return {
			title: (this as unknown as WebsocketInterfaceForm).pageTitle
		};
	}
})

/**
 * Daemon WebSocket messaging and service configuration form (normal user)
 */
export default class WebsocketInterfaceForm extends Vue {
	/**
	 * @constant {ModalInstance} componentNames Names of websocket messaging and service components
	 */
	private componentNames: ModalInstance = {
		messaging: 'iqrf::WebsocketMessaging',
		service: 'shape::WebsocketCppService',
	}

	/**
	 * @var {ModalInstance} instances Names of websocket messaging and service instances
	 */
	private instances: ModalInstance = {
		messaging: '',
		service: '',
	}

	/**
	 * @var {WsMessging} messaging WebSocket messaging component instance
	 */
	private messaging: WsMessaging = {
		component: '',
		instance: '',
		acceptAsyncMsg: false,
		RequiredInterfaces: [],
	}

	/**
	 * @var {WsService} service WebSocket service component instance
	 */
	private service: IWsService = {
		component: '',
		instance: '',
		WebsocketPort: 1338,
		acceptOnlyLocalhost: false,
		tlsEnabled: false,
		tlsMode: '',
		certificate: '',
		privateKey: ''
	}

	/**
	 * @constant {Array<IOption>} tlsModeOptions Array of CoreUI select options
	 */
	private tlsModeOptions: Array<IOption> = [
		{
			value: 'intermediate',
			label: this.$t('config.websocket.form.tlsModes.intermediate').toString()
		},
		{
			value: 'modern',
			label: this.$t('config.websocket.form.tlsModes.modern').toString()
		},
		{
			value: 'old',
			label: this.$t('config.websocket.form.tlsModes.old').toString()
		}
	]

	/**
	 * @property {string} instance WebSocket interface instance name
	 */
	@Prop({required: false, default: ''}) instance!: string

	/**
	 * Computes whether version of IQRF Gateway Daemon is high enough to support new properties
	 * @returns {boolean} true if version >= 2.3.0, false otherwise
	 */
	get versionNew(): boolean {
		const daemonVersion = this.$store.getters.daemonVersion;
		if (daemonVersion === '') {
			return false;
		}
		if (compareVersions.compare(daemonVersion, '2.3.0', '>=')) {
			return true;
		}
		return false;
	}

	/**
	 * Computes page title depending on the action (add, edit)
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$route.path === '/config/daemon/websocket/add' ?
			this.$t('config.websocket.interface.add').toString() : this.$t('config.websocket.interface.edit').toString();
	}
	
	/**
	 * Computes the text of form submit button depending on the action (add, edit)
	 * @returns {string} Button text
	 */
	get submitButton(): string {
		return this.$route.path === '/config/daemon/websocket/add' ?
			this.$t('forms.add').toString() : this.$t('forms.save').toString();
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('integer', integer);
		extend('required', required);
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		if (this.instance !== '') {
			this.getConfig();
		}
	}

	/**
	 * Retrieves configuration of the WebSocket messaging and service instances
	 */
	private getConfig(): void {
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.getInstance(this.componentNames.messaging, this.instance)
			.then((response: AxiosResponse) => {
				this.messaging = response.data;
				this.instances.messaging = this.instance;
				this.instances.service = this.messaging.RequiredInterfaces[0].target.instance;
				DaemonConfigurationService.getInstance(this.componentNames.service, this.instances.service)
					.then((response: AxiosResponse) => {
						this.service = response.data;
						this.$store.commit('spinner/HIDE');
					});
			})
			.catch((error: AxiosError) => {
				this.$router.push('/config/daemon/messagings/');
				FormErrorHandler.configError(error);
			});
	}
	
	/**
	 * Saves new or updates existing configuration of WebSocket messaging and service component instances
	 */
	private saveConfig(): void {
		if (!this.versionNew) {
			delete this.service.tlsEnabled;
			delete this.service.tlsMode;
			delete this.service.certificate;
			delete this.service.privateKey;
		}
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
		if (this.instance === '') {
			Promise.all([
				DaemonConfigurationService.createInstance(this.componentNames.service, this.service),
				DaemonConfigurationService.createInstance(this.componentNames.messaging, this.messaging),
			])
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => FormErrorHandler.configError(error));
		} else {
			Promise.all([
				DaemonConfigurationService.updateInstance(this.componentNames.service, this.instances.service, this.service),
				DaemonConfigurationService.updateInstance(this.componentNames.messaging, this.instances.messaging, this.messaging),
			])
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => FormErrorHandler.configError(error));
		}
	}

	/**
	 * Handles successful REST API response
	 */
	private successfulSave(): void {
		this.$store.commit('spinner/HIDE');
		if (this.$route.path === '/config/daemon/websocket/add') {
			this.$toast.success(
				this.$t('config.websocket.messages.add.success', {instance: this.messaging.instance})
					.toString()
			);
		} else {
			this.$toast.success(
				this.$t('config.websocket.messages.edit.success', {instance: this.messaging.instance})
					.toString()
			);
		}
		this.$router.push('/config/daemon/messagings/');
	}
}
</script>
