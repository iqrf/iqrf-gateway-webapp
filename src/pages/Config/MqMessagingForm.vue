<template>
	<div>
		<h1 v-if='$route.path === "/config/daemon/messagings/mq/add"'>
			{{ $t('config.daemon.messagings.mq.add') }}
		</h1>
		<h1 v-else>
			{{ $t('config.daemon.messagings.mq.edit') }}
		</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{ invalid }'>
					<CForm @submit.prevent='saveConfig'>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "config.daemon.messagings.mq.errors.instance"}'
						>
							<CInput
								v-model='configuration.instance'
								:label='$t("forms.fields.instanceName")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "config.daemon.messagings.mq.errors.LocalMqName"}'
						>
							<CInput
								v-model='configuration.LocalMqName'
								:label='$t("config.daemon.messagings.mq.form.LocalMqName")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "config.daemon.messagings.mq.errors.RemoteMqName"}'
						>
							<CInput
								v-model='configuration.RemoteMqName'
								:label='$t("config.daemon.messagings.mq.form.RemoteMqName")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CInputCheckbox
							:checked.sync='configuration.acceptAsyncMsg'
							:label='$t("config.daemon.messagings.acceptAsyncMsg")'
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
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput, CInputCheckbox} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import {required} from 'vee-validate/dist/rules';
import { MqInstance } from '../../interfaces/messagingInterfaces';
import { MetaInfo } from 'vue-meta';
import {AxiosError, AxiosResponse} from 'axios';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		CInputCheckbox,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo(): MetaInfo {
		return {
			title: (this as unknown as MqMessagingForm).pageTitle
		};
	},
})

/**
 * Daemon MQ messaging component configuration form
 */
export default class MqMessagingForm extends Vue {
	/**
	 * @constant {string} componentName MQ messaging component name
	 */
	private componentName = 'iqrf::MqMessaging'

	/**
	 * @var {MqInstance} configuration MQ messaging component instance configuration
	 */
	private configuration: MqInstance = {
		component: '',
		instance: '',
		LocalMqName: '',
		RemoteMqName: '',
		acceptAsyncMsg: false,
	}

	/**
	 * @property {string} instance MQ messaging component instance name
	 */
	@Prop({required: false, default: ''}) instance!: string

	/**
	 * Computes page title depending on the action (add, edit)
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$route.path === '/config/daemon/messagings/mq/add' ?
			this.$t('config.daemon.messagings.mq.add').toString() : this.$t('config.daemon.messagings.mq.edit').toString();
	}
	
	/**
	 * Computes the text of form submit button depending on the action (add, edit)
	 * @returns {string} Button text
	 */
	get submitButton(): string {
		return this.$route.path === '/config/daemon/messagings/mq/add' ?
			this.$t('forms.add').toString() : this.$t('forms.edit').toString();
	}

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
		if (this.instance !== '') {
			this.getConfig();
		}
	}

	/**
	 * Retrieves configuration of the MQ messaging component instance
	 */
	private getConfig(): void  {
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.getInstance(this.componentName, this.instance)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.configuration = response.data;
			})
			.catch((error: AxiosError) => {
				this.$router.push('/config/daemon/messagings/mq');
				FormErrorHandler.configError(error);
			});
	}

	/**
	 * Saves new or updates existing configuration of MQ messaging component instance
	 */
	private saveConfig(): void  {
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
	private successfulSave(): void  {
		this.$store.commit('spinner/HIDE');
		if (this.$route.path === '/config/daemon/messagings/mq/add') {
			this.$toast.success(
				this.$t('config.daemon.messagings.mq.messages.addSuccess', {instance: this.configuration.instance})
					.toString()
			);
		} else {
			this.$toast.success(
				this.$t('config.daemon.messagings.mq.messages.editSuccess', {instance: this.configuration.instance})
					.toString()
			);
		}
		this.$router.push('/config/daemon/messagings/mq/');
	}
}
</script>
