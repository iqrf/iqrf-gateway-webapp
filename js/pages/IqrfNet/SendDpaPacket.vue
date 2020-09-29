<template>
	<div>
		<CCard body-wrapper>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm @submit.prevent='handleSubmit'>
					<ValidationProvider
						v-slot='{ valid, touched, errors }'
						rules='dpaPacket|required'
						:custom-messages='{required: "iqrfnet.sendPacket.form.messages.missing.packet"}'
					>
						<CInput
							v-model='packet'
							:label='$t("iqrfnet.sendPacket.form.packet")'
							:is-valid='touched ? valid : null'
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
		<CRow>
			<CCol md='6'>
				<CCard v-if='request !== null'>
					<CCardHeader class='d-flex'>
						<span class='mr-auto'>{{ $t('iqrfnet.sendPacket.request') }}</span>
						<CButton
							v-clipboard='request'
							v-clipboard:success='() => $toast.success($t("iqrfnet.sendPacket.copy.messages.request").toString())'
							color='primary'
							size='sm'
						>
							{{ $t('iqrfnet.sendPacket.copy.request') }}
						</CButton>
					</CCardHeader>
					<CCardBody>
						<prism-editor v-model='request' :highlight='highlighter' :readonly='true' />
					</CCardBody>
				</CCard>
			</CCol>
			<CCol md='6'>
				<CCard v-if='response !== null'>
					<CCardHeader class='d-flex'>
						<span class='mr-auto'>{{ $t('iqrfnet.sendPacket.response') }}</span>
						<CButton
							v-clipboard='response'
							v-clipboard:success='() => $toast.success($t("iqrfnet.sendPacket.copy.messages.response").toString())'
							color='primary'
							size='sm'
						>
							{{ $t('iqrfnet.sendPacket.copy.response') }}
						</CButton>
					</CCardHeader>
					<CCardBody>
						<prism-editor v-model='response' :highlight='highlighter' :readonly='true' />
					</CCardBody>
				</CCard>
			</CCol>
		</CRow>
	</div>
</template>

<script>
import {CButton, CCard, CCardBody, CCardHeader, CCol, CForm, CInput, CInputCheckbox, CRow} from '@coreui/vue/src';
import DpaMacros from '../../components/IqrfNet/DpaMacros';
import {between, integer, min_value, required} from 'vee-validate/dist/rules';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import sendPacket from '../../iqrfNet/sendPacket';

import {PrismEditor} from 'vue-prism-editor';
import 'vue-prism-editor/dist/prismeditor.min.css';
import Prism from 'prismjs/components/prism-core';
import 'prismjs/components/prism-json';
import 'prismjs/themes/prism.css';

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
		PrismEditor,
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
		extend('between', between);
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
		extend('dpaPacket', (packet) => {
			return sendPacket.validatePacket(packet) ? true : 'iqrfnet.sendPacket.form.messages.invalid.packet';
		});
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONSEND' &&
					mutation.payload.mType === 'iqrfRaw') {
				this.request = JSON.stringify(mutation.payload, null, 4);
				this.response = null;
			}
			if (mutation.type === 'SOCKET_ONMESSAGE' &&
					mutation.payload.mType === 'iqrfRaw') {
				this.$store.commit('spinner/HIDE');
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
	},
	beforeDestroy() {
		this.unsubscribe();
	},
	methods: {
		/**
		 * Handles Send DPA packet form submit event
		 */
		handleSubmit() {
			if (this.packet === null || this.packet === '') {
				this.$toast.error(this.$t('iqrfnet.sendPacket.form.messages.missing.packet').toString());
				return;
			}
			this.$store.commit('spinner/SHOW');
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
		 * JSON highlighter method
		 */
		highlighter(code) {
			return Prism.highlight(code, Prism.languages.json, 'json');
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
	metaInfo: {
		title: 'iqrfnet.sendPacket.title',
	},
};
</script>
