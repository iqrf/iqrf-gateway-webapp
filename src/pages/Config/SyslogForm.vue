<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->
<template>
	<div>
		<h1 v-if='$route.path === "/config/daemon/misc/syslog/add"'>
			{{ $t('config.daemon.misc.syslog.add') }}
		</h1>
		<h1 v-else>
			{{ $t('config.daemon.misc.syslog.edit') }}
		</h1>
		<CCard body-wrapper>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='saveInstance'>
					<CRow>
						<CCol md='6'>
							<legend>{{ $t("config.daemon.misc.syslog.form.title") }}</legend>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: $t("config.daemon.misc.tracer.errors.instance"),
								}'
							>
								<CInput
									v-model='configuration.instance'
									:label='$t("forms.fields.instanceName")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
								/>
							</ValidationProvider>
						</CCol>
						<CCol md='6'>
							<legend>{{ $t("config.daemon.misc.tracer.form.verbosityLevels.title") }}</legend>
							<div
								v-for='(level, i) of configuration.VerbosityLevels'
								:key='i'
								class='form-group'
							>
								<hr v-if='i > 0'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='integer|required'
									:custom-messages='{
										integer: $t("forms.errors.integer"),
										required: $t("config.daemon.misc.tracer.errors.verbosityLevels.channel"),
									}'
								>
									<CInput
										v-model.number='level.channel'
										type='number'
										:label='$t("config.daemon.misc.tracer.form.channel")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("config.daemon.misc.tracer.errors.verbosityLevels.level"),
									}'
								>
									<CSelect
										:value.sync='level.level'
										:label='$t("config.daemon.misc.tracer.form.level")'
										:placeholder='$t("config.daemon.misc.tracer.errors.verbosityLevels.level")'
										:options='selectOptions'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(", ")'
									/>
								</ValidationProvider>
								<CButton
									v-if='configuration.VerbosityLevels.length > 1'
									color='danger'
									@click='removeLevel(i)'
								>
									{{ $t('config.daemon.misc.tracer.form.verbosityLevels.remove') }}
								</CButton> <CButton
									v-if='i === (configuration.VerbosityLevels.length - 1)'
									color='success'
									:disabled='level.channel === undefined || level.level === undefined'
									@click='addLevel'
								>
									{{ $t('config.daemon.misc.tracer.form.verbosityLevels.add') }}
								</CButton>
							</div>
						</CCol>
					</CRow>
					<CButton type='submit' color='primary' :disabled='invalid'>
						{{ submitButton }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CButton, CCard, CForm, CInput, CSelect, CSwitch} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {integer, min_value, required} from 'vee-validate/dist/rules';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IOption} from '@/interfaces/Coreui';
import {ISyslogLogger} from '@/interfaces/Config/Misc';
import {MetaInfo} from 'vue-meta';

@Component({
	components: {
		CButton,
		CCard,
		CForm,
		CInput,
		CSelect,
		CSwitch,
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
	 * @constant {string} componentName Component name
	 */
	private componentName = 'iqrf::SyslogLogger';

	/**
	 * @var {ISyslogLogger|null} configuration Syslog component configuration
	 */
	private configuration: ISyslogLogger = {
		component: '',
		instance: '',
		VerbosityLevels: [{channel: 0, level: 'INF'}]
	};

	/**
	 * @constant {Array<IOption>} selectOptions Array of CoreUI logging severity select options
	 */
	private selectOptions: Array<IOption> = [
		{
			value: 'DBG',
			label: this.$t('forms.fields.messageLevel.debug')
		},
		{
			value: 'INF',
			label: this.$t('forms.fields.messageLevel.info')
		},
		{
			value: 'WAR',
			label: this.$t('forms.fields.messageLevel.warning')
		},
		{
			value: 'ERR',
			label: this.$t('forms.fields.messageLevel.error')
		},
	];

	/**
	 * @property {string} instance Logging service component instance name, used for REST API communication
	 */
	@Prop({required: false, default: ''}) instance!: string;

	/**
	 * Computes page title depending on the action (add, edit)
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$route.path === '/config/daemon/misc/syslog/add' ?
			this.$t('config.daemon.misc.syslog.add').toString() : this.$t('config.daemon.misc.syslog.edit').toString();
	}

	/**
	 * Computes the text of form submit button depending on the action (add, edit)
	 * @returns {string} Button text
	 */
	get submitButton(): string {
		return this.$route.path === '/config/daemon/misc/syslog/add' ?
			this.$t('forms.add').toString() : this.$t('forms.edit').toString();
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
		if (this.instance !== '') {
			this.getInstance();
		}
	}

	/**
	 * Adds a logging severity level object
	 */
	private addLevel(): void {
		this.configuration.VerbosityLevels.push({channel: 0, level: 'INF'});
	}

	/**
	 * Removes a logging severity level object specified by index
	 * @param {number} index Index of logging severity object
	 */
	private removeLevel(index: number): void {
		this.configuration.VerbosityLevels.splice(index, 1);
	}

	/**
	 * Retrieves configuration of Logging service component instance
	 */
	private getInstance(): void {
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.getInstance(this.componentName, this.instance)
			.then((response: AxiosResponse) => {
				this.configuration = response.data;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(
					error,
					'config.daemon.misc.syslog.messages.fetchFailed',
					{instance: this.instance}
				);
				this.$router.push({
					name: 'misc',
					params: {
						tabName: 'tracer'
					}
				});
			});
	}

	/**
	 * Saves new or updates existing configuration of Logging service component instance
	 */
	private saveInstance(): void {
		this.$store.commit('spinner/SHOW');
		if (this.instance !== '') {
			DaemonConfigurationService.updateInstance(this.componentName, this.instance, this.configuration)
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => extendedErrorToast(
					error,
					'config.daemon.misc.syslog.messages.editFailed',
					{instance: this.instance}
				));
		} else {
			DaemonConfigurationService.createInstance(this.componentName, this.configuration)
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => extendedErrorToast(
					error,
					'config.daemon.misc.syslog.messages.addFailed'
				));
		}
	}

	/**
	 * Handles successful REST API response
	 */
	private successfulSave(): void {
		this.$store.commit('spinner/HIDE');
		if (this.$route.path === '/config/daemon/misc/syslog/add') {
			this.$toast.success(
				this.$t('config.daemon.misc.syslog.messages.addSuccess', {instance: this.configuration.instance})
					.toString()
			);
		} else {
			this.$toast.success(
				this.$t('config.daemon.misc.syslog.messages.editSuccess', {instance: this.instance})
					.toString()
			);
		}
		this.$router.push({
			name: 'misc',
			params: {
				tabName: 'tracer'
			}
		});
	}

}
</script>
