<template>
	<div>
		<h1>{{ $t('iqrfnet.sendPacket.title') }}</h1>
		<CCard body-wrapper>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm @submit.prevent='handleSubmit'>
					<CRow>
						<CCol md='6'>
							<ValidationProvider
								v-slot='{valid, touched, errors}'
								rules='nadr|minLen:4|maxLen:4|required'
								:custom-messages='{
									nadr: "iqrfnet.sendPacket.form.messages.invalid.nadr",
									minLen: "iqrfnet.sendPacket.form.messages.invalid.nadr",
									maxLen: "iqrfnet.sendPacket.form.messages.invalid.nadr",
									required: "iqrfnet.sendPacket.form.messages.invalid.nadr"
								}'
							>
								<CInput
									v-model='packetNadr'
									maxlength='4'
									:label='$t("iqrfnet.sendPacket.form.nadr")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
									:disabled='addressOverwrite'
									style='float: left; width: 25%'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{valid, touched, errors}'
								rules='pnum|minLen:2|maxLen:2|required'
								:custom-messages='{
									pnum: "iqrfnet.sendPacket.form.messages.invalid.pnum",
									minLen: "iqrfnet.sendPacket.form.messages.invalid.pnum",
									maxLen: "iqrfnet.sendPacket.form.messages.invalid.pnum",
									required: "iqrfnet.sendPacket.form.messages.invalid.pnum",
								}'
							>
								<CInput
									v-model='packetPnum'
									:label='$t("iqrfnet.sendPacket.form.pnum")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
									style='float: left; width: 25%'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{valid, touched, errors}'
								rules='pcmd|minLen:2|maxLen:2|required'
								:custom-messages='{
									pcmd: "iqrfnet.sendPacket.form.messages.invalid.pcmd",
									minLen: "iqrfnet.sendPacket.form.messages.invalid.pcmd",
									maxLen: "iqrfnet.sendPacket.form.messages.invalid.pcmd",
									required: "iqrfnet.sendPacket.form.messages.invalid.pcmd",
								}'
							>
								<CInput
									v-model='packetPcmd'
									:label='$t("iqrfnet.sendPacket.form.pcmd")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
									style='float: left; width: 25%'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{valid, touched, errors}'
								rules='hwpid|minLen:4|maxLen:4|required'
								:custom-messages='{
									hwpid: "iqrfnet.sendPacket.form.messages.invalid.hwpid",
									minLen: "iqrfnet.sendPacket.form.messages.invalid.hwpid",
									maxLen: "iqrfnet.sendPacket.form.messages.invalid.hwpid",
									required: "iqrfnet.sendPacket.form.messages.invalid.hwpid",
								}'
							>
								<CInput
									v-model='packetHwpid'
									:label='$t("iqrfnet.sendPacket.form.hwpid")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
									style='float: left; width: 25%'
								/>
							</ValidationProvider>
						</CCol>
						<CCol md='6'>
							<ValidationProvider
								v-slot='{valid, touched, errors}'
								rules='pdata'
								:custom-messages='{
									pdata: "iqrfnet.sendPacket.form.messages.invalid.hwpid"
								}'
							>
								<CInput
									v-model='packetPdata'
									:label='$t("iqrfnet.sendPacket.form.pdata")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
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
								v-slot='{ valid, touched, errors }'
								:disabled='!addressOverwrite'
								:rules='addressOverwrite ? "integer|between:0,239|required" : ""'
								:custom-messages='{
									between: "iqrfnet.sendPacket.form.messages.invalid.address",
									integer: "iqrfnet.sendPacket.form.messages.invalid.address",
									required: "iqrfnet.sendPacket.form.messages.missing.address",
								}'
							>
								<CInput
									v-model.number='address'
									:disabled='!addressOverwrite'
									:label='$t("iqrfnet.sendPacket.form.address")'
									:is-valid='addressOverwrite && touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
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
								v-slot='{ valid, touched, errors }'
								:rules='timeoutOverwrite ? "integer|min:0|required" : ""'
								:custom-messages='{
									integer: "iqrfnet.sendPacket.form.messages.invalid.timeout",
									min: "iqrfnet.sendPacket.form.messages.invalid.timeout",
									required: "iqrfnet.sendPacket.form.messages.missing.timeout",
								}'
							>
								<CInput
									v-model.number='timeout'
									:disabled='!timeoutOverwrite'
									:label='$t("iqrfnet.sendPacket.form.timeout")'
									:is-valid='timeoutOverwrite && touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
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
		<div>
			<CRow>
				<CCol v-if='request !== null' md='6'>
					<JsonMessage :message='request' type='request' source='sendDpa' />
				</CCol>
				<CCol v-if='response !== null' md='6'>
					<JsonMessage :message='response' type='response' source='sendDpa' />
				</CCol>
			</CRow>
		</div>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {MutationPayload} from 'vuex';
import {CButton, CCard, CCardBody, CCardHeader, CCol, CForm, CInput, CInputCheckbox, CRow} from '@coreui/vue/src';
import DpaMacros from '../../components/IqrfNet/DpaMacros.vue';
import {between, integer, min_value, required, min, max} from 'vee-validate/dist/rules';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import sendPacket from '../../iqrfNet/sendPacket';
import JsonMessage from '../../components/IqrfNet/JsonMessage.vue';
import {WebSocketOptions} from '../../store/modules/webSocketClient.module';
import {RawMessage} from '../../interfaces/dpa';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CCol,
		CForm,
		CInput,
		CInputCheckbox,
		CRow,
		DpaMacros,
		JsonMessage,
		ValidationObserver,
		ValidationProvider,
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
	private address = 0

	/**
	 * @var {boolean} addressOverwrite Controls whether packet address bytes should be overwritten
	 */
	private addressOverwrite = false

	/**
	 * @var {string|null} msgId Daemon api message id
	 */
	private msgId: string|null = null

	/**
	 * @var {string} packetNadr Packet NADR bytes
	 */
	private packetNadr = '0000'

	/**
	 * @var {string} packetPnum Packet PNUM byte
	 */
	private packetPnum = '00'

	/**
	 * @var {string} packetPcmd Packet PCMD byte
 	 */
	private packetPcmd = '00'

	/**
	 * @var {string} packetHwpid Packet HWPID bytes
	 */
	private packetHwpid = 'ffff'

	/**
	 * @var {string} packetPdata Packet PDATA bytes
	 */
	private packetPdata = ''

	/**
	 * @var {string|null} request Daemon api request message, used in message card
	 */
	private request: string|null = null

	/**
	 * @var {string|null} response Daemon api response message, used in message card
	 */
	private response: string|null = null

	/**
	 * @var {number} timeout Default daemon api message timeout
	 */
	private timeout = 1000

	/**
	 * @var {boolean} timeoutOverwrite Controls whether default daemon api message timeout should be overwritten
	 */
	private timeoutOverwrite = false

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;}

	/**
	 * Computes packet string from packet parts
	 * @returns {string} Packet string
	 */
	get packet(): string {
		let packet = this.packetNadr.substr(0, 2) + '.' + this.packetNadr.substr(2, 2) + '.';
		packet += this.packetPnum + '.' + this.packetPcmd + '.';
		packet += this.packetHwpid.substr(0, 2) + '.' + this.packetHwpid.substr(2, 2) + '.' + this.packetPdata;
		return packet;
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
			const re = new RegExp('^[0-9a-f]{2}00$', 'i');
			return re.test(nadr);
		});
		extend('pnum', (pnum: string) => {
			const re = new RegExp('^[0-9a-f]{2}$', 'i');
			return re.test(pnum);
		});
		extend('pcmd', (pcmd: string) => {
			const re = new RegExp('^[0-7][0-9a-f]$', 'i');
			return re.test(pcmd);
		});
		extend('hwpid', (hwpid: string) => {
			const re = new RegExp('^[0-9a-f]{4}$', 'i');
			return re.test(hwpid);
		});
		extend('pdata', (pdata: string) => {
			const re = new RegExp('^([0-9a-f]{2}\\.){0,56}[0-9a-f]{2}(\\.|)$', 'i');
			return re.test(pdata);
		});
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'SOCKET_ONSEND' &&
					mutation.payload.mType === 'iqrfRaw') {
				this.request = JSON.stringify(mutation.payload, null, 4);
				this.response = null;
			}
			if (mutation.type === 'SOCKET_ONMESSAGE' &&
				mutation.payload.mType === 'iqrfRaw' &&
				mutation.payload.data.msgId === this.msgId) {
				this.$store.commit('spinner/HIDE');
				this.$store.dispatch('removeMessage', this.msgId);
				this.response = JSON.stringify(mutation.payload, null, 4);
				switch (mutation.payload.data.status) {
					case 0:
						this.$toast.success(this.$t('iqrfnet.sendPacket.messages.success').toString());
						break;
					case 2:
						this.$toast.error(this.$t('iqrfnet.sendPacket.messages.incorrect.pcmd').toString());
						break;
					case 3:
						this.$toast.error(this.$t('iqrfnet.sendPacket.messages.incorrect.pnum').toString());
						break;
					case 5:
						this.$toast.error(this.$t('iqrfnet.sendPacket.messages.incorrect.dataLength').toString());
						break;
					case 6:
						this.$toast.error(this.$t('iqrfnet.sendPacket.messages.incorrect.data').toString());
						break;
					case 7:
						this.$toast.error(this.$t('iqrfnet.sendPacket.messages.incorrect.hwpid').toString());
						break;
					case 8:
						this.$toast.error(this.$t('iqrfnet.sendPacket.messages.incorrect.nadr').toString());
						break;
					default:
						this.$toast.error(this.$t('iqrfnet.sendPacket.messages.failure').toString());
						break;
				}
			}
		});
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Handles Send DPA packet form submit event
	 */
	private handleSubmit(): Promise<string>|void {
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
			json.data.req.rData = sendPacket.updateNadr(this.packet, this.address);
		}
		if (this.timeoutOverwrite) {
			json.data.timeout = this.timeout;
		}
		let options = new WebSocketOptions(json);
		const packet = sendPacket.Packet.parse(this.packet);
		if (packet.nadr === 255) {
			options.timeout = 1000;
		} else if (packet.pnum === 2 && (packet.pcmd === 5 || packet.pcmd === 11)) {
			options.timeout = 1000;
		} else {
			options.timeout = 60000;
			options.message = 'iqrfnet.sendPacket.messages.failure';
			this.$store.commit('spinner/SHOW');
		}
		options.callback = () => this.msgId = null;
		return this.$store.dispatch('sendRequest', options)
			.then((msgId: string) => this.msgId = msgId);
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
		this.packetNadr = this.joinBytes(newPacket.substr(0, 6));
		this.packetPnum = this.joinBytes(newPacket.substr(6, 3));
		this.packetPcmd = this.joinBytes(newPacket.substr(9, 3));
		this.packetHwpid = this.joinBytes(newPacket.substr(12, 5));
		if (newPacket.length > 17) {
			this.packetPdata = newPacket.substr(18, newPacket.length - 1);
		}
		this.setTimeout();
	}

	/**
	 * Sets DPA timeout
	 */
	private setTimeout(): void {
		let packet = sendPacket.Packet.parse(this.packet);
		let newTimeout = packet.detectTimeout();
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
