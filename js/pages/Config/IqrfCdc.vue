<template>
	<div>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{ invalid }'>
					<CForm @submit.prevent='saveConfig'>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "config.iqrfCdc.form.messages.instance"}'
						>
							<CInput
								v-model='configuration.instance'
								:label='$t("config.iqrfCdc.form.instance")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "config.iqrfCdc.form.messages.interface"}'
						>
							<CInput
								v-model='configuration.IqrfInterface'
								:label='$t("config.iqrfCdc.form.interface")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CButton type='submit' color='primary' :disabled='invalid'>
							{{ $t('forms.save') }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
		<CCard>
			<CCardHeader>{{ $t('config.iqrfCdc.mappings' ) }}</CCardHeader>
			<CCardBody>
				<InterfacePorts interface-type='cdc' @updatePort='updatePort' />
			</CCardBody>
		</CCard>
	</div>
</template>

<script>
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import InterfacePorts from '../../components/Config/InterfacePorts';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import ComponentConfigService from '../../services/ComponentConfigService';

export default {
	name: 'IqrfCdc',
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		InterfacePorts,
		ValidationObserver,
		ValidationProvider,
	},
	data() {
		return {
			componentName: 'iqrf::IqrfCdc',
			configuration: {
				instance: null,
				IqrfInterface: null,
			},
			instance: null,
		};
	},
	created() {
		extend('required', required);
		this.getConfig();
	},
	methods: {
		getConfig() {
			this.$store.commit('spinner/SHOW');
			ComponentConfigService.getConfig(this.componentName)
				.then((response) => {
					this.$store.commit('spinner/HIDE');
					if (response.data.instances.length > 0) {
						this.configuration = response.data.instances[0];
						this.instance = this.configuration.instance;
					}
				})
				.catch((error) => FormErrorHandler.configError(error));
		},
		saveConfig() {
			this.$store.commit('spinner/SHOW');
			if (this.instance !== null) {
				ComponentConfigService.saveConfig(this.componentName, this.instance, this.configuration)
					.then(() => this.successfulSave())
					.catch((error) => FormErrorHandler.configError(error));
			} else {
				ComponentConfigService.createConfig(this.componentName, this.configuration)
					.then(() => this.successfulSave())
					.catch((error) => FormErrorHandler.configError(error));
			}
		},
		successfulSave() {
			this.$store.commit('spinner/HIDE');
			this.$toast.success(this.$t('config.success').toString());
		},
		updatePort(port) {
			this.configuration.IqrfInterface = port;
		},
	},
	metaInfo: {
		title: 'config.iqrfCdc.title',
	},
};
</script>
