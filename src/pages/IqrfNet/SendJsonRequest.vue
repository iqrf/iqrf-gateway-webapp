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
		<h1>{{ $t('iqrfnet.sendJson.title') }}</h1>
		<CCard>
			<CCardHeader>
				<CButton
					color='primary'
					size='sm'
					href='https://docs.iqrf.org/iqrf-gateway/daemon-api.html'
					target='_blank'
				>
					{{ $t("iqrfnet.sendJson.documentation") }}
				</CButton>
			</CCardHeader>
			<CCardBody>
				<JsonSchemaErrors :errors='validatorErrors' />
				<CElementCover
					v-if='!isSocketConnected'
					style='z-index: 1;'
					:opacity='0.85'
				>
					{{ $t('iqrfnet.messages.socketError') }}
				</CElementCover>
				<ValidationObserver v-slot='{invalid}' slim>
					<CForm @submit.prevent='processSubmit'>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|json'
							:custom-messages='{
								required: $t("iqrfnet.sendJson.messages.missing"),
								json: $t("iqrfnet.sendJson.messages.invalid"),
							}'
							slim
						>
							<JsonEditor
								v-model='json'
								:label='$t("iqrfnet.sendJson.form.json").toString()'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
								@blur='$emit("blur", $event)'
							/>
						</ValidationProvider>
						<CButton color='primary' type='submit' :disabled='invalid'>
							{{ $t('forms.send') }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
		<CCard
			v-if='messages.length !== 0'
			body-wrapper
		>
			<CSelect
				:value.sync='activeIdx'
				:label='$t("iqrfnet.sendJson.form.activeMessage").toString()'
				:options='messageOptions'
			/>
			<div v-if='messages.length > 0'>
				<CRow>
					<CCol md='6'>
						<JsonMessage
							:message='messages[activeIdx].request'
							type='request'
							source='sendJson'
						/>
					</CCol>
					<CCol
						v-if='messages[activeIdx].response.length > 0'
						md='6'
					>
						<JsonMessage
							v-for='(rsp, i) of messages[activeIdx].response'
							:key='i'
							:message='rsp'
							type='response'
							source='sendJson'
						/>
					</CCol>
				</CRow>
			</div>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CElementCover, CForm, CTextarea} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import JsonEditor from '@/components/Config/JsonEditor.vue';
import JsonMessage from '@/components/IqrfNet/JsonMessage.vue';
import JsonSchemaErrors from '@/components/Config/JsonSchemaErrors.vue';

import {required} from 'vee-validate/dist/rules';
import {StatusMessages} from '@/iqrfNet/sendJson';
import {v4 as uuidv4} from 'uuid';

import IqrfNetService from '@/services/IqrfNetService';

import {IMessagePairRequest} from '@/interfaces/DaemonApi/Api';
import {IOption} from '@/interfaces/Coreui';
import {mapGetters, MutationPayload} from 'vuex';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';
import DaemonApiValidator from '@/helpers/DaemonApiValidator';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CElementCover,
		CForm,
		CTextarea,
		JsonEditor,
		JsonMessage,
		JsonSchemaErrors,
		ValidationObserver,
		ValidationProvider,
	},
	computed: {
		...mapGetters({
			isSocketConnected: 'daemonClient/isConnected',
		}),
	},
	metaInfo: {
		title: 'iqrfnet.sendJson.title',
	},
})

/**
 * Send daemon JSON API message page component
 */
export default class SendJsonRequest extends Vue {

	/**
	 * @var {string|null} json Daemon API JSON message
	 */
	private json: string|null = null;

	/**
	 * @var {string|null} msgId Daemon API message ID
	 */
	private msgId: string|null = null;

	/**
	 * @var {number} activeIdx Index of active message pair
	 */
	private activeIdx = 0;

	/**
	 * @var {Array<IMessagePair>} messages Array of Daemon API request and response pairs
	 */
	private messages: Array<IMessagePairRequest> = [];

	/**
	 * @var {DaemonApiValidator} validator JSON schema validator function
	 */
	private validator: DaemonApiValidator;

	/**
	 * @var {Array<string>} validatorErrors String containing JSON schema violations
	 */
	private validatorErrors: Array<string> = [];

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Component constructor
	 */
	constructor() {
		super();
		this.validator = new DaemonApiValidator();
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('json', (json) => this.validator.validate(json, (errorMessages) => this.validatorErrors = errorMessages));
		extend('required', required);
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'daemonClient/SOCKET_ONCLOSE' || mutation.type === 'daemonClient/SOCKET_ONERROR') {
				this.$store.commit('spinner/HIDE');
				this.$store.dispatch('daemonClient/removeMessage', this.msgId);
				return;
			}
			if (mutation.type === 'daemonClient/SOCKET_ONSEND') {
				this.messages.unshift({
					msgId: mutation.payload.data.msgId,
					request: JSON.stringify(mutation.payload, null, 4),
					response: [],
					label: '[' + new Date().toLocaleString() + ']: ' + mutation.payload.mType + ' (' + mutation.payload.data.msgId + ')',
				});
				this.activeIdx = 0;
			}
			if (mutation.type !== 'daemonClient/SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			if (mutation.payload.mType === 'messageError') {
				this.$store.dispatch('daemonClient/removeMessage', this.msgId);
				this.handleMessageError(mutation.payload);
			} else if (mutation.payload.mType === 'iqmeshNetwork_AutoNetwork') {
				this.handleAutoNetworkResponse(mutation.payload);
			} else if (mutation.payload.mType === 'iqmeshNetwork_Backup') {
				this.handleBackup(mutation.payload);
			} else if (mutation.payload.mType === 'infoDaemon_Enumeration' && mutation.payload.data.rsp.command === 'now') {
				this.handleEnumerationNow(mutation.payload);
			} else {
				this.$store.dispatch('daemonClient/removeMessage', this.msgId);
				this.handleResponse(mutation.payload);
			}
		});
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Generates array of message options to select from to show
	 * @returns {Array<IOption>} Array of options
	 */
	get messageOptions(): Array<IOption> {
		const options: Array<IOption> = [];
		this.messages.forEach((item: IMessagePairRequest) => {
			options.push({
				label: item.label,
				value: this.messages.indexOf(item),
			});
		});
		return options;
	}

	/**
	 * Sends daemon api json message
	 */
	private processSubmit(): void {
		if (this.json === null) {
			return;
		}
		const json = JSON.parse(this.json);
		if (json.data.msgId === undefined) {
			Object.assign(json.data, {msgId: uuidv4()});
		}
		this.sendRequest(json);
	}

	/**
	 * Sets request options and sends Daemon API request
	 * @param request Daemon API request
	 */
	private sendRequest(request): void {
		const options = new DaemonMessageOptions(request);
		if ({}.hasOwnProperty.call(request.data, 'req') && {}.hasOwnProperty.call(request.data.req, 'nAdr') && request.data.req.nAdr === 255) { // if a message is broadcasted, do not wait for proper response
			options.timeout = 1000;
		} else if (request.mType === 'iqrfEmbedOs_Batch' || request.mType === 'iqrfEmbedOs_SelectiveBatch') { // batch and selective batch requests do not have proper responses, do not wait
			options.timeout = 1000;
		} else if (request.mType === 'iqmeshNetwork_AutoNetwork' ||
			request.mType === 'iqmeshNetwork_Backup' ||
			(request.mType === 'infoDaemon_Enumeration' && request.data.req.command === 'now') ||
			request.mType === 'otaUpload') { // requests without timeout
			this.$store.commit('spinner/SHOW');
		} else { // regular messages have a minute timeout
			options.timeout = 60000;
			options.message = 'iqrfnet.sendJson.messages.error.fail';
			this.$store.commit('spinner/SHOW');
		}
		options.callback = () => this.msgId = null;
		IqrfNetService.sendJson(options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles messageError Daemon API response
	 * @param response Daemon API response
	 */
	private handleMessageError(response): void {
		const idx = this.messages.findIndex((item: IMessagePairRequest) => item.msgId === response.data.msgId);
		if (idx !== -1) {
			this.messages[idx].response.push(JSON.stringify(response, null, 4));
		}
		this.$store.commit('spinner/HIDE');
		if (response.data.rsp.errorStr.includes('daemon overload')) { // daemon queue is full
			this.$toast.error(
				this.$t('iqrfnet.sendJson.messages.error.messageQueueFull').toString()
			);
			return;
		}
		this.$toast.error(
			this.$t('iqrfnet.sendJson.messages.error.invalidMessage').toString()
		);
	}

	/**
	 * Handles AutoNetwork Daemon API response
	 * @param response Daemon API response
	 */
	private handleAutoNetworkResponse(response): void {
		const idx = this.messages.findIndex((item: IMessagePairRequest) => item.msgId === response.data.msgId);
		if (idx !== -1) {
			this.messages[idx].response.push(JSON.stringify(response, null, 4));
		}
		if (response.data.rsp.lastWave && response.data.rsp.progress === 100) { // autonetwork finished
			this.$store.commit('spinner/HIDE');
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			this.$toast.info(
				this.$t('iqrfnet.sendJson.messages.autoNetworkFinish').toString()
			);
		}
	}

	/**
	 * Handles Backup Daemon API response
	 * @param response Daemon API response
	 */
	private handleBackup(response): void {
		const idx = this.messages.findIndex((item: IMessagePairRequest) => item.msgId === response.data.msgId);
		if (idx !== -1) {
			this.messages[idx].response.push(JSON.stringify(response, null, 4));
		}
		if (response.data.rsp.progress === 100) {
			this.$store.commit('spinner/HIDE');
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			this.$toast.info(
				this.$t('iqrfnet.sendJson.messages.backupFinish').toString()
			);
		}
	}

	/**
	 * Handles Enumeration now Daemon API response
	 * @param response Daemon API response
	 */
	private handleEnumerationNow(response): void {
		const idx = this.messages.findIndex((item: IMessagePairRequest) => item.msgId === response.data.msgId);
		if (idx !== -1) {
			this.messages[idx].response.push(JSON.stringify(response, null, 4));
		}
		if (response.data.rsp.percentage === 100) { // enumeration finished
			this.$store.commit('spinner/HIDE');
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			this.$toast.info(
				this.$t('iqrfnet.sendJson.messages.enumerationFinish').toString()
			);
		}
	}

	/**
	 * Handles other Daemon API responses
	 * @param response Daemon API response
	 */
	private handleResponse(response): void {
		const idx = this.messages.findIndex((item: IMessagePairRequest) => item.msgId === response.data.msgId);
		if (idx !== -1) {
			this.messages[idx].response.push(JSON.stringify(response, null, 4));
		}
		this.$store.commit('spinner/HIDE');
		if (response.data.status === 0) {
			this.$toast.success(
				this.$t('iqrfnet.sendJson.messages.success').toString()
			);
			return;
		}
		if (response.data.status in StatusMessages) {
			this.$toast.error(
				this.$t(StatusMessages[response.data.status]).toString()
			);
			return;
		}
		this.$toast.error(
			this.$t('iqrfnet.sendJson.messages.error.fail').toString()
		);
	}

}
</script>
