<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
		<h1 v-if='$route.path === "/config/daemon/scheduler/add"'>
			{{ $t('config.daemon.scheduler.add') }}
		</h1>
		<h1 v-else>
			{{ $t('config.daemon.scheduler.edit') }}
		</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{ invalid }'>
					<CForm @submit.prevent='saveTask'>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='integer|required'
							:custom-messages='{
								required: $t("config.daemon.scheduler.errors.nums"),
								integer: $t("config.daemon.scheduler.errors.nums"),
							}'
						>
							<CInput
								v-model.number='taskId'
								type='number'
								:label='$t("config.daemon.scheduler.form.task.taskId")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("config.daemon.scheduler.errors.service"),
							}'
						>
							<CSelect
								:value.sync='clientId'
								:label='$t("config.daemon.scheduler.table.service")'
								:options='[
									{value: "SchedulerMessaging", label: "SchedulerMessaging"}
								]'
								placeholder='Select service'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<CSelect
							:value.sync='timeSpecSelected'
							:label='$t("config.daemon.scheduler.form.task.timeSpec")'
							:options='timeSpecOptions'
						/>
						<div
							v-if='timeSpecSelected === "cron"'
							class='form-group'
						>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='cron|required'
								:custom-messages='{
									cron: $t("config.daemon.scheduler.errors.cron"),
									required: $t("config.daemon.scheduler.errors.cron"),
								}'
							>
								<label for='cronTime'>
									<strong>{{ $t("config.daemon.scheduler.form.task.cronTime") }}</strong>
								</label> <CBadge v-if='cronMessage !== null' :color='valid ? "info" : "danger"'>
									{{ cronMessage }}
								</CBadge>
								<CInput
									id='cronTime'
									v-model='timeSpec.cronTime'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
									@input='cronMessage = null'
								/>
							</ValidationProvider>
						</div>
						<ValidationProvider
							v-if='timeSpecSelected === "periodic"'
							v-slot='{errors, touched, valid}'
							rules='integer|required|min:0'
							:custom-messages='{
								required: $t("config.daemon.scheduler.errors.period"),
								integer: $t("config.daemon.scheduler.errors.period"),
								min: $t("config.daemon.scheduler.errors.period"),
							}'
						>
							<CInput
								v-model.number='timeSpec.period'
								type='number'
								min='0'
								:label='$t("config.daemon.scheduler.form.task.period")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<div
							v-if='timeSpecSelected === "exact"'
							class='form-group'
						>
							<label for='exactTime'>
								{{ $t("config.daemon.scheduler.form.task.startTime") }}
							</label>
							<Datetime
								id='exactTime'
								v-model='timeSpec.startTime'
								type='datetime'
								:format='dateFormat'
								:min-datetime='new Date().toISOString()'
								input-class='form-control'
							/>
						</div><hr>
						<div class='messages-header'>
							<h3>
								{{ $t('config.daemon.scheduler.form.messages.title') }}
							</h3>
							<CButton
								color='primary'
								size='sm'
								href='https://docs.iqrf.org/iqrf-gateway/daemon-api.html'
								target='_blank'
							>
								{{ $t("iqrfnet.sendJson.documentation") }}
							</CButton>
						</div>
						<div v-for='i of tasks.length' :key='i' class='form-group'>
							<hr v-if='i > 1'>
							<CRow>
								<CCol>
									<JsonSchemaErrors :errors='validatorErrors[i-1]' />
								</CCol>
							</CRow>
							<CRow>
								<CCol md='6'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required|json|mType'
										:custom-messages='{
											required: $t("config.daemon.scheduler.errors.message"),
											json: $t("iqrfnet.sendJson.messages.invalid"),
											mType: $t("iqrfnet.sendJson.messages.mType"),
										}'
										slim
									>
										<JsonEditor
											v-model='tasks[i-1].message'
											:label='$t("config.daemon.scheduler.form.messages.label").toString()'
											:is-valid='touched ? valid : null'
											:invalid-feedback='errors.join(", ").toString()'
										/>
									</ValidationProvider>
									<CButton
										v-if='tasks.length > 1'
										color='danger'
										@click='removeMessage(i-1)'
									>
										{{ $t('config.daemon.scheduler.form.messages.remove') }}
									</CButton> <CButton
										v-if='i === tasks.length'
										color='success'
										@click='addMessage'
									>
										{{ $t('config.daemon.scheduler.form.messages.add') }}
									</CButton>
								</CCol>
								<CCol md='6'>
									<div
										v-for='(messaging, j) of tasks[i-1].messaging'
										:key='j'
										class='form-group'
									>
										<ValidationProvider
											v-slot='{errors, touched, valid}'
											rules='required'
											:custom-messages='{
												required: $t("config.daemon.scheduler.errors.service"),
											}'
										>
											<CSelect
												:value.sync='tasks[i-1].messaging[j]'
												:label='$t("config.daemon.scheduler.form.messages.messaging")'
												:placeholder='$t("config.daemon.scheduler.form.messages.messagingPlaceholder")'
												:options='messagings'
												:is-valid='touched ? valid : null'
												:invalid-feedback='errors.join(", ")'
											/>
										</ValidationProvider>
										<CButton
											v-if='tasks[i-1].messaging.length > 1'
											color='danger'
											@click='removeTaskMessaging(i-1, j)'
										>
											{{ $t('config.daemon.scheduler.form.messagings.remove') }}
										</CButton> <CButton
											v-if='j === (tasks[i-1].messaging.length - 1)'
											color='success'
											@click='addTaskMessaging(i-1)'
										>
											{{ $t('config.daemon.scheduler.form.messagings.add') }}
										</CButton>
									</div>
								</CCol>
							</CRow>
						</div>
						<CButton type='submit' color='primary' :disabled='invalid || (timeSpecSelected === "exact" && timeSpec.startTime === "")'>
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
import {CBadge, CButton, CCard, CCardBody, CCardHeader, CForm, CInput, CInputCheckbox, CSelect, CTextarea} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {integer, required, min_value} from 'vee-validate/dist/rules';
import {Datetime} from 'vue-datetime';

import DaemonApiValidator from '@/helpers/DaemonApiValidator';
import {extendedErrorToast} from '@/helpers/errorToast';

import cron from 'cron-validate';
import cronstrue from 'cronstrue';
import DaemonConfigurationService from '@/services/DaemonConfigurationService';
import SchedulerService from '@/services/SchedulerService';

import {AxiosError, AxiosResponse} from 'axios';
import {IOption} from '@/interfaces/Coreui';
import {ITaskRest, ITaskDaemon, ITaskMessage, ITaskMessaging, ITaskTimeSpec} from '@/interfaces/DaemonApi/Scheduler';
import {MetaInfo} from 'vue-meta';
import {MutationPayload} from 'vuex';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';
import {IWsMessaging} from '@/interfaces/Config/Messaging';

import JsonEditor from '@/components/Config/JsonEditor.vue';
import JsonSchemaErrors from '@/components/Config/JsonSchemaErrors.vue';

enum TimeSpecTypes {
	CRON = 'cron',
	EXACT = 'exact',
	PERIODIC = 'periodic'
}

@Component({
	components: {
		CBadge,
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		CInputCheckbox,
		CSelect,
		CTextarea,
		Datetime,
		JsonEditor,
		JsonSchemaErrors,
		ValidationObserver,
		ValidationProvider
	},
	metaInfo(): MetaInfo {
		return {
			title: (this as unknown as SchedulerForm).pageTitle
		};
	}
})

/**
 * Scheduler task form component
 */
export default class SchedulerForm extends Vue {
	/**
	 * @var {string} clientId Scheduler task client ID
	 */
	private clientId = 'SchedulerMessaging';

	/**
	 * @constant {Record<string, string>} components Names of messaging components
	 */
	private components: Record<string, string> = {
		mq: 'iqrf::MqMessaging',
		mqtt: 'iqrf::MqttMessaging',
		websocket: 'iqrf::WebsocketMessaging'
	};

	/**
	 * @var {string|null} cronMessage Converted message from time setting in cron format
	 */
	private cronMessage: string|null = null;

	/**
	 * @constant {Record<string, string|boolean>} dateFormat Date formatting options
	 */
	private dateFormat: Record<string, string|boolean> = {
		year: 'numeric',
		month: 'short',
		day: 'numeric',
		hour12: false,
		hour: 'numeric',
		minute: 'numeric',
		second: 'numeric',
	};

	/**
	 * @var {Array<IOption>} messagings Array of available messaging component instances
	 */
	private messagings: Array<IOption> = [];

	/**
	 * @var {Array<string>} msgIds Array of daemon api message ids
	 */
	private msgIds: Array<string> = [];

	/**
	 * @var {DaemonApiValidator} validator JSON schema validator function
	 */
	private validator: DaemonApiValidator;

	/**
	 * @var {Array<Array<string>>} validatorErrors String containing JSON schema violations
	 */
	private validatorErrors: Array<Array<string>> = [[]];

	/**
	 * @constant {number} taskId Scheduler task id in epoch seconds
	 */
	private taskId = Math.floor(Date.now() / 1000);

	/**
	 * @var {Array<ILocalTask>} tasks Array of scheduler task messages
	 */
	private tasks: Array<ITaskMessaging> = [
		{
			message: '',
			messaging: [''],
		},
	];

	/**
	 * @var {ITaskTimeSpec} timeSpec Scheduler task time specification
	 */
	private timeSpec: ITaskTimeSpec = {
		cronTime: '',
		periodic: false,
		period: 1,
		exactTime: false,
		startTime: ''
	};

	/**
	 * @var {TimeSpecTypes} timeSpecSelected Selected task time specification type
	 */
	private timeSpecSelected = TimeSpecTypes.EXACT;

	/**
	 * @constant {Array<IOption>} timeSpecOptions Scheduler task time specification options
	 */
	private timeSpecOptions: Array<IOption> = [
		{
			value: TimeSpecTypes.EXACT,
			label: this.$t('config.daemon.scheduler.form.task.timeSpecTypes.exact').toString(),
		},
		{
			value: TimeSpecTypes.PERIODIC,
			label: this.$t('config.daemon.scheduler.form.task.timeSpecTypes.periodic').toString(),
		},
		{
			value: TimeSpecTypes.CRON,
			label: this.$t('config.daemon.scheduler.form.task.timeSpecTypes.cron').toString(),
		},
	];

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * @var {boolean} untouched Indicates whether props for creation of scheduler tasks have been retrieved
	 */
	private untouched = true;

	/**
	 * @var {boolean} useRest Indicates whether the webapp should use REST API to retrieve scheduler task props
	 */
	private useRest = true;

	/**
	 * @constant cronPreset Cron validate presets
	 */
	private cronPreset = {
		presetId: 'schedulerCron',
		useSeconds: true,
		useYears: true,
		useAliases: true,
		useBlankDay: true,
		allowOnlyOneBlankDayField: false,
		seconds: {
			minValue: 0,
			maxValue: 59,
		},
		minutes: {
			minValue: 0,
			maxValue: 59,
		},
		hours: {
			minValue: 0,
			maxValue: 23,
		},
		daysOfMonth: {
			minValue: 1,
			maxValue: 31,
		},
		months: {
			minValue: 1,
			maxValue: 12,
		},
		daysOfWeek: {
			minValue: 0,
			maxValue: 6,
		},
		years: {
			minValue: 1970,
			maxValue: 2099,
		},
	};

	/**
	 * @constant {Array<string>} dayAliases Array of day aliases for day of week range 0-6
	 */
	private dayAliases = [
		'sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'
	];

	/**
	 * @constant {Array<string>} monthAliases Array of month aliases for month range 1-12
	 */
	private monthAliases = [
		'jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'
	];

	/**
	 * @property {number} id Id of existing scheduler task
	 */
	@Prop({required: false, default: null}) id!: number;

	/**
	 * Constructor
	 */
	constructor() {
		super();
		this.validator = new DaemonApiValidator();
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		this.$store.commit('spinner/SHOW');
		extend('json', (json) => {
			return this.validator.validate(json, (errorMessages) => {
				const index = this.tasks.findIndex((task) => task.message === json);
				this.validatorErrors[index] = errorMessages;
			});
		});
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
		extend('mType', (json) => {
			const object = JSON.parse(json);
			return {}.hasOwnProperty.call(object, 'mType');
		});
		extend('cron', (cronstring: string) => {
			if (cronstring[0] === '@') {
				const cronAlias = this.getCronAlias(cronstring);
				if (cronAlias !== undefined) {
					this.cronMessage = cronstrue.toString(cronAlias);
					return true;
				}
				this.cronMessage = null;
				return false;
			}
			const cronResult = cron(cronstring, {preset: this.cronPreset});
			if (cronResult.isValid()) {
				this.calculateCron(cronstring);
				return true;
			} else {
				this.cronMessage = cronResult.getError().join(',');
				return false;
			}
		});
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'daemonClient/SOCKET_ONOPEN') {
				this.useRest = false;
				if (this.id && this.untouched) {
					this.getTask(this.id);
				}
			} else if (mutation.type === 'daemonClient/SOCKET_ONCLOSE' ||
				mutation.type === 'daemonClient/SOCKET_ONERROR') {
				this.useRest = true;
			} else if (mutation.type === 'daemonClient/SOCKET_ONMESSAGE') {
				if (!this.msgIds.includes(mutation.payload.data.msgId)) {
					return;
				}
				this.$store.commit('spinner/HIDE');
				this.$store.dispatch('daemonClient/removeMessage', mutation.payload.data.msgId);
				if (mutation.payload.mType === 'mngScheduler_GetTask') {
					this.handleGetTask(mutation.payload.data);
				} else if (mutation.payload.mType === 'mngScheduler_AddTask') {
					this.handleAddTask(mutation.payload.data);
				} else if (mutation.payload.mType === 'mngScheduler_RemoveTask') {
					this.handleRemoveTask(mutation.payload.data);
				} else if (mutation.payload.mType === 'messageError') {
					this.$toast.error(
						this.$t('config.daemon.scheduler.messages.processError').toString()
					);
				}
			}
		});
		this.getMessagings();
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		setTimeout(() => {
			if (this.$store.getters['daemonClient/isConnected']) {
				this.useRest = false;
			}
			if (this.id && this.untouched) {
				this.getTask(this.id);
			}
		}, 1000);
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.msgIds.forEach((item: string) => this.$store.dispatch('daemonClient/removeMessage', item));
		this.unsubscribe();
	}

	/**
	 * Computes page title depending on the action (add, edit)
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$route.path === '/config/daemon/scheduler/add' ?
			this.$t('config.daemon.scheduler.add').toString() : this.$t('config.daemon.scheduler.edit').toString();
	}

	/**
	 * Converts cron time expression into a human readable message
	 * @param {string} cronExpression Cron time expression
	 */
	private calculateCron(cronExpression: string): void {
		const cronTime = cronExpression.trim().split(' ');
		const len = cronTime.length;
		if (len === 1) {
			const alias = this.getCronAlias((this.timeSpec.cronTime as string));
			if (alias !== undefined) {
				this.cronMessage = cronstrue.toString(alias);
			} else {
				this.cronMessage = null;
			}
		} else if (len > 4 && len < 8) {
			this.cronMessage = cronstrue.toString((this.timeSpec.cronTime as string));
		} else {
			this.cronMessage = null;
		}
	}

	/**
	 * Adds another scheduler task message object
	 */
	private addMessage(): void {
		this.tasks.push({message: '', messaging: ['']});
		this.validatorErrors.push([]);
	}

	/**
	 * Adds another scheduler task messaging
	 * @param {number} index Task index
	 */
	private addTaskMessaging(index: number): void {
		this.tasks[index].messaging.push('');
	}

	/**
	 * Removes a scheduler task message object specified by index
	 * @param {number} index Index of scheduler task message
	 */
	private removeMessage(index: number): void {
		this.tasks.splice(index, 1);
		this.validatorErrors.splice(index, 1);
	}

	/**
	 * Removes a scheduler task messaging specified
	 * @param {number} tIndex Task index
	 * @param {number} mIndex Messaging index
	 */
	private removeTaskMessaging(tIndex: number, mIndex: number): void {
		this.tasks[tIndex].messaging.splice(mIndex, 1);
	}

	/**
	 * Retrieves task specified by passed id
	 * @param {number} taskId Scheduler task id
	 */
	private getTask(taskId: number): void {
		this.untouched = false;
		this.$store.commit('spinner/SHOW');
		if (this.useRest) {
			SchedulerService.getTaskREST(taskId)
				.then((response: AxiosResponse) => {
					this.handleGetTaskRest(response.data);
					this.$store.commit('spinner/HIDE');
				})
				.catch((error: AxiosError) => {
					extendedErrorToast(error, 'config.daemon.scheduler.messages.getFail', {task: this.id});
					this.$router.push('/config/daemon/scheduler/');
				});
		} else {
			SchedulerService.getTask(taskId, new DaemonMessageOptions(null, 30000, null, () => this.$router.push('/config/daemon/scheduler/')))
				.then((msgId: string) => this.storeId(msgId));
		}
	}

	/**
	 * Handles GetTask response from Daemon API
	 * @param response Task retrieved from Daemon API
	 */
	private handleGetTask(response): void {
		if (response.status !== 0) {
			this.$router.push('/config/daemon/scheduler/');
			this.$toast.error(
				this.$t('config.daemon.scheduler.messages.getFail', {task: this.id})
					.toString()
			);
			return;
		}
		const taskDaemon: ITaskDaemon = response.rsp;
		if (Array.isArray(taskDaemon.timeSpec.cronTime)) {
			taskDaemon.timeSpec.cronTime = taskDaemon.timeSpec.cronTime.join(' ').trim();
		}
		this.taskId = taskDaemon.taskId;
		this.clientId = taskDaemon.clientId;
		if (!taskDaemon.timeSpec.exactTime && !taskDaemon.timeSpec.periodic) {
			this.timeSpecSelected = TimeSpecTypes.CRON;
		} else if (taskDaemon.timeSpec.exactTime && !taskDaemon.timeSpec.periodic) {
			this.timeSpecSelected = TimeSpecTypes.EXACT;
		} else {
			this.timeSpecSelected = TimeSpecTypes.PERIODIC;
		}
		this.timeSpec = taskDaemon.timeSpec;
		if (Array.isArray(taskDaemon.task)) {
			this.tasks = taskDaemon.task.map((item: ITaskMessage) => ({
				messaging: Array.isArray(item.messaging) ? item.messaging : item.messaging.split('&'),
				message: JSON.stringify(item.message, null, 2),
			}));
		} else {
			this.tasks = [
				{
					messaging: Array.isArray(taskDaemon.task.messaging) ? taskDaemon.task.messaging : taskDaemon.task.messaging.split('&'),
					message: JSON.stringify(taskDaemon.task.message, null, 2),
				}
			];
		}
		for (let i = 0; i < this.tasks.length; ++i) {
			this.validatorErrors.push([]);
		}
	}

	/**
	 * Handles GetTask response from REST API
	 * @param {ITaskRest} taskRest Task retrieved from REST API
	 */
	private handleGetTaskRest(taskRest: ITaskRest): void {
		this.taskId = taskRest.taskId;
		this.clientId = taskRest.clientId;
		if (!taskRest.timeSpec.exactTime && !taskRest.timeSpec.periodic) {
			this.timeSpecSelected = TimeSpecTypes.CRON;
		} else if (taskRest.timeSpec.exactTime && !taskRest.timeSpec.periodic) {
			this.timeSpecSelected = TimeSpecTypes.EXACT;
		} else {
			this.timeSpecSelected = TimeSpecTypes.PERIODIC;
		}
		this.timeSpec = taskRest.timeSpec;
		this.tasks = taskRest.task.map((item: ITaskMessage) => ({
			messaging: Array.isArray(item.messaging) ? item.messaging : item.messaging.split('&'),
			message: JSON.stringify(item.message, null, 2),
		}));
		for (let i = 0; i < this.tasks.length; ++i) {
			this.validatorErrors.push([]);
		}
	}

	/**
	 * Stores message id for later response handling
	 * @param {string} msgId Daemon api message id
	 */
	private storeId(msgId: string): void {
		this.msgIds.push(msgId);
		setTimeout(() => {
			if (this.msgIds.includes(msgId)) {
				this.msgIds.splice(this.msgIds.indexOf(msgId), 1);
			}
		}, 30000);
	}

	/**
	 * Retrieves instances of Daemon messaging components
	 */
	private getMessagings(): void {
		this.$store.commit('spinner/SHOW');
		Promise.all([
			DaemonConfigurationService.getComponent(this.components.mq),
			DaemonConfigurationService.getComponent(this.components.mqtt),
			DaemonConfigurationService.getComponent(this.components.websocket),
		])
			.then((responses: Array<AxiosResponse>) => {
				this.$store.commit('spinner/HIDE');
				responses.forEach((item: AxiosResponse) => {
					if (item.data.instances) {
						item.data.instances.forEach((messaging: IWsMessaging) => {
							this.messagings.push({
								value: messaging.instance, label: messaging.instance,
							});
						});
					}
				});
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.scheduler.messages.messagingFailed');
				this.$router.push('/config/daemon/scheduler/');
			});
	}

	/**
	 * Prepares task time specification before submitting
	 * @returns {ITaskTimeSpec} Submission ready time specification object
	 */
	private prepareTaskToSubmit(): ITaskTimeSpec {
		const timeSpec = JSON.parse(JSON.stringify(this.timeSpec));
		if (this.timeSpecSelected === TimeSpecTypes.EXACT) { // exact time, reset others
			timeSpec.cronTime = Array(7).fill('');
			timeSpec.exactTime = true;
			timeSpec.periodic = false;
			timeSpec.period = 0;
			const date = new Date(timeSpec.startTime);
			date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
			timeSpec.startTime = date.toISOString().split('.')[0];
		} else if (this.timeSpecSelected === TimeSpecTypes.PERIODIC) { // periodic time, reset others
			timeSpec.cronTime = Array(7).fill('');
			timeSpec.exactTime = false;
			timeSpec.periodic = true;
			timeSpec.startTime = '';
		} else { // cron time, reset others
			timeSpec.cronTime = timeSpec.cronTime.replace('?', '*').split(' ');
			if (timeSpec.cronTime.length === 1) { // potentially using alias
				const alias = this.getCronAlias(timeSpec.cronTime[0]);
				if (alias !== undefined) {
					timeSpec.cronTime = alias.split(' ');
				} else {
					timeSpec.cronTime = Array(7).fill('');
				}
			}
			if (this.dayAliases.includes(timeSpec.cronTime[5])) { // day alias translation
				timeSpec.cronTime[5] = this.dayAliases.indexOf(timeSpec.cronTime[5]).toString();
			}
			if (this.monthAliases.includes(timeSpec.cronTime[4])) {
				timeSpec.cronTime[4] = (this.monthAliases.indexOf(timeSpec.cronTime[4]) + 1).toString();
			}
			timeSpec.exactTime = timeSpec.periodic = false;
			timeSpec.period = 0;
			timeSpec.startTime = '';
		}
		return timeSpec;
	}

	/**
	 * Processes time specification for daemon api and then saves scheduler task
	 */
	private saveTask(): void {
		this.$store.commit('spinner/SHOW');
		if (this.$route.path === '/config/daemon/scheduler/add') {
			if (this.useRest) {
				SchedulerService.addTaskREST(this.taskId, this.clientId, this.tasks, this.prepareTaskToSubmit())
					.then(() => this.successfulSave())
					.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.scheduler.messages.addFailedRest'));
			} else {
				SchedulerService.addTask(this.taskId, this.clientId, this.tasks, this.prepareTaskToSubmit(), new DaemonMessageOptions(
					null, 30000, 'config.daemon.scheduler.messages.processError'))
					.then((msgId: string) => this.storeId(msgId));
			}
		} else {
			if (this.useRest) {
				SchedulerService.editTaskREST(this.id, this.taskId, this.clientId, this.tasks, this.prepareTaskToSubmit())
					.then(() => this.successfulSave())
					.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.scheduler.messages.editFailedRest'));
			} else {
				SchedulerService.removeTask(this.id, new DaemonMessageOptions(null, 30000, 'config.daemon.scheduler.messages.deleteFail'))
					.then((msgId: string) => this.storeId(msgId));
			}
		}
	}

	/**
	 * Handles Remove Task response from Daemon API
	 * @param response Daemon API response
	 */
	private handleRemoveTask(response): void {
		if (response.status !== 0) {
			this.$toast.error(
				this.$t('config.daemon.scheduler.messages.deleteFail').toString()
			);
			return;
		}
		SchedulerService.addTask(
			this.taskId,
			this.clientId,
			this.tasks,
			this.prepareTaskToSubmit(),
			new DaemonMessageOptions(null, 30000, 'config.daemon.scheduler.messages.processError')
		).then((msgId: string) => this.storeId(msgId));
	}

	/**
	 * Retrieves cron alias from predefined strings
	 * @param {string} input Cron time string
	 * @returns {string|undefined} Cron time alias if one exists for received time string
	 */
	private getCronAlias(input: string): string|undefined {
		const aliases = new Map();
		aliases.set('@reboot', '');
		aliases.set('@yearly', '0 0 0 1 1 * *');
		aliases.set('@annually', '0 0 0 1 1 * *');
		aliases.set('@monthly', '0 0 0 1 * * *');
		aliases.set('@weekly', '0 0 0 * * 0 *');
		aliases.set('@daily', '0 0 0 * * * *');
		aliases.set('@hourly', '0 0 * * * * *');
		aliases.set('@minutely', '0 * * * * * *');
		return aliases.get(input);
	}

	/**
	 * Handles Add task response from Daemon API
	 * @param response Daemon API response
	 */
	private handleAddTask(response): void {
		if (response.status === 0) {
			this.successfulSave();
		} else {
			this.$toast.error(
				this.$t('config.daemon.scheduler.messages.processError').toString()
			);
		}
	}

	/**
	 * Handles successful REST API response
	 */
	private successfulSave(): void {
		this.$store.commit('spinner/HIDE');
		if (this.$route.path === '/config/daemon/scheduler/add') {
			this.$toast.success(
				this.$t('config.daemon.scheduler.messages.addSuccess').toString()
			);
		} else {
			this.$toast.success(
				this.$t('config.daemon.scheduler.messages.editSuccess', {task: this.id}).toString()
			);
		}
		this.$router.push('/config/daemon/scheduler/');
	}

}
</script>

<style scoped>
.messages-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
}
</style>
