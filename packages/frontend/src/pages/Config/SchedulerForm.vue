<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
		<v-card>
			<v-card-text>
				<ValidationObserver v-slot='{invalid}'>
					<v-form>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|uuidv4'
							:custom-messages='{
								required: $t("config.daemon.scheduler.errors.taskIdMissing"),
								uuidv4: $t("config.daemon.scheduler.errors.taskIdInvalid"),
							}'
						>
							<v-text-field
								v-model='record.taskId'
								:label='$t("config.daemon.scheduler.form.task.taskId")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-text-field
							v-model='record.description'
							:label='$t("config.daemon.scheduler.form.task.description")'
						/>
						<v-select
							v-model='timeSpecSelected'
							:label='$t("config.daemon.scheduler.form.task.timeSpec")'
							:items='timeSpecOptions'
						/>
						<div v-if='timeSpecSelected === TimeSpecTypes.CRON'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='cron|required'
								:custom-messages='{
									cron: $t("config.daemon.scheduler.errors.cron"),
									required: $t("config.daemon.scheduler.errors.cron"),
								}'
							>
								<v-text-field
									v-model='record.timeSpec.cronTime'
									:label='$t("config.daemon.scheduler.form.task.cronTime")'
									:success='touched ? valid : null'
									:error-messages='errors'
									@input='cronMessage = null'
								>
									<template #append>
										<v-chip
											v-if='cronMessage !== null'
											label
											small
											:color='valid ? "info" : "error"'
										>
											{{ cronMessage }}
										</v-chip>
									</template>
								</v-text-field>
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
							<v-text-field
								v-model.number='record.timeSpec.period'
								type='number'
								min='0'
								:label='$t("config.daemon.scheduler.form.task.period")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<DateTimePicker
							v-if='timeSpecSelected === TimeSpecTypes.EXACT'
							:datetime.sync='record.timeSpec.startTime'
							:min-date='new Date().toISOString()'
						/>
						<v-checkbox
							v-model='record.persist'
							:label='$t("config.daemon.scheduler.form.task.persistent")'
							dense
						/>
						<v-checkbox
							v-model='record.enabled'
							:label='$t("config.daemon.scheduler.form.task.enabled")'
							dense
						/>
						<v-divider />
						<v-toolbar
							dense
							flat
						>
							<v-toolbar-title>
								{{ $t('config.daemon.scheduler.form.messages.title') }}
							</v-toolbar-title>
							<v-spacer />
							<v-btn
								color='primary'
								small
								href='https://docs.iqrf.org/iqrf-gateway/user/daemon/api.html'
								target='_blank'
							>
								{{ $t("iqrfnet.sendJson.documentation") }}
							</v-btn>
						</v-toolbar>
						<v-expansion-panels
							class='mb-5'
							accordion
						>
							<v-expansion-panel
								v-for='i of record.task.length'
								:key='i'
							>
								<v-expansion-panel-header class='pt-0 pb-0'>
									{{ $t('config.daemon.scheduler.form.messages.subtitle') }}
									<span class='text-end'>
										<v-btn
											v-if='record.task.length > 1'
											color='error'
											small
											@click.native.stop='removeMessage(i-1)'
										>
											<v-icon small>
												mdi-delete
											</v-icon>
										</v-btn>
										<v-btn
											v-if='i === record.task.length'
											class='ml-1'
											color='success'
											small
											@click.native.stop='addMessage'
										>
											<v-icon small>
												mdi-plus
											</v-icon>
										</v-btn>
									</span>
								</v-expansion-panel-header>
								<v-expansion-panel-content eager>
									<JsonSchemaErrors :errors='validatorErrors[i-1]' />
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required|json|mType'
										:custom-messages='{
											required: $t("config.daemon.scheduler.errors.message"),
											json: $t("iqrfnet.sendJson.messages.invalid"),
											mType: $t("iqrfnet.sendJson.messages.mType"),
										}'
									>
										<v-textarea
											v-model='record.task[i-1].message'
											:label='$t("config.daemon.scheduler.form.messages.label")'
											:success='touched ? valid : null'
											:error-messages='errors'
											rows='1'
											auto-grow
										/>
									</ValidationProvider>
									<ValidationProvider
										v-for='(messaging, j) of record.task[i-1].messaging'
										:key='j'
										v-slot='{errors, touched, valid}'
										rules='required'
										:custom-messages='{
											required: $t("config.daemon.scheduler.errors.service"),
										}'
									>
										<v-select
											v-model='record.task[i-1].messaging[j]'
											:label='$t("config.daemon.scheduler.form.messages.messaging")'
											:placeholder='$t("config.daemon.scheduler.form.messages.messagingPlaceholder")'
											:items='messagings'
											:success='touched ? valid : null'
											:error-messages='errors'
										>
											<template #append-outer>
												<v-btn
													v-if='record.task[i-1].messaging.length > 1'
													color='error'
													small
													@click='removeTaskMessaging(i-1, j)'
												>
													<v-icon small>
														mdi-delete
													</v-icon>
												</v-btn> <v-btn
													v-if='j === (record.task[i-1].messaging.length - 1)'
													class='ml-1'
													color='success'
													small
													@click='addTaskMessaging(i-1)'
												>
													<v-icon small>
														mdi-plus
													</v-icon>
												</v-btn>
											</template>
										</v-select>
									</ValidationProvider>
								</v-expansion-panel-content>
							</v-expansion-panel>
						</v-expansion-panels>
						<v-btn
							color='primary'
							:disabled='invalid || (timeSpecSelected === "exact" && record.timeSpec.startTime === "")'
							@click='saveTask'
						>
							{{ $t('forms.save') }}
						</v-btn>
					</v-form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import DateTimePicker from '@/components/DateTimePicker.vue';
import JsonEditor from '@/components/Config/JsonEditor.vue';
import JsonSchemaErrors from '@/components/Config/JsonSchemaErrors.vue';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import cron from 'cron-validate';
import DaemonApiValidator from '@/helpers/DaemonApiValidator';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';
import {extendedErrorToast} from '@/helpers/errorToast';
import {integer, required, min_value} from 'vee-validate/dist/rules';
import {uuid_v4} from '@/helpers/validators';
import {mType} from '@/helpers/validationRules/Daemon';
import {v4 as uuidv4} from 'uuid';
import SchedulerRecord from '@/helpers/SchedulerRecord';
import {TimeSpecTypes} from '@/enums/Config/Scheduler';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';
import SchedulerService from '@/services/SchedulerService';

import {AxiosError, AxiosResponse} from 'axios';
import {ISchedulerRecord, ISchedulerRecordTask} from '@/interfaces/DaemonApi/Scheduler';
import {IWsMessaging} from '@/interfaces/Config/Messaging';
import {MetaInfo} from 'vue-meta';
import {MutationPayload} from 'vuex';
import { ISelectItem } from '@/interfaces/Vuetify';

@Component({
	components: {
		DateTimePicker,
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
		enabled: true,
	};

	/**
	 * @var {string} cronMessage CRON string conversion message
	 */
	private cronMessage = '';

	/**
	 * @var {Array<ISelectItem>} messagings Array of available messaging component instances
	 */
	private messagings: Array<ISelectItem> = [];

	/**
	 * @var {TimeSpecTypes} timeSpecSelected Selected task time specification type
	 */
	private timeSpecSelected = TimeSpecTypes.EXACT;

	/**
	 * @constant {Array<ISelectItem>} timeSpecOptions Scheduler task time specification options
	 */
	private timeSpecOptions: Array<ISelectItem> = [
		{
			value: TimeSpecTypes.EXACT,
			text: this.$t('config.daemon.scheduler.form.task.timeSpecTypes.exact').toString(),
		},
		{
			value: TimeSpecTypes.PERIODIC,
			text: this.$t('config.daemon.scheduler.form.task.timeSpecTypes.periodic').toString(),
		},
		{
			value: TimeSpecTypes.CRON,
			text: this.$t('config.daemon.scheduler.form.task.timeSpecTypes.cron').toString(),
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
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
		extend('mType', mType);
		extend('uuidv4', uuid_v4);
		extend('json', (json) => {
			return this.validator.validate(json, (errorMessages) => {
				const index = this.record.task.findIndex((task) => task.message === json);
				this.validatorErrors[index] = errorMessages;
			});
		});
		extend('cron', (cronstring: string) => {
			if (cronstring[0] === '@') {
				const expr = SchedulerRecord.resolveExpressionAlias(cronstring);
				if (expr === undefined) {
					this.cronMessage = '';
					return false;
				}
				cronstring = expr;
			}
			const cronObj = cron(cronstring, {preset: SchedulerRecord.cronTraits});
			if (cronObj.isValid()) {
				this.cronMessage = SchedulerRecord.expressionToString(cronstring);
				return true;
			} else {
				this.cronMessage = cronObj.getError().join(',');
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
								value: messaging.instance, text: messaging.instance,
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
		if (this.taskId !== null) {
			record.newTaskId = record.taskId;
			record.taskId = this.taskId;
		} else {
			delete record.newTaskId;
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
}
</script>

<style scoped>
.messages-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
}
</style>
