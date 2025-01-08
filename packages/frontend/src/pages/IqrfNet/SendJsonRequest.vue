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
		<v-card class='mb-5'>
			<v-card-title>
				<v-btn
					color='primary'
					small
					href='https://docs.iqrf.org/iqrf-gateway/user/daemon/api.html'
					target='_blank'
				>
					{{ $t("iqrfnet.sendJson.documentation") }}
				</v-btn>
			</v-card-title>
			<v-card-text>
				<v-overlay
					v-if='!isSocketConnected'
					:opacity='0.65'
					absolute
				>
					{{ $t('iqrfnet.messages.socketError') }}
				</v-overlay>
				<ValidationObserver v-slot='{invalid}' slim>
					<form @submit.prevent='processSubmit'>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|json'
							:custom-messages='{
								required: $t("iqrfnet.sendJson.messages.missing"),
								json: $t("iqrfnet.sendJson.messages.invalid"),
							}'
						>
							<v-textarea
								v-model='json'
								:label='$t("iqrfnet.sendJson.form.json").toString()'
								:success='touched ? valid : null'
								:error-messages='errors'
								rows='1'
								auto-grow
							/>
						</ValidationProvider>
						<v-btn color='primary' type='submit' :disabled='invalid'>
							{{ $t('forms.send') }}
						</v-btn>
					</form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
		<v-card v-if='messages.length !== 0'>
			<v-card-text>
				<v-select
					v-model='activeIdx'
					:label='$t("iqrfnet.sendJson.form.activeMessage").toString()'
					:items='messageOptions'
				/>
				<div v-if='messages.length > 0'>
					<v-row>
						<v-col md='6'>
							<JsonMessage
								:message='messages[activeIdx].request'
								type='request'
								source='sendJson'
							/>
						</v-col>
						<v-col
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
						</v-col>
					</v-row>
				</div>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import JsonMessage from '@/components/IqrfNet/JsonMessage.vue';

import {required} from 'vee-validate/dist/rules';
import {StatusMessages} from '@/iqrfNet/sendJson';
import {v4 as uuidv4} from 'uuid';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import IqrfNetService from '@/services/IqrfNetService';

import {IMessagePairRequest} from '@/interfaces/DaemonApi/Api';
import {ISelectItem} from '@/interfaces/Vuetify';
import {mapGetters, MutationPayload} from 'vuex';

@Component({
	components: {
		JsonMessage,
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
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('json', (json: string) => {
			try {
				JSON.parse(json);
				return true;
			} catch {
				return false;
			}
		});
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
	 * @returns {Array<ISelectItem>} Array of options
	 */
	get messageOptions(): Array<ISelectItem> {
		const options: Array<ISelectItem> = [];
		this.messages.forEach((item: IMessagePairRequest) => {
			options.push({
				text: item.label,
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
			request.mType === 'iqmeshNetwork_OtaUpload') { // requests without timeout
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
