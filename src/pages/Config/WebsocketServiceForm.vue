<template>
	<div>
		<h1 v-if='$route.path === "/config/websocket/add-service"'>
			{{ $t('config.websocket.service.add') }}
		</h1>
		<h1 v-else>
			{{ $t('config.websocket.service.edit') }}
		</h1>
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
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CForm, CInput, CInputCheckbox} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {integer, required} from 'vee-validate/dist/rules';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import { WsService } from '../../interfaces/messagingInterfaces';
import { AxiosError, AxiosResponse } from 'axios';
import { MetaInfo } from 'vue-meta';

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
	metaInfo(): MetaInfo {
		return {
			title: (this as unknown as WebsocketServiceForm).pageTitle
		};
	}
})

/**
 * Daemon WebSocket service component configuration form
 */
export default class WebsocketServiceForm extends Vue {
	/**
	 * @constant {string} componentName Name of WebSocket service component
	 */
	private componentName = 'shape::WebsocketCppService'

	/**
	 * @var {WsService} configuration WebSocket service component instance configuration
	 */
	private configuration: WsService = {
		component: '',
		instance: '',
		WebsocketPort: 1338,
		acceptOnlyLocalhost: false,
	}

	/**
	 * @property {string} instance WebSocket service component instance name
	 */
	@Prop({required: false, default: ''}) instance!: string

	/**
	 * Computes page title depending on the action (add, edit)
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$route.path === '/config/websocket/add-service' ?
			this.$t('config.websocket.service.add').toString() : this.$t('config.websocket.service.edit').toString();
	}

	/**
	 * Computes the text of form submit button depending on the action (add, edit)
	 * @returns {string} Button text
	 */
	get submitButton(): string {
		return this.$route.path === '/config/websocket/add-service' ?
			this.$t('forms.add').toString() : this.$t('forms.edit').toString();
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('integer', integer);
		extend('required', required);
		if (this.instance) {
			this.getInstance();
		}
	}

	/**
	 * Retrieves instance of WebSocket service component
	 */
	private getInstance(): void {
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.getInstance(this.componentName, this.instance)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.configuration = response.data;
			})
			.catch((error: AxiosError) => {
				this.$router.push('/config/websocket/');
				FormErrorHandler.configError(error);
			});
	}

	/**
	 * Saves new or updates existing configuration of WebSocket service component instance
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
	 * Handles successful REST API response
	 */
	private successfulSave(): void {
		this.$store.commit('spinner/HIDE');
		if (this.$route.path === '/config/websocket/add-service') {
			this.$toast.success(
				this.$t('config.websocket.service.messages.addSuccess', {service: this.configuration.instance})
					.toString()
			);
		} else {
			this.$toast.success(
				this.$t('config.websocket.service.messages.editSuccess', {service: this.instance})
					.toString()
			);
		}
		this.$router.push('/config/websocket/');
	}
}
</script>
