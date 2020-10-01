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
	</CCard>
</template>

<script>
import {CButton, CCard, CForm, CInput, CInputCheckbox} from '@coreui/vue';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import {required} from 'vee-validate/dist/rules';

export default {
	name: 'MqMessagingForm',
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
			componentName: 'iqrf::MqMessaging',
			configuration: {
				instance: null,
				LocalMqName: null,
				RemoteMqName: null,
				acceptAsyncMsg: false,
			}
		};
	},
	computed: {
		submitButton() {
			return this.$route.path === '/config/mq/add' ?
				this.$t('forms.add') : this.$t('forms.save');
		},
	},
	created() {
		extend('required', required);
		if (this.instance !== null) {
			this.getConfig();
		}
	},
	methods: {
		getConfig() {
			this.$store.commit('spinner/SHOW');
			DaemonConfigurationService.getInstance(this.componentName, this.instance)
				.then((response) => {
					this.$store.commit('spinner/HIDE');
					this.configuration = response.data;
					this.instance = this.configuration.instance;
				})
				.catch((error) => {
					this.$router.push('/config/mq/');
					FormErrorHandler.configError(error);
				});
		},
		saveConfig() {
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
			this.$router.push('/config/mq/');
			this.$store.commit('spinner/HIDE');
			if (this.$route.path === '/config/mq/add') {
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
		},
	},
	metaInfo() {
		return {
			title: this.$route.path === '/config/mq/add' ?
				'config.mq.add' : 'config.mq.edit',
		};
	},
};
</script>
