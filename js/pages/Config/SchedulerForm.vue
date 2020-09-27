<template>
	<CCard>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm>
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
					<CInput
						v-model='timeSpec.cronTime'
						:label='$t("config.scheduler.form.task.cronTime")'
					/>
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
					<div class='form-group'>
						<ValidationProvider
							v-slot='{ errors, touched, valid}'
							rules='required'
							:custom-messages='{required: "config.scheduler.form.messages.service"}'
						>
							<CSelect
								:value.sync='task.messaging'
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
								v-model='task.message'
								v-autogrow
								:label='$t("config.scheduler.form.message.label")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
					</div>
					<CButton color='primary' :disabled='invalid'>
						{{ $t('forms.save') }}
					</CButton>
					<CButton color='primary' :disabled='invalid'>
						{{ $t('forms.saveRestart') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script>
import {CButton, CCard, CCardBody, CForm, CInput, CInputCheckbox, CSelect, CTextarea} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {integer, required} from 'vee-validate/dist/rules';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import SchedulerService from '../../services/SchedulerService';
import {TextareaAutogrowDirective} from 'vue-textarea-autogrow-directive/src/VueTextareaAutogrowDirective';


export default {
	name: 'SchedulerForm',
	components: {
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
			taskId: null,
			clientId: null,
			task: {
				messaging: null,
				message: null,
			},
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
		};
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
						this.task.messaging = rsp.task.messaging;
						this.task.message = JSON.stringify(rsp.task.message, null, 2);
					} else {
						this.$router.push('/config/scheduler/');
						this.$toast.error(this.$t('config.scheduler.messages.getFail', {task: this.task}));
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
						console.log(item);
					});
				});
		}
	},
	metaInfo() {
		return {
			title: this.$route.path === '/config/scheduler/add' ? this.$t('config.scheduler.add') : this.$t('config.scheduler.edit')
		};
	}

};
</script>
