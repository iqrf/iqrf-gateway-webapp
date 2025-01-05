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
		<h1>{{ $t('iqrfnet.sendPacket.title') }}</h1>
		<CCard body-wrapper>
			<CElementCover
				v-if='!isSocketConnected'
				style='z-index: 1;'
				:opacity='0.85'
			>
				{{ $t('iqrfnet.messages.socketError') }}
			</CElementCover>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='handleSubmit'>
					<CRow>
						<CCol md='6'>
							<ValidationProvider
								v-slot='{valid, touched, errors}'
								rules='nadr|minLen:2|maxLen:2|required'
								:custom-messages='{
									nadr: $t("iqrfnet.sendPacket.form.messages.invalid.nadr"),
									minLen: $t("iqrfnet.sendPacket.form.messages.invalid.nadr"),
									maxLen: $t("iqrfnet.sendPacket.form.messages.invalid.nadr"),
									required: $t("iqrfnet.sendPacket.form.messages.invalid.nadr"),
								}'
							>
								<CInput
									v-model='packetNadr'
									maxlength='4'
									:label='$t("iqrfnet.sendPacket.form.nadr")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
									:disabled='addressOverwrite'
									class='dpa-section'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{valid, touched, errors}'
								rules='pnum|minLen:2|maxLen:2|required'
								:custom-messages='{
									pnum: $t("iqrfnet.sendPacket.form.messages.invalid.pnum"),
									minLen: $t("iqrfnet.sendPacket.form.messages.invalid.pnum"),
									maxLen: $t("iqrfnet.sendPacket.form.messages.invalid.pnum"),
									required: $t("iqrfnet.sendPacket.form.messages.invalid.pnum"),
								}'
							>
								<CInput
									v-model='packetPnum'
									:label='$t("iqrfnet.sendPacket.form.pnum")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
									class='dpa-section'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{valid, touched, errors}'
								rules='pcmd|minLen:2|maxLen:2|required'
								:custom-messages='{
									pcmd: $t("iqrfnet.sendPacket.form.messages.invalid.pcmd"),
									minLen: $t("iqrfnet.sendPacket.form.messages.invalid.pcmd"),
									maxLen: $t("iqrfnet.sendPacket.form.messages.invalid.pcmd"),
									required: $t("iqrfnet.sendPacket.form.messages.invalid.pcmd"),
								}'
							>
								<CInput
									v-model='packetPcmd'
									:label='$t("iqrfnet.sendPacket.form.pcmd")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
									class='dpa-section'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{valid, touched, errors}'
								rules='hwpid|minLen:4|maxLen:4|required'
								:custom-messages='{
									hwpid: $t("iqrfnet.sendPacket.form.messages.invalid.hwpid"),
									minLen: $t("iqrfnet.sendPacket.form.messages.invalid.hwpid"),
									maxLen: $t("iqrfnet.sendPacket.form.messages.invalid.hwpid"),
									required: $t("iqrfnet.sendPacket.form.messages.invalid.hwpid"),
								}'
							>
								<CInput
									v-model='packetHwpid'
									:label='$t("iqrfnet.sendPacket.form.hwpid")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
									class='dpa-section'
								/>
							</ValidationProvider>
						</CCol>
						<CCol md='6'>
							<ValidationProvider
								v-slot='{valid, touched, errors}'
								rules='pdata'
								:custom-messages='{
									pdata: $t("iqrfnet.sendPacket.form.messages.invalid.pdata"),
								}'
							>
								<CInput
									v-model='packetPdata'
									v-maska='{mask: generateMask, tokens: {"H": {pattern: /[0-9a-fA-F]/}}}'
									:label='$t("iqrfnet.sendPacket.form.pdata")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
								/>
							</ValidationProvider>
						</CCol>
					</CRow>
					<CRow>
						<CCol md='6'>
							<CInputCheckbox
								:checked.sync='addressOverwrite'
								:label='$t("iqrfnet.sendPacket.form.addressOverwrite")'
							/>
							<ValidationProvider
								v-slot='{valid, touched, errors}'
								:disabled='!addressOverwrite'
								:rules='addressOverwrite ? "integer|between:0,239|required" : ""'
								:custom-messages='{
									between: $t("iqrfnet.sendPacket.form.messages.invalid.address"),
									integer: $t("iqrfnet.sendPacket.form.messages.invalid.address"),
									required: $t("iqrfnet.sendPacket.form.messages.missing.address"),
								}'
							>
								<CInput
									v-model.number='address'
									:disabled='!addressOverwrite'
									:label='$t("iqrfnet.sendPacket.form.address")'
									:is-valid='addressOverwrite && touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
									type='number'
									min='0'
									max='239'
								/>
							</ValidationProvider>
						</CCol>
						<CCol md='6'>
							<CInputCheckbox
								:checked.sync='timeoutOverwrite'
								:label='$t("iqrfnet.sendPacket.form.timeoutOverwrite")'
							/>
							<ValidationProvider
								v-slot='{valid, touched, errors}'
								:rules='timeoutOverwrite ? "integer|min:1000|required" : ""'
								:custom-messages='{
									integer: $t("iqrfnet.sendPacket.form.messages.invalid.timeout"),
									min: $t("iqrfnet.sendPacket.form.messages.invalid.timeout"),
									required: $t("iqrfnet.sendPacket.form.messages.missing.timeout"),
								}'
							>
								<CInput
									v-model.number='timeout'
									min='1000'
									:disabled='!timeoutOverwrite'
									:label='$t("iqrfnet.sendPacket.form.timeout")'
									:is-valid='timeoutOverwrite && touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
									type='number'
								/>
							</ValidationProvider>
						</CCol>
					</CRow>
					<CButton color='primary' type='submit' :disabled='invalid'>
						{{ $t('forms.send') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCard>
		<DpaMacros @set-packet='setPacket($event)' />
		<CCard
			v-if='messages.length !== 0'
			body-wrapper
		>
			<CSelect
				:value.sync='activeIdx'
				:label='$t("iqrfnet.sendPacket.form.activeMessage")'
				:options='messageOptions'
				@change='activeMessagePair = messages[activeIdx]'
			/>
			<div v-if='activeMessagePair !== null'>
				<CRow>
					<CCol md='6'>
						<JsonMessage
							:message='activeMessagePair.request'
							type='request'
							source='sendDpa'
						/>
					</CCol>
					<CCol md='6'>
						<JsonMessage
							v-if='activeMessagePair.response !== undefined'
							:message='activeMessagePair.response'
							type='response'
							source='sendDpa'
						/>
					</CCol>
				</CRow>
			</div>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CElementCover, CForm, CInput, CInputCheckbox, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import DpaMacros from '@/components/IqrfNet/DpaMacros.vue';
import JsonMessage from '@/components/IqrfNet/JsonMessage.vue';

import {maska} from 'maska';
import {between, integer, min_value, required, min, max} from 'vee-validate/dist/rules';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';
import Packet from '@/iqrfNet/sendPacket';

import {IMessagePairPacket} from '@/interfaces/DaemonApi/Api';
import {IOption} from '@/interfaces/Coreui';
import {mapGetters, MutationPayload} from 'vuex';
import {RawMessage} from '@/interfaces/DaemonApi/Dpa';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CElementCover,
		CForm,
		CInput,
		CInputCheckbox,
		CSelect,
		DpaMacros,
		JsonMessage,
		ValidationObserver,
		ValidationProvider,
	},
	computed: {
		...mapGetters({
			isSocketConnected: 'daemonClient/isConnected',
		}),
	},
	directives: {
		'maska': maska,
	},
	metaInfo: {
		title: 'iqrfnet.sendPacket.title',
	}
})

/**
 * Send Raw DPA packet page component
 */
export default class SendDpaPacket extends Vue {
	/**
	 * @var {number} address Default device address
	 */
	private address = 0;

	/**
	 * @var {boolean} addressOverwrite Controls whether packet address bytes should be overwritten
	 */
	private addressOverwrite = false;

	/**
	 * @var {string} packetNadr Packet NADR bytes
	 */
	private packetNadr = '00';

	/**
	 * @var {string} packetPnum Packet PNUM byte
	 */
	private packetPnum = '00';

	/**
	 * @var {string} packetPcmd Packet PCMD byte
 	 */
	private packetPcmd = '00';

	/**
	 * @var {string} packetHwpid Packet HWPID bytes
	 */
	private packetHwpid = 'ffff';

	/**
	 * @var {string} packetPdata Packet PDATA bytes
	 */
	private packetPdata = '';

	/**
	 * @var {number} timeout Default Daemon API message timeout
	 */
	private timeout = 1000;

	/**
	 * @var {boolean} timeoutOverwrite Controls whether default Daemon API message timeout should be overwritten
	 */
	private timeoutOverwrite = false;

	/**
	 * @var {IMessagePair|null} activeMessagePair Currently shown message pair
	 */
	private activeMessagePair: IMessagePairPacket|null = null;

	/**
	 * @var {number} activeIdx Index of active message pair
	 */
	private activeIdx = 0;

	/**
	 * @var {Array<IMessagePair>} messages Array of Daemon API request and response pairs
	 */
	private messages: Array<IMessagePairPacket> = [];

	/**
	 * @var {string|null} msgId Daemon API message ID
	 */
	private msgId: string|null = null;

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Generates array of message options to select from to show
	 * @returns {Array<IOption>} Array of options
	 */
	get messageOptions(): Array<IOption> {
		const options: Array<IOption> = [];
		this.messages.forEach((item: IMessagePairPacket) => {
			options.push({
				label: item.label,
				value: this.messages.indexOf(item),
			});
		});
		return options;
	}

	/**
	 * Computes packet string from packet parts
	 * @returns {string} Packet string
	 */
	get packet(): string {
		let packet = this.packetNadr + '.00.';
		packet += this.packetPnum + '.' + this.packetPcmd + '.';
		packet += this.packetHwpid.substring(0, 2) + '.' + this.packetHwpid.substring(2, 4) + '.' + this.packetPdata;
		return packet;
	}

	/**
	 * Generates packet pdata pmask
	 * @returns {string} packet pdata mask
	 */
	get generateMask(): string {
		return 'HH.'.repeat(56) + 'HH';
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('min', min_value);
		extend('minLen', min);
		extend('maxLen', max);
		extend('required', required);
		extend('nadr', (nadr: string) => {
			const re = /^[0-9a-f]{2}$/i;
			return re.test(nadr);
		});
		extend('pnum', (pnum: string) => {
			const re = /^[0-9a-f]{2}$/i;
			return re.test(pnum);
		});
		extend('pcmd', (pcmd: string) => {
			const re = /^[0-7][0-9a-f]$/i;
			return re.test(pcmd);
		});
		extend('hwpid', (hwpid: string) => {
			const re = /^[0-9a-f]{4}$/i;
			return re.test(hwpid);
		});
		extend('pdata', (pdata: string) => {
			const re = /^([0-9a-f]{2}.){0,56}[0-9a-f]{2}(.|)$/i;
			return re.test(pdata);
		});
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'daemonClient/SOCKET_ONSEND' && mutation.payload.mType === 'iqrfRaw') {
				this.messages.unshift({
					msgId: mutation.payload.data.msgId,
					request: JSON.stringify(mutation.payload, null, 4),
					response: undefined,
					label: '[' + new Date().toLocaleString() + ']: ' + Packet.parse(this.packet).toString(false) + ' (' + mutation.payload.data.msgId + ')',
				});
				this.activeMessagePair = this.messages[0];
			}
			if (mutation.type !== 'daemonClient/SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			this.$store.commit('spinner/HIDE');
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			if (mutation.payload.mType === 'messageError') {
				this.handleMessageError(mutation.payload);
				return;
			}
			if (mutation.payload.mType === 'iqrfRaw') {
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
	 * Handles Send DPA packet form submit event
	 */
	private handleSubmit(): void {
		if (this.packet === '') {
			this.$toast.error(this.$t('iqrfnet.sendPacket.form.messages.missing.packet').toString());
			return;
		}
		const json: RawMessage = {
			'mType': 'iqrfRaw',
			'data': {
				'req': {
					'rData': this.packet,
				},
				'returnVerbose': true,
			},
		};
		if (this.addressOverwrite) {
			json.data.req.rData = Packet.updateNadr(this.packet, this.address);
		}
		if (this.timeoutOverwrite) {
			json.data.timeout = this.timeout;
		}
		const options = new DaemonMessageOptions(json);
		const packet = Packet.parse(this.packet);
		if (packet.nadr === 255) {
			options.timeout = 1000;
		} else if (packet.pnum === 2 && (packet.pcmd === 5 || packet.pcmd === 11)) {
			options.timeout = 1000;
		} else {
			options.timeout = 60000;
			options.message = 'iqrfnet.sendPacket.messages.failure';
			this.$store.commit('spinner/SHOW');
		}
		this.$store.dispatch('daemonClient/sendRequest', options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles Daemon API messageError response
	 */
	private handleMessageError(response): void {
		const idx = this.messages.findIndex((item: IMessagePairPacket) => item.msgId === response.data.msgId);
		if (idx !== -1) {
			this.messages[idx].response = JSON.stringify(response, null, 4);
		}
		this.$store.dispatch('daemonClient/removeMessage', response.data.msgId);
		this.$store.commit('daemonClient/TRIM_MESSAGE_QUEUE');
		this.$toast.clear();
		this.$toast.error(
			this.$t('iqrfnet.sendPacket.messages.queueFull').toString()
		);
	}

	/**
	 * Handles Daemon API Raw message responses
	 * @param response Daemon API response payload
	 */
	private handleResponse(response): void {
		const idx = this.messages.findIndex((item: IMessagePairPacket) => item.msgId === response.data.msgId);
		if (idx !== -1) {
			this.messages[idx].response = JSON.stringify(response, null, 4);
		}
		let message: string;
		let error = true;
		switch (response.data.status) {
			case 0:
				message = this.$t('iqrfnet.sendPacket.messages.success').toString();
				error = false;
				break;
			case 2:
				message = this.$t('iqrfnet.sendPacket.messages.incorrect.pcmd').toString();
				break;
			case 3:
				message = this.$t('iqrfnet.sendPacket.messages.incorrect.pnum').toString();
				break;
			case 5:
				message = this.$t('iqrfnet.sendPacket.messages.incorrect.dataLength').toString();
				break;
			case 6:
				message = this.$t('iqrfnet.sendPacket.messages.incorrect.data').toString();
				break;
			case 7:
				message = this.$t('iqrfnet.sendPacket.messages.incorrect.hwpid').toString();
				break;
			case 8:
				message = this.$t('forms.messages.noDevice',{
					address: (this.addressOverwrite ? this.address : Number.parseInt(this.packetNadr, 16))
				}).toString();
				break;
			default:
				message = this.$t('iqrfnet.sendPacket.messages.failure').toString();
				break;
		}
		this.$toast.clear();
		this.$toast.open({
			message: message,
			type: (error ? 'error': 'success'),
			position: 'top',
			dismissible: true,
			duration: 5000,
			pauseOnHover: true
		});
	}

	/**
	 * Removes dots from string packet representation
	 * @param {string} bytes Packet bytes
	 * @returns {string} Packet byte string without dot separators
	 */
	private joinBytes(bytes: string): string {
		return bytes.replace(/\./g, '');
	}

	/**
	 * Sets new DPA packet
	 * @param {string} newPacket New DPA packet
	 */
	private setPacket(newPacket: string): void {
		this.packetNadr = this.joinBytes(newPacket.substring(0, 3));
		this.packetPnum = this.joinBytes(newPacket.substring(6, 9));
		this.packetPcmd = this.joinBytes(newPacket.substring(9, 12));
		this.packetHwpid = this.joinBytes(newPacket.substring(12, 17));
		if (newPacket.length > 17) {
			this.packetPdata = newPacket.substring(18, newPacket.length);
		} else {
			this.packetPdata = '';
		}
		this.setTimeout();
	}

	/**
	 * Sets DPA timeout
	 */
	private setTimeout(): void {
		const packet = Packet.parse(this.packet);
		const newTimeout = packet.detectTimeout();
		if (newTimeout === null) {
			this.timeoutOverwrite = false;
			this.timeout = 1000;
		} else {
			this.timeoutOverwrite = true;
			this.timeout = newTimeout;
		}
	}
}
</script>

<style lang='scss'>
.dpa-section {
	float: left;
	width: 25%;
}
</style>
