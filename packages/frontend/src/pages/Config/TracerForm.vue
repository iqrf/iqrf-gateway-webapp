<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
		<h1>{{ pageTitle }}</h1>
		<v-card>
			<v-card-text>
				<ValidationObserver v-slot='{invalid}'>
					<v-form>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("config.daemon.misc.tracer.errors.instance"),
							}'
						>
							<v-text-field
								v-model='configuration.instance'
								:label='$t("forms.fields.instanceName")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-row>
							<v-col cols='12' md='4'>
								<v-text-field
									v-model='configuration.path'
									:label='$t("config.daemon.misc.tracer.form.path")'
								/>
							</v-col>
							<v-col cols='12' md='4'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("config.daemon.misc.tracer.errors.filename"),
									}'
								>
									<v-text-field
										v-model='configuration.filename'
										:label='$t("config.daemon.misc.tracer.form.filename")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
							</v-col>
							<v-col cols='12' md='4'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='integer|required|min:1'
									:custom-messages='{
										required: $t("config.daemon.misc.tracer.errors.maxSizeMb"),
										min: $t("config.daemon.misc.tracer.errors.maxSizeMb"),
										integer: $t("forms.errors.integer"),
									}'
								>
									<v-text-field
										v-model.number='configuration.maxSizeMB'
										type='number'
										:label='$t("config.daemon.misc.tracer.form.maxSize")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
							</v-col>
						</v-row>
						<v-switch
							v-model='configuration.timestampFiles'
							:label='$t("config.daemon.misc.tracer.form.timestampFiles")'
							inset
							dense
						/>
						<div v-if='configuration.timestampFiles'>
							<v-row>
								<v-col cols='12' md='6'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='integer|min:0'
										:custom-messages='{
											integer: $t("forms.errors.integer"),
											min: $t("config.daemon.misc.tracer.errors.maxAgeMinutes"),
										}'
									>
										<v-text-field
											v-model.number='configuration.maxAgeMinutes'
											type='number'
											min='0'
											:label='$t("config.daemon.misc.tracer.form.maxAgeMinutes") + " *"'
											:success='touched ? valid : null'
											:error-messages='errors'
											:hint='$t("config.daemon.misc.tracer.messages.zeroValues")'
											persistent-hint
										/>
									</ValidationProvider>
								</v-col>
								<v-col cols='12' md='6'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='integer|min:0'
										:custom-messages='{
											integer: $t("forms.errors.integer"),
											min: $t("config.daemon.misc.tracer.errors.maxNumber"),
										}'
									>
										<v-text-field
											v-model.number='configuration.maxNumber'
											type='number'
											min='0'
											:label='$t("config.daemon.misc.tracer.form.maxNumber") + " *"'
											:success='touched ? valid : null'
											:error-messages='errors'
											:hint='$t("config.daemon.misc.tracer.messages.zeroValues")'
											persistent-hint
										/>
									</ValidationProvider>
								</v-col>
							</v-row>
						</div>
						<v-row
							v-for='(level, idx) of configuration.VerbosityLevels'
							:key='idx'
						>
							<v-col cols='12' md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("config.daemon.misc.tracer.errors.verbosityLevels.level"),
									}'
								>
									<v-select
										v-model='level.level'
										:label='$t("config.daemon.misc.tracer.form.level")'
										:placeholder='$t("config.daemon.misc.tracer.errors.verbosityLevels.level")'
										:items='severityOptions'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
							</v-col>
							<v-col cols='12' md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='integer|required'
									:custom-messages='{
										integer: $t("forms.errors.integer"),
										required: $t("config.daemon.misc.tracer.errors.verbosityLevels.channel"),
									}'
								>
									<v-text-field
										v-model.number='level.channel'
										type='number'
										:label='$t("config.daemon.misc.tracer.form.channel")'
										:success='touched ? valid : null'
										:error-messages='errors'
									>
										<template #append-outer>
											<v-btn
												v-if='configuration.VerbosityLevels.length > 1'
												color='error'
												small
												@click='removeLevel(idx)'
											>
												<v-icon>
													mdi-delete-outline
												</v-icon>
											</v-btn>
											<v-btn
												v-if='idx === (configuration.VerbosityLevels.length - 1)'
												:class='configuration.VerbosityLevels.length > 1 ? "ml-1" : ""'
												color='success'
												small
												@click='addLevel'
											>
												<v-icon>
													mdi-plus
												</v-icon>
											</v-btn>
										</template>
									</v-text-field>
								</ValidationProvider>
							</v-col>
						</v-row>
						<v-btn
							color='primary'
							:disabled='invalid'
							@click='saveInstance'
						>
							{{ submitButton }}
						</v-btn>
					</v-form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {integer, min_value, required} from 'vee-validate/dist/rules';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {ISelectItem} from '@/interfaces/Vuetify';
import {ITraceService} from '@/interfaces/Config/Misc';
import {MetaInfo} from 'vue-meta';

@Component({
	components: {
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
	private componentName = 'shape::TraceFileService';

	/**
	 * @var {ITraceService} configuration TraceFile component configuration
	 */
	private configuration: ITraceService = {
		component: '',
		instance: '',
		filename: '',
		maxSizeMB: 1048576,
		maxAgeMinutes: 0,
		maxNumber: 0,
		path: '',
		timestampFiles: false,
		VerbosityLevels: [{channel: 0, level: 'INF'}]
	};

	/**
	 * @constant {Array<ISelectItem>} severityOptions Logging severity options
	 */
	private severityOptions: Array<ISelectItem> = [
		{
			value: 'DBG',
			text: this.$t('forms.fields.messageLevel.debug').toString(),
		},
		{
			value: 'INF',
			text: this.$t('forms.fields.messageLevel.info').toString(),
		},
		{
			value: 'WAR',
			text: this.$t('forms.fields.messageLevel.warning').toString(),
		},
		{
			value: 'ERR',
			text: this.$t('forms.fields.messageLevel.error').toString(),
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
		return this.$route.path === '/config/daemon/misc/tracer/add' ?
			this.$t('config.daemon.misc.tracer.add').toString() : this.$t('config.daemon.misc.tracer.edit').toString();
	}

	/**
	 * Computes the text of form submit button depending on the action (add, edit)
	 * @returns {string} Button text
	 */
	get submitButton(): string {
		return this.$route.path === '/config/daemon/misc/tracer/add' ?
			this.$t('forms.add').toString() : this.$t('forms.edit').toString();
	}

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
	}

	/**
	 * Loads component data
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
				if (this.configuration.maxAgeMinutes === undefined) {
					this.configuration.maxAgeMinutes = 0;
				}
				if (this.configuration.maxNumber === undefined) {
					this.configuration.maxNumber = 0;
				}
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(
					error,
					'config.daemon.misc.tracer.messages.fetchFailed',
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
		if (!this.configuration.timestampFiles) {
			this.configuration.maxAgeMinutes = 0;
			this.configuration.maxNumber = 0;
		}
		this.$store.commit('spinner/SHOW');
		if (this.instance !== '') {
			DaemonConfigurationService.updateInstance(this.componentName, this.instance, this.configuration)
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => extendedErrorToast(
					error,
					'config.daemon.misc.tracer.messages.editFailed',
					{instance: this.instance}
				));
		} else {
			DaemonConfigurationService.createInstance(this.componentName, this.configuration)
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => extendedErrorToast(
					error,
					'config.daemon.misc.tracer.messages.addFailed'
				));
		}
	}

	/**
	 * Handles successful REST API response
	 */
	private successfulSave(): void {
		this.$store.commit('spinner/HIDE');
		if (this.$route.path === '/config/daemon/misc/tracer/add') {
			this.$toast.success(
				this.$t('config.daemon.misc.tracer.messages.addSuccess', {instance: this.configuration.instance})
					.toString()
			);
		} else {
			this.$toast.success(
				this.$t('config.daemon.misc.tracer.messages.editSuccess', {instance: this.instance})
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
