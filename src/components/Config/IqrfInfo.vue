<template>
	<CCard class='border-0 card-margin-bottom'>
		<CCardBody>
			<CElementCover v-if='loadFailed'>
				{{ $t('config.daemon.misc.messages.failedElement') }}
			</CElementCover>
			<ValidationObserver v-slot='{invalid}'>
				<CForm
					@submit.prevent='saveConfig'
				>
					<fieldset :disabled='loadFailed'>
						<ValidationProvider
							v-if='powerUser'
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{required: "config.daemon.misc.iqrfInfo.errors.instance"}'
						>
							<CInput
								v-model='configuration.instance'
								:label='$t("forms.fields.instanceName")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<div
							v-if='daemon230'
							class='form-group'
						>
							<label for='enumPeriodicEnable'>
								{{ $t("config.daemon.misc.iqrfInfo.form.enablePeriodic") }}
							</label><br>
							<CSwitch
								id='enumPeriodicEnable'
								color='primary'
								size='lg'
								shape='pill'
								label-on='ON'
								label-off='OFF'
								:checked.sync='enumPeriodic'
							/>
							<ValidationProvider
								v-if='enumPeriodic'
								v-slot='{errors, touched, valid}'
								rules='integer|min:0|required'
								:custom-messages='{
									required: "config.daemon.misc.iqrfInfo.errors.enumPeriod",
									min: "config.daemon.misc.iqrfInfo.errors.enumPeriod",
									integer: "forms.errors.integer"
								}'
							>
								<CInput
									v-model.number='configuration.enumPeriod'
									type='number'
									min='0'
									:label='$t("config.daemon.misc.iqrfInfo.form.enumPeriod")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
						</div>
						<CInputCheckbox
							:checked.sync='configuration.enumAtStartUp'
							:label='$t("config.daemon.misc.iqrfInfo.form.enumAtStartUp")'
						/>
						<CInputCheckbox
							v-if='daemon230'
							:checked.sync='configuration.enumUniformDpaVer'
							:label='$t("config.daemon.misc.iqrfInfo.form.enumUniformDpaVer")'
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
import {Component, Vue, Watch} from 'vue-property-decorator';
import {AxiosError, AxiosResponse} from 'axios';
import {CButton, CCard, CCardBody, CCardHeader, CElementCover, CForm, CInput, CInputCheckbox, CSwitch} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import {IIqrfInfo} from '../../interfaces/iqrfInfo';
import {mapGetters} from 'vuex';
import {versionHigherEqual} from '../../helpers/versionChecker';

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
 * IQRF Info component configuration
 */
export default class IqrfInfo extends Vue {

	/**
	 * @constant {string} name IQRF Info component name
	 */
	private name = 'iqrf::IqrfInfo'

	/**
	 * @var {string} instance IQRF Info component instance name
	 */
	private instance = ''

	/**
	 * @var {IIqrfInfo} configuration IQRF Info component instance configuration
	 */
	private configuration: IIqrfInfo = {
		component: '',
		instance: '',
		enumAtStartUp: false,
	}

	/**
	 * @var {boolean} daemon230 Indicates whether Daemon version is 2.3.0 or higher
	 */
	private daemon230 = false

	/**
	 * @var {boolean} enumPeriodic Shows period input
	 */
	private enumPeriodic = false

	/**
	 * @var {boolean} powerUser Indicates whether user role is power user
	 */
	private powerUser = false

	/**
	 * @var {boolean} loadFailed Indicates whether configuration fetch failed
	 */
	private loadFailed = false

	/**
	 * Daemon version computed property watcher to re-render elements dependent on version
	 */
	@Watch('daemonVersion')
	private updateForm(): void {
		if (versionHigherEqual('2.3.0')) {
			Object.assign(this.configuration, {enumPeriod: 0, enumUniformDpaVer: false});
			this.daemon230 = true;
		}
	}

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
		this.updateForm();
		if (this.$store.getters['user/getRole'] === 'power') {
			this.powerUser = true;
		}
		this.getConfig();
	}

	/**
	 * Retrieves configuration of IQRF Info component
	 */
	private getConfig(): Promise<void> {
		return DaemonConfigurationService.getComponent(this.name)
			.then((response: AxiosResponse) => {
				if (response.data.instances.length > 0) {
					this.parseConfiguration(response.data.instances[0]);
				}
				this.$emit('fetched', {name: 'iqrfInfo', success: true});
			})
			.catch(() => {
				this.loadFailed = true;
				this.$emit('fetched', {name: 'iqrfInfo', success: false});
			});
	}

	/**
	 * Parses IQRF Info configuration from REST API response
	 * @param {IIqrfInfo} response Configuration from REST API response
	 */
	private parseConfiguration(response: IIqrfInfo): void {
		this.instance = response.instance;
		this.configuration = response;
		if (!this.daemon230) {
			return;
		}
		if (this.configuration.enumPeriod !== undefined && this.configuration.enumPeriod > 0) {
			this.enumPeriodic = true;
		}
	}

	/**
	 * Saves new or updates existing configuration of IQRF Info component instance
	 */
	private saveConfig(): void {
		if (this.daemon230 && !this.enumPeriodic) {
			this.configuration.enumPeriod = 0;
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
