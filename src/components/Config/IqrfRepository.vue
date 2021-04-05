<template>
	<CCard class='border-0 card-margin-bottom'>
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
							:custom-messages='{required: "config.daemon.misc.iqrfRepository.errors.instance"}'
						>
							<CInput
								v-model='configuration.instance'
								:label='$t("forms.fields.instanceName")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{required: "config.daemon.misc.iqrfRepository.errors.urlRepo"}'
						>
							<CInput
								v-model='configuration.urlRepo'
								:label='$t("config.daemon.misc.iqrfRepository.form.urlRepo")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<div class='form-group'>
							<label for='checkEnableSwitch'>
								{{ $t("config.daemon.misc.iqrfRepository.form.enableCheck") }}
							</label><br>
							<CSwitch
								id='checkEnableSwitch'
								color='primary'
								size='lg'
								shape='pill'
								label-on='ON'
								label-off='OFF'
								:checked.sync='checkEnabled'
							/>
						</div>
						<ValidationProvider
							v-if='checkEnabled'
							v-slot='{errors, touched, valid}'
							rules='integer|required|min:0'
							:custom-messages='{
								integer: "forms.errors.integer",
								required: "config.daemon.misc.iqrfRepository.errors.checkPeriod",
								min: "config.daemon.misc.iqrfRepository.errors.checkPeriod"
							}'
						>
							<CInput
								v-model.number='configuration.checkPeriodInMinutes'
								type='number'
								min='0'
								:label='$t("config.daemon.misc.iqrfRepository.form.checkPeriod")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CInputCheckbox
							:checked.sync='configuration.downloadIfRepoCacheEmpty'
							:label='$t("config.daemon.misc.iqrfRepository.form.downloadIfEmpty")'
						/>
						<CButton type='submit' color='primary' :disabled='invalid'>
							{{ $t('forms.save') }}
						</CButton>
					</fieldset>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {AxiosError, AxiosResponse} from 'axios';
import {CButton, CCard, CCardBody, CCardHeader, CElementCover, CForm, CInput, CInputCheckbox, CSwitch} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import DaemonConfigurationService	from '../../services/DaemonConfigurationService';
import {IIqrfRepository} from '../../interfaces/iqrfRepository';
import {mapGetters} from 'vuex';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CElementCover,
		CForm,
		CInput,
		CInputCheckbox,
		CSwitch,
		ValidationObserver,
		ValidationProvider,
	},
	computed: {
		...mapGetters({
			daemonVersion: 'daemonVersion',
		}),
	}
})

/**
 * IQRF Repository component configuration
 */
export default class IqrfRepository extends Vue {

	/**
	 * @constant {string} name IQRF Repository component name, used for REST API communication
	 */
	private name = 'iqrf::JsCache'

	/**
	 * @var {string} instance IQRF Repository component instance name
	 */
	private instance = ''

	/**
	 * @var {IIqrfRepository} configuration IQRF Repository component instance configuration
	 */
	private configuration: IIqrfRepository = {
		component: '',
		instance: '',
		urlRepo: '',
		checkPeriodInMinutes: 0,
		downloadIfRepoCacheEmpty: true,
	}

	/**
	 * @var {boolean} checkEnabled Enable periodical update check
	 */
	private checkEnabled = false

	/**
	 * @var {boolean} powerUser Indicates whether user role is power user
	 */
	private powerUser = false

	/**
	 * @var {boolean} loadFailed Indicates whether configuration fetch failed
	 */
	private loadFailed = false

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('integer', integer);
		extend('min', min_value);
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
	 * Retrieves configuration of IQRF Repository component
	 */
	private getConfig(): Promise<void> {
		return DaemonConfigurationService.getComponent(this.name)
			.then((response: AxiosResponse) => {
				if (response.data.instances.length > 0) {
					this.parseConfiguration(response.data.instances[0]);
				}
				this.$emit('fetched', {name: 'iqrfRepository', success: true});
			})
			.catch(() => {
				this.loadFailed = true;
				this.$emit('fetched', {name: 'iqrfRepository', success: false});
			});
	}

	/**
	 * Parses IQRF Repository configuration from REST API response
	 * @param {IIqrfRepository} response Configuration from REST API response
	 */
	private parseConfiguration(response: IIqrfRepository): void {
		this.instance = response.instance;
		this.configuration = response;
		if (this.configuration.checkPeriodInMinutes !== undefined && this.configuration.checkPeriodInMinutes > 0) {
			this.checkEnabled = true;
		}
	}

	/**
	 * Saves new or updates existing configuration of IQRF Repository component instance
	 */
	private saveConfig(): void {
		if (!this.checkEnabled) {
			this.configuration.checkPeriodInMinutes = 0;
		}
		this.$store.commit('spinner/SHOW');
		if (this.instance !== '') {
			DaemonConfigurationService.updateInstance(this.name, this.instance, this.configuration)
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => FormErrorHandler.configError(error));
		} else {
			DaemonConfigurationService.createInstance(this.name, this.configuration)
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

}
</script>
