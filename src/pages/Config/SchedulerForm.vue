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
		<h1>{{ $t(pageTitle) }}</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{invalid}'>
					<CForm>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|uuidv4'
							:custom-messages='{
								required: $t("config.daemon.scheduler.errors.taskIdMissing"),
								uuidv4: $t("config.daemon.scheduler.errors.taskIdInvalid"),
							}'
						>
							<CInput
								v-model='record.taskId'
								:label='$t("config.daemon.scheduler.form.task.taskId")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<CInput
							v-model='record.description'
							:label='$t("config.daemon.scheduler.form.task.description")'
						/>
						<CSelect
							:value.sync='timeSpecSelected'
							:label='$t("config.daemon.scheduler.form.task.timeSpec")'
							:options='timeSpecOptions'
						/>
						<div
							v-if='timeSpecSelected === TimeSpecTypes.CRON'
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
								</label>
								<CBadge
									v-if='cronMessage !== null'
									:color='valid ? "info" : "danger"'
								>
									{{ cronMessage }}
								</CBadge>
								<CInput
									id='cronTime'
									v-model='record.timeSpec.cronTime'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
									@input='cronMessage = null'
								/>
							</ValidationProvider>
						</div>
						<ValidationProvider
							v-if='timeSpecSelected === TimeSpecTypes.PERIODIC'
							v-slot='{errors, touched, valid}'
							rules='integer|required|min:0'
							:custom-messages='{
								required: $t("config.daemon.scheduler.errors.period"),
								integer: $t("config.daemon.scheduler.errors.period"),
								min: $t("config.daemon.scheduler.errors.period"),
							}'
						>
							<CInput
								v-model.number='record.timeSpec.period'
								type='number'
								min='0'
								:label='$t("config.daemon.scheduler.form.task.period")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<div
							v-if='timeSpecSelected === TimeSpecTypes.EXACT'
							class='form-group'
						>
							<label for='exactTime'>
								{{ $t("config.daemon.scheduler.form.task.startTime") }}
							</label>
							<Datetime
								id='exactTime'
								v-model='record.timeSpec.startTime'
								type='datetime'
								:format='dateFormat'
								:min-datetime='new Date().toISOString()'
								input-class='form-control'
							/>
						</div>
						<CInputCheckbox
							:checked.sync='record.persist'
							:label='$t("config.daemon.scheduler.form.task.persistent")'
						/>
						<CInputCheckbox
							:checked.sync='record.autoStart'
							:label='$t("config.daemon.scheduler.form.task.autoStart")'
						/>
						<hr>
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
						<div
							v-for='i of record.task.length'
							:key='i'
							class='form-group'
						>
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
											v-model='record.task[i-1].message'
											:label='$t("config.daemon.scheduler.form.messages.label").toString()'
											:is-valid='touched ? valid : null'
											:invalid-feedback='errors.join(", ").toString()'
										/>
									</ValidationProvider>
									<CButton
										v-if='record.task.length > 1'
										class='mr-1'
										color='danger'
										@click='removeMessage(i-1)'
									>
										{{ $t('config.daemon.scheduler.form.messages.remove') }}
									</CButton>
									<CButton
										v-if='i === record.task.length'
										color='success'
										@click='addMessage'
									>
										{{ $t('config.daemon.scheduler.form.messages.add') }}
									</CButton>
								</CCol>
								<CCol md='6'>
									<div
										v-for='(messaging, j) of record.task[i-1].messaging'
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
												:value.sync='record.task[i-1].messaging[j]'
												:label='$t("config.daemon.scheduler.form.messages.messaging")'
												:placeholder='$t("config.daemon.scheduler.form.messages.messagingPlaceholder")'
												:options='messagings'
												:is-valid='touched ? valid : null'
												:invalid-feedback='errors.join(", ")'
											/>
										</ValidationProvider>
										<CButton
											v-if='record.task[i-1].messaging.length > 1'
											class='mr-1'
											color='danger'
											@click='removeTaskMessaging(i-1, j)'
										>
											{{ $t('config.daemon.scheduler.form.messagings.remove') }}
										</CButton>
										<CButton
											v-if='j === (record.task[i-1].messaging.length - 1)'
											color='success'
											@click='addTaskMessaging(i-1)'
										>
											{{ $t('config.daemon.scheduler.form.messagings.add') }}
										</CButton>
									</div>
								</CCol>
							</CRow>
						</div>
						<CButton
							color='primary'
							:disabled='invalid || (timeSpecSelected === "exact" && record.timeSpec.startTime === "")'
							@click='saveTask'
						>
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
import JsonEditor from '@/components/Config/JsonEditor.vue';
import JsonSchemaErrors from '@/components/Config/JsonSchemaErrors.vue';
import {Datetime} from 'vue-datetime';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import cron from 'cron-validate';
import cronstrue from 'cronstrue';
import DaemonApiValidator from '@/helpers/DaemonApiValidator';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';
import {extendedErrorToast} from '@/helpers/errorToast';
import {integer, required, min_value} from 'vee-validate/dist/rules';
import {v4 as uuidv4} from 'uuid';
import SchedulerRecord from '@/helpers/SchedulerRecord';
import {TimeSpecTypes} from '@/enums/Config/Scheduler';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';
import SchedulerService from '@/services/SchedulerService';

import {AxiosError, AxiosResponse} from 'axios';
import {IOption} from '@/interfaces/Coreui';
import {ISchedulerRecord, ISchedulerRecordTask} from '@/interfaces/DaemonApi/Scheduler';
import {IWsMessaging} from '@/interfaces/Config/Messaging';
import {MetaInfo} from 'vue-meta';
import {MutationPayload} from 'vuex';

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
	data: () => ({
		TimeSpecTypes,
	}),
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
	 * @property {string} taskId Id of existing scheduler task
	 */
	@Prop({required: false, default: null}) taskId!: string;

	/**
	 * @var {string} msgId Daemon API message ID
	 */
	private msgId = '';

	/**
	 * @var {string} clientId Scheduler task client ID
	 */
	private clientId = 'SchedulerMessaging';

	/**
	 * @var {string} record Scheduler record
	 */
	private record: ISchedulerRecord = {
		clientId: this.clientId.slice(),
		taskId: uuidv4(),
		newTaskId: '',
		description: '',
		task: [{
			message: '',
			messaging: [''],
		}],
		timeSpec: {
			cronTime: '',
			exactTime: false,
			startTime: '',
			periodic: false,
			period: 1
		},
		persist: true,
		autoStart: true,
	};

	/**
	 * @var {string} cronMessage CRON string conversion message
	 */
	private cronMessage = '';

	/**
	 * @var {Array<IOption>} messagings Array of available messaging component instances
	 */
	private messagings: Array<IOption> = [];

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
	 * @var {boolean} useRest Indicates whether the webapp should use REST API to retrieve scheduler task props
	 */
	private useRest = true;

	/**
	 * @constant {Record<string, string>} components Names of messaging components
	 */
	private components: Record<string, string> = {
		mq: 'iqrf::MqMessaging',
		mqtt: 'iqrf::MqttMessaging',
		websocket: 'iqrf::WebsocketMessaging'
	};

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
	 * @var {DaemonApiValidator} validator JSON schema validator function
	 */
	private validator: DaemonApiValidator;

	/**
	 * @var {Array<Array<string>>} validatorErrors String containing JSON schema violations
	 */
	private validatorErrors: Array<Array<string>> = [[]];

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
				const index = this.record.task.findIndex((task) => task.message === json);
				this.validatorErrors[index] = errorMessages;
			});
		});
		extend('uuidv4', (id: string) => {
			const re = RegExp(/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/);
			return re.test(id);
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
				const cronAlias = SchedulerRecord.resolveExpressionAlias(cronstring);
				if (cronAlias !== undefined) {
					this.cronMessage = cronstrue.toString(cronAlias);
					return true;
				}
				this.cronMessage = '';
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
			if (mutation.type === 'daemonClient/SOCKET_ONCLOSE' ||
				mutation.type === 'daemonClient/SOCKET_ONERROR') {
				this.useRest = true;
			} else if (mutation.type === 'daemonClient/SOCKET_ONMESSAGE') {
				if (mutation.payload.data.msgId !== this.msgId) {
					return;
				}
				this.$store.commit('spinner/HIDE');
				this.$store.dispatch('daemonClient/removeMessage', mutation.payload.data.msgId);
				if (mutation.payload.mType === 'mngScheduler_GetTask') {
					this.handleGetTask(mutation.payload.data);
				} else if (['mngScheduler_AddTask', 'mngScheduler_EditTask'].includes(mutation.payload.mType)) {
					this.handleSaveTask(mutation.payload.data);
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
		if (this.$store.getters['daemonClient/isConnected']) {
			this.useRest = false;
		}
		if (this.taskId) {
			this.getTask(this.taskId);
		}
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Computes page title depending on the action (add, edit)
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$route.path === '/config/daemon/scheduler/add' ?
			'config.daemon.scheduler.add' : 'config.daemon.scheduler.edit';
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
	 * Retrieves task specified by passed id
	 * @param {string} taskId Scheduler task id
	 */
	private getTask(taskId: string): void {
		this.$store.commit('spinner/SHOW');
		if (this.useRest) {
			SchedulerService.getTaskREST(taskId)
				.then((response: AxiosResponse) => {
					this.parseRecord(response.data);
					this.$store.commit('spinner/HIDE');
				})
				.catch((error: AxiosError) => {
					extendedErrorToast(error, 'config.daemon.scheduler.messages.getFail', {task: this.taskId});
					this.$router.push('/config/daemon/scheduler/');
				});
		} else {
			SchedulerService.getTask(taskId, new DaemonMessageOptions(null, 30000, null, () => {
				this.msgId = '';
				this.$router.push('/config/daemon/scheduler/');
			}))
				.then((msgId: string) => this.msgId = msgId);
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
				this.$t('config.daemon.scheduler.messages.getFail', {task: this.taskId}).toString()
			);
			return;
		}
		this.parseRecord(response.rsp);
	}

	/**
	 * Parses record from response
	 * @param {ISchedulerRecord} record Scheduler record
	 */
	private parseRecord(record: ISchedulerRecord): void {
		if (!Array.isArray(record.task)) {
			record.task = [record.task];
		}
		record.task = record.task.map((task: ISchedulerRecordTask) => ({
			message: JSON.stringify(task.message, null, 2),
			messaging: Array.isArray(task.messaging) ? task.messaging : task.messaging.split('&'),
		}));
		if (Array.isArray(record.timeSpec.cronTime)) {
			record.timeSpec.cronTime = record.timeSpec.cronTime.join(' ').trim();
		}
		if (!record.timeSpec.exactTime && !record.timeSpec.periodic) {
			this.timeSpecSelected = TimeSpecTypes.CRON;
		} else if (record.timeSpec.exactTime && !record.timeSpec.periodic) {
			this.timeSpecSelected = TimeSpecTypes.EXACT;
		} else {
			this.timeSpecSelected = TimeSpecTypes.PERIODIC;
		}
		delete record.active;
		for (let i = 0; i < record.task.length; ++i) {
			this.validatorErrors.push([]);
		}
		this.record = record;
	}

	/**
	 * Processes time specification for daemon api and then saves scheduler task
	 */
	private saveTask(): void {
		this.$store.commit('spinner/SHOW');
		const record = SchedulerRecord.prepareRecord(this.timeSpecSelected, JSON.parse(JSON.stringify(this.record)));
		record.newTaskId = this.taskId;
		if (this.taskId && record.taskId != this.taskId) {
			record.taskId = this.taskId;
		}
		if (this.$route.path === '/config/daemon/scheduler/add') {
			if (this.useRest) {
				SchedulerService.addTaskREST(record)
					.then(() => this.successfulSave())
					.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.scheduler.messages.addFailedRest'));
			} else {
				SchedulerService.addTask(record, new DaemonMessageOptions(null, 30000, 'config.daemon.scheduler.messages.processError',  () => this.msgId = ''))
					.then((msgId: string) => this.msgId = msgId);
			}
		} else {
			if (this.useRest) {
				SchedulerService.editTaskREST(record)
					.then(() => this.successfulSave())
					.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.scheduler.messages.editFailedRest'));
			} else {
				SchedulerService.editTask(record, new DaemonMessageOptions(null, 30000, 'config.daemon.scheduler.messages.processError', () => this.msgId = ''))
					.then((msgId: string) => this.msgId = msgId);
			}
		}
	}

	/**
	 * Handles Add task response from Daemon API
	 * @param response Daemon API response
	 */
	private handleSaveTask(response): void {
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
				this.$t('config.daemon.scheduler.messages.editSuccess', {task: this.taskId}).toString()
			);
		}
		this.$router.push('/config/daemon/scheduler/');
	}

	/**
	 * Adds another scheduler task message object
	 */
	private addMessage(): void {
		this.record.task.push({message: '', messaging: ['']});
		this.validatorErrors.push([]);
	}

	/**
	 * Adds another scheduler task messaging
	 * @param {number} index Task index
	 */
	private addTaskMessaging(index: number): void {
		(this.record.task[index].messaging as Array<string>).push('');
	}

	/**
	 * Removes a scheduler task message object specified by index
	 * @param {number} index Index of scheduler task message
	 */
	private removeMessage(index: number): void {
		this.record.task.splice(index, 1);
		this.validatorErrors.splice(index, 1);
	}

	/**
	 * Removes a scheduler task messaging specified
	 * @param {number} tIndex Task index
	 * @param {number} mIndex Messaging index
	 */
	private removeTaskMessaging(tIndex: number, mIndex: number): void {
		(this.record.task[tIndex].messaging as Array<string>).splice(mIndex, 1);
	}

	/**
	 * Converts cron time expression into a human readable message
	 * @param {string} cronExpression Cron time expression
	 */
	private calculateCron(cronExpression: string): void {
		const cronTime = cronExpression.trim().split(' ');
		const len = cronTime.length;
		if (len === 1) {
			const alias = SchedulerRecord.resolveExpressionAlias((this.record.timeSpec.cronTime as string));
			if (alias !== undefined) {
				this.cronMessage = cronstrue.toString(alias);
			} else {
				this.cronMessage = '';
			}
		} else if (len > 4 && len < 8) {
			this.cronMessage = cronstrue.toString((this.record.timeSpec.cronTime as string));
		} else {
			this.cronMessage = '';
		}
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
