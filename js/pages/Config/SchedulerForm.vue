<template>
	<CCard>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm @submit.prevent='saveTask'>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='integer|required'
						:custom-messages='{
							required: "config.scheduler.form.messages.nums",
							integer: "config.scheduler.form.messages.nums"
						}'
					>
						<CInput
							v-model.number='taskId'
							type='number'
							:label='$t("config.scheduler.form.task.taskId")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid}'
						rules='required'
						:custom-messages='{required: "config.scheduler.form.messages.service"}'
					>
						<CSelect
							:value.sync='clientId'
							:label='$t("config.scheduler.table.service")'
							:options='[
								{value: "SchedulerMessaging", label: "SchedulerMessaging"}
							]'
							placeholder='Select service'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<div class='form-group'>
						<label for='cronTime'>{{ $t("config.scheduler.form.task.cronTime") }}</label>
						<CBadge v-if='cronMessage !== null' color='info'>
							{{ cronMessage }}
						</CBadge>
						<CInput
							id='cronTime'
							v-model='timeSpec.cronTime'
							@input='calculateCron'
							@change='calculateCron'
						/>
					</div>
					<CInputCheckbox
						:checked.sync='timeSpec.exactTime'
						:label='$t("config.scheduler.form.task.exactTime")'
					/>
					<CInputCheckbox
						:checked.sync='timeSpec.periodic'
						:label='$t("config.scheduler.form.task.periodic")'
					/>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='integer|required'
						:custom-messages='{
							required: "config.scheduler.form.messages.nums",
							integer: "config.scheduler.form.messages.nums"
						}'
					>
						<CInput
							v-model.number='timeSpec.period'
							type='number'
							min='0'
							:label='$t("config.scheduler.form.task.period")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CInput
						v-model='timeSpec.startTime'
						:label='$t("config.scheduler.form.task.startTime")'
					/>
					<h3>{{ $t('config.scheduler.form.message.title') }}</h3><hr>
					<div v-for='i of task.length' :key='i' class='form-group'>
						<ValidationProvider
							v-slot='{ errors, touched, valid}'
							rules='required'
							:custom-messages='{required: "config.scheduler.form.messages.service"}'
						>
							<CSelect
								:value.sync='task[i-1].messaging'
								:placeholder='$t("config.scheduler.form.message.messagePlaceholder")'
								:options='messagings'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required|json|mType'
							:custom-messages='{
								required: "config.scheduler.form.messages.message",
								json: "iqrfnet.sendJson.form.messages.invalid",
								mType: "iqrfnet.sendJson.form.messages.mType"
							}'
						>
							<CTextarea
								v-model='task[i-1].message'
								v-autogrow
								:label='$t("config.scheduler.form.message.label")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CButton v-if='task.length > 1' color='danger' @click='removeMessage(i-1)'>
							{{ $t('config.scheduler.buttons.remove') }}
						</CButton>
						<CButton v-if='i === task.length' color='success' @click='addMessage'>
							{{ $t('config.scheduler.buttons.add') }}
						</CButton>
					</div>
					<CButton type='submit' color='primary' :disabled='invalid'>
						{{ $t('forms.save') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script>
import {CBadge, CButton, CCard, CCardBody, CForm, CInput, CInputCheckbox, CSelect, CTextarea} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {integer, required} from 'vee-validate/dist/rules';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import SchedulerService from '../../services/SchedulerService';
import {TextareaAutogrowDirective} from 'vue-textarea-autogrow-directive/src/VueTextareaAutogrowDirective';
import cronstrue from 'cronstrue';

export default {
	name: 'SchedulerForm',
	components: {
		CBadge,
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		CInputCheckbox,
		CSelect,
		CTextarea,
		ValidationObserver,
		ValidationProvider
	},
	directives: {
		'autogrow': TextareaAutogrowDirective
	},
	props: {
		id: {
			type: Number,
			required: false,
			default: null,
		},
	},
	data() {
		return {
			taskId: Date.now(),
			clientId: null,
			task: [{}],
			timeSpec: {
				cronTime: '',
				periodic: false,
				period: 0,
				exactTime: false,
				startTime: '',
			},
			components: {
				mq: 'iqrf::MqMessaging',
				mqtt: 'iqrf::MqttMessaging',
				websocket: 'iqrf::WebsocketMessaging',
			},
			messagings: [],
			cronMessage: null,
		};
	},
	computed: {
		cronTime() {
			return 2;
		},
	},
	created() {
		extend('integer', integer);
		extend('required', required);
		extend('json', (json) => {
			try {
				JSON.parse(json);
				return true;
			} catch (error) {
				return false;
			}
		});
		extend('mType', (json) => {
			let object = JSON.parse(json);
			return {}.hasOwnProperty.call(object, 'mType');
		});
		extend('cron', (cronTime) => {
			//
		});
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONOPEN') {
				if (this.id) {
					this.getTask(this.id);
					return;
				}
			}
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (mutation.payload.mType === 'mngScheduler_GetTask') {
					this.$store.commit('spinner/HIDE');
					if (mutation.payload.data.status === 0) {
						let rsp = mutation.payload.data.rsp;
						this.taskId = rsp.taskId;
						this.clientId = rsp.clientId;
						this.timeSpec = rsp.timeSpec;
						let timeString = '';
						this.timeSpec.cronTime.forEach(item => {
							timeString += item + ' ';
						});
						this.timeSpec.cronTime = timeString.trim();
						if (Array.isArray(rsp.task)) {
							let tasks = [];
							rsp.task.forEach(item => {
								tasks.push({messaging: item.messaging, message: JSON.stringify(item.message, null, 2)});
							});
							this.task = tasks;
						} else {
							this.task = [{messaging: rsp.task.messaging, message: JSON.stringify(rsp.task.message, null, 2)}];
						}
					} else {
						this.$router.push('/config/scheduler/');
						this.$toast.error(this.$t('config.scheduler.messages.getFail', {task: this.task}));
					}
				} else if (mutation.payload.mType === 'mngScheduler_AddTask') {
					this.$store.commit('spinner/HIDE');
					if (mutation.payload.data.status === 0) {
						this.$router.push('/config/scheduler/');
						this.$toast.success(this.$t('config.scheduler.messages.addSuccess').toString());
					}
				} else if (mutation.payload.mType === 'messageError') {
					this.$store.commit('spinner/HIDE');
				}
			}
		});
		this.getMessagings();
	},
	beforeDestroy() {
		this.unsubscribe();
	},
	methods: {
		calculateCron() {
			this.cronMessage = cronstrue.toString(this.timeSpec.cronTime);
		},
		addMessage() {
			this.task.push({});
		},
		removeMessage(index) {
			this.task.splice(index, 1);
		},
		getTask(taskId) {
			this.$store.commit('spinner/SHOW');
			SchedulerService.getTask(taskId);
		},
		getMessagings() {
			this.$store.commit('spinner/SHOW');
			Promise.all([
				DaemonConfigurationService.getComponent(this.components.mq),
				DaemonConfigurationService.getComponent(this.components.mqtt),
				DaemonConfigurationService.getComponent(this.components.websocket),
			])
				.then((responses) => {
					this.$store.commit('spinner/HIDE');
					responses.forEach(item => {
						if (item.data.instances) {
							item.data.instances.forEach(messaging => {
								this.messagings.push({value: messaging.instance, label: messaging.instance});
							});
						}
					});
				});
		},
		saveTask() {
			this.$store.commit('spinner/SHOW');
			let task = null;
			if (this.task.length === 1) {
				task = this.task[0];
			} else {
				task = this.task;
			}
			let timeSpec = JSON.parse(JSON.stringify(this.timeSpec));
			timeSpec.cronTime = timeSpec.cronTime.split(' ');
			if (this.$route.path === '/config/scheduler/add') {
				SchedulerService.addTask(this.taskId, this.clientId, task, timeSpec);
			} else {
				SchedulerService.removeTask(this.id);
				SchedulerService.addTask(this.taskId, this.clientId, task, timeSpec);
			}
		}
	},
	metaInfo() {
		return {
			title: this.$route.path === '/config/scheduler/add' ? this.$t('config.scheduler.add') : this.$t('config.scheduler.edit')
		};
	}

};
</script>
