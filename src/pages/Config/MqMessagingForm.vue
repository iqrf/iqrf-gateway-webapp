<template>
	<CCard>
		<CCardHeader>
			<h3 v-if='$route.path === "/config/daemon/mq/add"'>
				{{ $t('config.mq.add') }}
			</h3>
			<h3 v-else>
				{{ $t('config.mq.edit') }}
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
							v-model='configuration.instance'
							:label='$t("config.mq.form.instance")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{required: "config.mq.form.messages.LocalMqName"}'
					>
						<CInput
							v-model='configuration.LocalMqName'
							:label='$t("config.mq.form.LocalMqName")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{required: "config.mq.form.messages.RemoteMqName"}'
					>
						<CInput
							v-model='configuration.RemoteMqName'
							:label='$t("config.mq.form.RemoteMqName")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CInputCheckbox
						:checked.sync='configuration.acceptAsyncMsg'
						:label='$t("config.mq.form.acceptAsyncMsg")'
					/>
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
		return this.$route.path === '/config/daemon/mq/add' ?
			this.$t('config.mq.add').toString() : this.$t('config.mq.edit').toString();
	}
	
	/**
	 * Computes the text of form submit button depending on the action (add, edit)
	 * @returns {string} Button text
	 */
	get submitButton(): string {
		return this.$route.path === '/config/daemon/mq/add' ?
			this.$t('forms.add').toString() : this.$t('forms.save').toString();
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
				this.$router.push('/config/daemon/messagings/');
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
		if (this.$route.path === '/config/daemon/mq/add') {
			this.$toast.success(
				this.$t('config.mq.messages.add.success', {instance: this.configuration.instance})
					.toString()
			);
		} else {
			this.$toast.success(
				this.$t('config.mq.messages.edit.success', {instance: this.configuration.instance})
					.toString()
			);
		}
		this.$router.push('/config/daemon/messagings/');
	}
}
</script>
