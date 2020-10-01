<template>
	<CCard body-wrapper>
		<ValidationObserver v-slot='{ invalid }'>
			<CForm @submit.prevent='saveConfig'>
				<ValidationProvider
					v-slot='{ errors, touched, valid }'
					rules='required'
					:custom-messages='{required: "config.udp.form.messages.instance"}'
				>
					<CInput
						v-model='configuration.instance'
						:label='$t("config.udp.form.instance")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='$t(errors[0])'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{ errors, touched, valid }'
					rules='required|between:1,65535'
					:custom-messages='{
						between: "config.udp.form.messages.RemotePort",
						required: "config.udp.form.messages.RemotePort",
					}'
				>
					<CInput
						v-model.number='configuration.RemotePort'
						:label='$t("config.udp.form.RemotePort")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='$t(errors[0])'
						type='number'
						min='1'
						max='65535'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{ errors, touched, valid }'
					rules='required|between:1,65535'
					:custom-messages='{
						between: "config.udp.form.messages.LocalPort",
						required: "config.udp.form.messages.LocalPort",
					}'
				>
					<CInput
						v-model.number='configuration.LocalPort'
						:label='$t("config.udp.form.LocalPort")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='$t(errors[0])'
						type='number'
						min='1'
						max='65535'
					/>
				</ValidationProvider>
				<CButton type='submit' color='primary' :disabled='invalid'>
					{{ submitButton }}
				</CButton>
			</CForm>
		</ValidationObserver>
	</CCard>
</template>

<script>
import {CButton, CCard, CForm, CInput} from '@coreui/vue';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import {between, required} from 'vee-validate/dist/rules';

export default {
	name: 'UdpMessagingForm',
	components: {
		CButton,
		CCard,
		CForm,
		CInput,
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
			componentName: 'iqrf::UdpMessaging',
			configuration: {
				instance: null,
				RemotePort: null,
				LocalPort: null,
			}
		};
	},
	computed: {
		submitButton() {
			return this.$route.path === '/config/udp/add' ?
				this.$t('forms.add') : this.$t('forms.save');
		},
	},
	created() {
		extend('between', between);
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
					this.$router.push('/config/udp/');
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
			this.$router.push('/config/udp/');
			this.$store.commit('spinner/HIDE');
			if (this.$route.path === '/config/udp/add') {
				this.$toast.success(
					this.$t('config.udp.messages.add.success', {instance: this.configuration.instance})
						.toString());
			} else {
				this.$toast.success(
					this.$t('config.udp.messages.edit.success', {instance: this.configuration.instance})
						.toString()
				);
			}
		},
	},
	metaInfo() {
		return {
			title: this.$route.path === '/config/udp/add' ?
				'config.udp.add' : 'config.udp.edit',
		};
	},
};
</script>
