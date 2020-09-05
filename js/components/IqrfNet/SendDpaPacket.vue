<template>
	<div>
		<CCard body-wrapper>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm @submit.prevent='handleSubmit'>
					<ValidationProvider
						v-slot='{ valid, errors }'
						rules='dpaPacket|required'
						:custom-messages='{required: "iqrfnet.sendPacket.form.messages.missing.packet"}'
					>
						<CInput
							v-model='packet'
							:label='$t("iqrfnet.sendPacket.form.packet")'
							:is-valid='valid'
							:invalid-feedback='$t(errors[0])'
							@input='setTimeout'
						/>
					</ValidationProvider>
					<CRow>
						<CCol md='6'>
							<CInputCheckbox
								:checked.sync='addressOverwrite'
								:label='$t("iqrfnet.sendPacket.form.addressOverwrite")'
							/>
							<ValidationProvider
								v-slot='{ valid, errors }'
								:disabled='!addressOverwrite'
								:rules='addressOverwrite ? "nadr|required" : ""'
								:custom-messages='{required: "iqrfnet.sendPacket.form.messages.missing.address"}'
							>
								<CInput
									v-model='address'
									:disabled='!addressOverwrite'
									:label='$t("iqrfnet.sendPacket.form.address")'
									type='number'
									:is-valid='addressOverwrite ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
						</CCol>
						<CCol md='6'>
							<CInputCheckbox
								:checked.sync='timeoutOverwrite'
								:label='$t("iqrfnet.sendPacket.form.timeoutOverwrite")'
							/>
							<ValidationProvider
								v-slot='{ valid, errors }'
								:rules='timeoutOverwrite ? "integer|required" : ""'
								:custom-messages='{integer: "iqrfnet.sendPacket.form.messages.invalid.timeout", required: "iqrfnet.sendPacket.form.messages.missing.timeout"}'
							>
								<CInput
									v-model='timeout'
									:disabled='!timeoutOverwrite'
									:label='$t("iqrfnet.sendPacket.form.timeout")'
									:is-valid='timeoutOverwrite ? valid : null'
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
		<CRow>
			<CCol md='6'>
				<CCard v-if='request !== null'>
					<CCardHeader>{{ $t('iqrfnet.sendPacket.request') }}</CCardHeader>
					<CCardBody>
						<pre><code class='json'>{{ request }}</code></pre>
					</CCardBody>
				</CCard>
			</CCol>
			<CCol md='6'>
				<CCard v-if='response !== null'>
					<CCardHeader>{{ $t('iqrfnet.sendPacket.response') }}</CCardHeader>
					<CCardBody>
						<pre><code class='json'>{{ response }}</code></pre>
					</CCardBody>
				</CCard>
			</CCol>
		</CRow>
	</div>
</template>

<script>
import {CButton, CCard, CCardBody, CCardHeader, CCol, CForm, CInput, CInputCheckbox, CRow} from '@coreui/vue';
import DpaMacros from './DpaMacros';
import {required, integer} from 'vee-validate/dist/rules';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import sendPacket from '../../iqrfNet/sendPacket';

extend('dpaPacket', (packet) => {
	return sendPacket.validatePacket(packet) ? true : 'iqrfnet.sendPacket.form.messages.invalid.packet';
});

extend('nadr', (value) => {
	const address = Number.parseInt(value, 10);
	if (!Number.isInteger(address) || address < 0 || address > 239) {
		return 'iqrfnet.sendPacket.form.messages.invalid.address';
	}
	return true;
});

extend('integer', integer);
extend('required', required);

export default {
	name: 'SendDpaPacket',
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
		ValidationObserver,
		ValidationProvider,
	},
	data() {
		return {
			packet: null,
			address: 0,
			addressOverwrite: false,
			timeout: 1000,
			timeoutOverwrite: false,
			request: null,
			response: null,
		};
	},
	created() {
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONSEND' &&
					mutation.payload.mType === 'iqrfRaw') {
				this.request = mutation.payload;
			}
			if (mutation.type === 'SOCKET_ONMESSAGE' &&
					mutation.payload.mType === 'iqrfRaw') {
				this.response = mutation.payload;
				switch (mutation.payload.data.status) {
					case 0:
						this.$toast.success(this.$t('iqrfnet.sendPacket.messages.success'));
						break;
					case 2:
						this.$toast.error(this.$t('iqrfnet.sendPacket.messages.incorrect.pcmd'));
						break;
					case 3:
						this.$toast.error(this.$t('iqrfnet.sendPacket.messages.incorrect.pnum'));
						break;
					case 5:
						this.$toast.error(this.$t('iqrfnet.sendPacket.messages.incorrect.dataLength'));
						break;
					case 6:
						this.$toast.error(this.$t('iqrfnet.sendPacket.messages.incorrect.data'));
						break;
					case 7:
						this.$toast.error(this.$t('iqrfnet.sendPacket.messages.incorrect.hwpid'));
						break;
					case 8:
						this.$toast.error(this.$t('iqrfnet.sendPacket.messages.incorrect.nadr'));
						break;
					default:
						this.$toast.error(this.$t('iqrfnet.sendPacket.messages.failure'));
						break;
				}
			}
		});
	},
	beforeDestroy() {
		this.unsubscribe();
	},
	methods: {
		/**
		 * Handles Send DPA packet form submit event
		 */
		handleSubmit() {
			let json = {
				'mType': 'iqrfRaw',
				'data': {
					'req': {
						'rData': this.packet,
					},
					'returnVerbose': true,
				},
			};
			if (this.addressOverwrite && this.address !== null) {
				json.data.req.rData = sendPacket.updateNadr(this.packet, this.address);
			}
			if (this.timeoutOverwrite && this.timeout !== null) {
				json.data.timeout = this.timeout;
			}
			return this.$store.dispatch('sendRequest', json);
		},
		/**
		 * Sets new DPA packet
		 * @param {string} newPacket New DPA packet
		 */
		setPacket(newPacket) {
			this.packet = newPacket;
			this.setTimeout();
		},
		/**
		 * Sets DPA timeout
		 */
		setTimeout() {
			let packet = sendPacket.Packet.parse(this.packet);
			let newTimeout = packet.detectTimeout();
			if (newTimeout === null) {
				this.timeoutOverwrite = false;
				this.timeout = 1000;
			} else {
				this.timeoutOverwrite = true;
				this.timeout = newTimeout;
			}
		},
	},
};
</script>

<style scoped>

</style>
