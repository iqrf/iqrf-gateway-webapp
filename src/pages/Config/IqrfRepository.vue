<template>
	<div>
		<h1>{{ $t('config.iqrfRepository.title') }}</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{ invalid }'>
					<CForm @submit.prevent='saveConfig'>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "config.iqrfRepository.form.messages.instance"}'
						>
							<CInput
								v-model='instance'
								:label='$t("config.iqrfRepository.form.instance")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "config.iqrfRepository.form.messages.urlRepo"}'
						>
							<CInput
								v-model='urlRepo'
								:label='$t("config.iqrfRepository.form.urlRepo")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='integer|required|min:0'
							:custom-messages='{
								integer: "forms.messages.integer",
								required: "config.iqrfRepository.form.messages.checkPeriod",
								min: "config.iqrfRepository.form.messages.checkPeriod"
							}'
						>
							<CInput
								v-model.number='checkPeriodInMinutes'
								type='number'
								min='0'
								:label='$t("config.iqrfRepository.form.checkPeriod")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CInputCheckbox
							v-if='versionNew'
							:checked.sync='downloadIfRepoCacheEmpty'
							:label='$t("config.iqrfRepository.form.downloadIfEmpty")'
						/>
						<CButton type='submit' color='primary' :disabled='invalid'>
							{{ $t('forms.save') }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {AxiosError, AxiosResponse} from 'axios';
import {CButton, CCard, CCardBody, CForm, CInput, CInputCheckbox} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import DaemonConfigurationService	from '../../services/DaemonConfigurationService';
import compareVersions from 'compare-versions';
import {IIqrfRepository} from '../../interfaces/iqrfRepository';

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
	metaInfo: {
		title: 'config.iqrfRepository.title',
	},
})

/**
 * IQRF Repository component configuration
 */
export default class IqrfRepository extends Vue {
	/**
	 * @var {number} checkPeriodInMinutes Check period in minutes
	 */
	private checkPeriodInMinutes = 1

	/**
	 * @constant {string} component IQRF Repository component name
	 */
	private component = 'iqrf::JsCache'

	/**
	 * @var {string} componentInstance IQRF Repository component instance name
	 */
	private componentInstance = ''

	/**
	 * @constant {string} componentName IQRF Repository component name, used for REST API communication
	 */
	private componentName = 'iqrf::JsCache'

	/**
	 * @var {boolean} downloadIfRepoCacheEmpty Download if repository cache is empty?
	 */
	private downloadIfRepoCacheEmpty = true

	/**
	 * @var {string|null} instance IQRF Repository component instance name, used for REST API communication
	 */
	private instance: string|null = null

	/**
	 * @var {string} urlRepo Repository URL
	 */
	private urlRepo = ''

	/**
	 * Computes whether version of IQRF Gateway Daemon is high enough to support new tracer properties
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
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
		this.getConfig();
	}

	/**
	 * Retrieves configuration of IQRF Repository component
	 */
	private getConfig(): void {
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				if (response.data.instances.length > 0) {
					this.parseConfiguration(response.data.instances[0]);
				}
			})
			.catch((error: AxiosError) => FormErrorHandler.configError(error));
	}

	/**
	 * Parses IQRF Repository configuration from REST API response
	 * @param {IIqrfRepository} response Configuration from REST API response
	 */
	private parseConfiguration(response: IIqrfRepository): void {
		this.component = response.component;
		this.instance = this.componentInstance = response.instance;
		this.urlRepo = response.urlRepo;
		this.checkPeriodInMinutes = response.checkPeriodInMinutes;
		if (this.versionNew) {
			if (response.downloadIfRepoCacheEmpty !== undefined) {
				this.downloadIfRepoCacheEmpty = response.downloadIfRepoCacheEmpty;
			}
		}
	}

	/**
	 * Creates IQRF Repository component instance configuration
	 */
	private buildConfiguration(): IIqrfRepository {
		let configuration: IIqrfRepository = {
			component: this.component,
			instance: this.componentInstance,
			urlRepo: this.urlRepo,
			checkPeriodInMinutes: this.checkPeriodInMinutes,
		};
		if (this.versionNew) {
			Object.assign(configuration, {downloadIfRepoCacheEmpty: this.downloadIfRepoCacheEmpty});
		}
		return configuration;
	}

	/**
	 * Saves new or updates existing configuration of IQRF Repository component instance
	 */
	private saveConfig(): void {
		this.$store.commit('spinner/SHOW');
		if (this.instance !== null) {
			DaemonConfigurationService.updateInstance(this.componentName, this.instance, this.buildConfiguration())
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => FormErrorHandler.configError(error));
		} else {
			DaemonConfigurationService.createInstance(this.componentName, this.buildConfiguration())
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => FormErrorHandler.configError(error));
		}
	}

	/**
	 * Handles successful REST API response
	 */
	private successfulSave(): void {
		this.$store.commit('spinner/HIDE');
		this.$toast.success(this.$t('config.success').toString());
	}

}
</script>
