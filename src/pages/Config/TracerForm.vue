<template>
	<div>
		<h1 v-if='$route.path === "/config/tracer/add"'>
			{{ $t('config.tracer.add') }}
		</h1>
		<h1 v-else>
			{{ $t('config.tracer.edit') }}
		</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{ invalid }'>
					<CForm @submit.prevent='saveInstance'>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "config.tracer.form.messages.instance"}'
						>
							<CInput
								v-model='componentInstance'
								:label='$t("config.tracer.form.instance")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CInput
							v-model='path'
							:label='$t("config.tracer.form.path")'
						/>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "config.tracer.form.messages.filename"}'
						>
							<CInput
								v-model='filename'
								:label='$t("config.tracer.form.filename")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='integer|required|min:1'
							:custom-messages='{
								required: "config.tracer.form.messages.maxSizeMb",
								min: "config.tracer.form.messages.maxSizeMb",
								integer: "forms.messages.integer"
							}'
						>
							<CInput
								v-model.number='maxSize'
								type='number'
								:label='$t("config.tracer.form.maxSize")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<div v-if='versionNew'>
							<ValidationProvider
								v-if='versionNew'
								v-slot='{ errors, touched, valid }'
								rules='integer|min:0'
								:custom-messages='{
									integer: "forms.messages.integer",
									min: "config.tracer.form.messages.maxAgeMinutes"
								}'
							>
								<CInput
									v-model.number='maxAgeMinutes'
									type='number'
									min='0'
									:label='$t("config.tracer.form.maxAgeMinutes") + " *"'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider
								
								v-slot='{ errors, touched, valid }'
								rules='integer|min:0'
								:custom-messages='{
									integer: "forms.messages.integer",
									min: "config.tracer.form.messages.maxNumber"
								}'
							>
								<CInput
									v-model.number='maxNumber'
									type='number'
									min='0'
									:label='$t("config.tracer.form.maxNumber") + " *"'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<i>* {{ $t('config.tracer.form.messages.zeroValues') }}</i>
						</div><br v-if='versionNew'>
						<CInputCheckbox
							:checked.sync='timestampFiles'
							:label='$t("config.tracer.form.timestampFiles")'
						/>
						<h4>{{ $t("config.tracer.form.verbosityLevels.title") }}</h4>
						<div
							v-for='(level, i) of VerbosityLevels'
							:key='i'
							class='form-group'
						>
							<ValidationProvider
								v-slot='{ errors, touched, valid }'
								rules='integer|required'
								:custom-messages='{
									integer: "forms.messages.integer",
									required: "config.tracer.form.messages.verbosityLevels.channel"
								}'
							>
								<CInput
									v-model.number='level.channel'
									type='number'
									:label='$t("config.tracer.form.channel")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{ errors, touched, valid }'
								rules='required'
								:custom-messages='{required: "config.tracer.form.messages.verbosityLevels.level"}'
							>
								<CSelect
									:value.sync='level.level'
									:label='$t("config.tracer.form.level")'
									:placeholder='$t("config.tracer.form.messages.verbosityLevels.level")'
									:options='selectOptions'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<CButton
								v-if='VerbosityLevels.length > 1'
								color='danger'
								@click='removeLevel(i)'
							>
								{{ $t('config.tracer.form.verbosityLevels.remove') }}
							</CButton>
							<CButton
								v-if='i === (VerbosityLevels.length - 1)'
								color='success'
								:disabled='level.channel === undefined || level.level === undefined'
								@click='addLevel'
							>
								{{ $t('config.tracer.form.verbosityLevels.add') }}
							</CButton>
						</div>
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
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CForm, CInput, CInputCheckbox, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import VersionService from '../../services/VersionService';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import { MetaInfo } from 'vue-meta';
import { IOption } from '../../interfaces/coreui';
import { AxiosError, AxiosResponse } from 'axios';
import { WebSocketOptions } from '../../store/modules/webSocketClient.module';
import { MutationPayload } from 'vuex';
import compareVersions from 'compare-versions';

interface VerbosityLevel {
	channel: number
	level: string
}

interface TracerConfiguration {
	component: string
	filename: string
	instance: string
	maxSize?: number
	maxSizeMB?: number
	maxAgeMinutes?: number
	maxNumber?: number
	path: string
	timestampFiles: boolean
	VerbosityLevels: Array<VerbosityLevel>
}

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		CInputCheckbox,
		CSelect,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo(): MetaInfo {
		return {
			title: (this as unknown as TracerForm).pageTitle
		};
	}
})

/**
 * Daemon Logging service component configuration card
 */
export default class TracerForm extends Vue {
	/**
	 * @var {string} component Logging service component name
	 */
	private component = ''

	/**
	 * @var {string} componentInstance Logging service component instance name
	 */
	private componentInstance = ''

	/**
	 * @var {string} daemonVersion IQRF Gateway Daemon version
	 */
	private daemonVersion = ''

	/**
	 * @var {string} filename Name of file used by this Logging service component instance
	 */
	private filename = ''

	/**
	 * @var {number} maxSize Maximum size of Logging service component instance file
	 */
	private maxSize = 1048576

	/**
	 * @var {number} maxAgeMinutes Maximum lifespan of timestamped files in minutes, 0 means disabled
	 */
	private maxAgeMinutes = 0

	/**
	 * @var {number} maxNumber Maximum number of timestamped files, 0 means disabled
	 */
	private maxNumber = 0

	/**
	 * @var {string} msgId Daemon api message id
	 */
	private msgId = '';

	/**
	 * @var {string} path Path to directory containing Logging service files
	 */
	private path = ''

	/**
	 * @var {string} timestampFiles Should Logging service files be timestamped?
	 */
	private timestampFiles = false

	/**
	 * @var {Array<VerbosityLevel>} VerbosityLevels Logging service verbosity level settings
	 */
	private VerbosityLevels: Array<VerbosityLevel> = [{channel: 0, level: 'INF'}]

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;}
	

	/**
	 * @constant {string} componentName Logging service component name
	 */
	private componentName = 'shape::TraceFileService'

	/**
	 * @constant {Array<IOption>} selectOptions Array of CoreUI logging severity select options
	 */
	private selectOptions: Array<IOption> = [
		{value: 'ERR', label: this.$t('config.tracer.form.levels.error')},
		{value: 'WAR', label: this.$t('config.tracer.form.levels.warning')},
		{value: 'INF', label: this.$t('config.tracer.form.levels.info')},
		{value: 'DBG', label: this.$t('config.tracer.form.levels.debug')}
	]

	/**
	 * @property {string} instance Logging service component instance name
	 */
	@Prop({required: false, default: ''}) instance!: string

	/**
	 * Computes whether version of IQRF Gateway Daemon is high enough to support new tracer properties
	 * @returns {boolean} true if version >= 2.3.0, false otherwise
	 */
	get versionNew(): boolean {
		if (this.daemonVersion === '') {
			return false;
		}
		if (compareVersions.compare(this.daemonVersion, '2.3.0', '>=')) {
			return true;
		}
		return false;
	}

	/**
	 * Computes page title depending on the action (add, edit)
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$route.path === '/config/tracer/add' ?
			this.$t('config.tracer.add').toString() : this.$t('config.tracer.edit').toString();
	}

	/**
	 * Computes the text of form submit button depending on the action (add, edit)
	 * @returns {string} Button text
	 */
	get submitButton(): string {
		return this.$route.path === '/config/tracer/add' ?
			this.$t('forms.add').toString() : this.$t('forms.edit').toString();
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			if (mutation.payload.mType === 'mngDaemon_Version') {
				this.$store.dispatch('spinner/hide');
				this.$store.dispatch('removeMessage', this.msgId);
				this.handleVersionResponse(mutation.payload.data);
			} else if (mutation.payload.mType === 'messageError') {
				this.$store.dispatch('spinner/hide');
				this.$store.dispatch('removeMessage', this.msgId);
			}
		});
		this.getVersion();
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Retrieves version of IQRF Gateway Daemon
	 */
	private getVersion(): void {
		this.$store.dispatch('spinner/show', {timeout: 10000});
		VersionService.getVersion(new WebSocketOptions(null, 10000, 'config.tracer.messages.versionFail', () => this.msgId = ''))
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles the Daemon version api message response
	 */
	private handleVersionResponse(response: any): void {
		if (response.status === 0 && response.rsp.version !== '') {
			this.daemonVersion = response.rsp.version.substring(0, 6);
			if (this.instance !== '') {
				this.getInstance();
			}
		} else {
			this.$toast.error(this.$t('iqrfnet.networkManager.messages.autoNetwork.versionFailure').toString());
			this.$router.push('/config/tracer/');
		}
	}

	/**
	 * Adds a logging severity level object
	 */
	private addLevel(): void {
		this.VerbosityLevels.push({channel: 0, level: 'INF'});
	}

	/**
	 * Removes a logging severity level object specified by index
	 * @param {number} index Index of logging severity object
	 */
	private removeLevel(index: number): void {
		this.VerbosityLevels.splice(index, 1);
	}

	/**
	 * Retrieves configuration of Logging service component instance
	 */
	private getInstance(): void {
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.getInstance(this.componentName, this.instance)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.parseConfiguration(response);
			})
			.catch((error: AxiosError) => {
				this.$router.push('/config/tracer/');
				FormErrorHandler.configError(error);
			});
	}

	/**
	 * Parses Logging service component instance data
	 */
	private parseConfiguration(response: AxiosResponse): void {
		const configuration = response.data;
		this.component = configuration.component;
		this.componentInstance = configuration.instance;
		this.path = configuration.path;
		this.filename = configuration.filename;
		this.timestampFiles = configuration.timestampFiles;
		this.VerbosityLevels = configuration.VerbosityLevels;
		if (this.versionNew) { // Daemon v2.3.0 supports new properties
			this.maxSize = configuration.maxSize ? configuration.maxSize : configuration.maxSizeMB;
			if (configuration.maxAgeMinutes) { // optional property
				this.maxAgeMinutes = configuration.maxAgeMinutes;
			}
			if (configuration.maxNumber) { // optional property
				this.maxNumber = configuration.maxNumber;
			}
		} else {
			this.maxSize = configuration.maxSizeMB;
		}
	}

	/**
	 * Creates a new Logging service configuration object
	 * @returns {TracerConfiguration} Logging service configuration
	 */
	private buildConfiguration(): TracerConfiguration {
		let configuration: TracerConfiguration = {
			component: this.component,
			instance: this.componentInstance,
			filename: this.filename,
			path: this.path,
			timestampFiles: this.timestampFiles,
			VerbosityLevels: this.VerbosityLevels
		};
		if (this.versionNew) { // Daemon version 2.3.0 or higher
			Object.assign(configuration, {maxSizeMB: this.maxSize}); // TODO: to be changed for version 2.4.0 as maxSize is currently not supported by Shape
			if (this.maxAgeMinutes > 0) { // > 0, not disabled (optional)
				Object.assign(configuration, {maxAgeMinutes: this.maxAgeMinutes});
			}
			if (this.maxNumber > 0) { // > 0, not disabled (optional)
				Object.assign(configuration, {maxNumber: this.maxNumber});
			}
		} else { // Demon version 2.2.2 or lower
			Object.assign(configuration, {maxSizeMB: this.maxSize});
		}
		return configuration;
	}

	/**
	 * Saves new or updates existing configuration of Logging service component instance
	 */
	private saveInstance(): void {
		this.$store.commit('spinner/SHOW');
		if (this.instance !== '') {
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
		if (this.$route.path === '/config/tracer/add') {
			this.$toast.success(
				this.$t('config.tracer.messages.addSuccess', {instance: this.componentInstance})
					.toString()
			);
		} else {
			this.$toast.success(
				this.$t('config.tracer.messages.editSuccess', {instance: this.instance})
					.toString()
			);
		}
		this.$router.push('/config/tracer/');
	}

}
</script>
