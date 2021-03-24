<template>
	<div>
		<CCard>
			<CCardHeader>
				{{ $t('config.daemon.interfaces.iqrfCdc.title') }}
			</CCardHeader>
			<CCardBody>
				<CElementCover 
					v-if='loadFailed'
					style='z-index: 1;'
					:opacity='0.85'
				>
					{{ $t('config.daemon.messages.failedElement') }}
				</CElementCover>
				<ValidationObserver v-slot='{ invalid }'>
					<CForm @submit.prevent='saveConfig'>
						<fieldset :disabled='loadFailed'>
							<ValidationProvider
								v-if='powerUser'
								v-slot='{ errors, touched, valid }'
								rules='required'
								:custom-messages='{required: "config.daemon.interfaces.iqrfCdc.errors.instance"}'
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
								:custom-messages='{required: "config.daemon.interfaces.iqrfCdc.errors.iqrfInterface"}'
							>
								<CInput
									v-model='configuration.IqrfInterface'
									:label='$t("config.daemon.interfaces.iqrfCdc.form.interface")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<CButton type='submit' color='primary' :disabled='invalid'>
								{{ $t('forms.save') }}
							</CButton>
						</fieldset>
					</CForm>
				</ValidationObserver>
			</CCardBody>
			<CCardFooter>
				<h4>{{ $t('config.daemon.interfaces.iqrfCdc.mappings' ) }}</h4><hr>
				<InterfacePorts interface-type='cdc' @update-port='updatePort' />
			</CCardFooter>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {AxiosError, AxiosResponse} from 'axios';
import {CButton, CCard, CCardBody, CCardHeader, CElementCover, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import InterfacePorts from '../../components/Config/InterfacePorts.vue';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import {IIqrfCdc} from '../../interfaces/iqrfInterfaces';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CElementCover,
		CForm,
		CInput,
		InterfacePorts,
		ValidationObserver,
		ValidationProvider,
	}
})

/**
 * IQRF CDC communication interface configuration component
 */
export default class IqrfCdc extends Vue {
	/**
	 * @constant {string} componentName IQRF CDC interface component name
	 */
	private componentName = 'iqrf::IqrfCdc'

	/**
	 * @var {IIqrfCdc} configuration IQRF CDC interface instance configuration
	 */
	private configuration: IIqrfCdc = {
		component: '',
		instance: '',
		IqrfInterface: '',
	}

	/**
	 * @var {string} instance Name of IQRF CDC component instance
	 */
	private instance = ''

	/**
	 * @var {boolean} powerUser Indicates whether user role is power user
	 */
	private powerUser = false

	/**
	 * @var {boolean} loadFailed Indicates whether configuration fetch failed
	 */
	private loadFailed = false;

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
		if (this.$store.getters['user/getRole'] === 'power') {
			this.powerUser = true;
		}
		this.getConfig();
	}

	/**
	 * Retrieves configuration of IQRF CDC interface component
	 */
	private getConfig(): Promise<void> {
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				if (response.data.instances.length > 0) {
					this.configuration = response.data.instances[0];
					this.instance = this.configuration.instance;
				}
				this.$emit('fetched', {name: 'iqrfCdc', success: true});
			})
			.catch(() => {
				this.loadFailed = true;
				this.$emit('fetched', {name: 'iqrfCdc', success: false});
			});
	}

	/**
	 * Saves new or updates existing configuration of IQRF CDC interface component instance
	 */
	private saveConfig(): void {
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
		this.getConfig().then(() => this.$toast.success(this.$t('config.success').toString()));
	}
	
	/**
	 * Updates port in configuration from mapping
	 * @param {string} port Port
	 */
	private updatePort(port: string): void {
		this.configuration.IqrfInterface = port;
	}
}
</script>
