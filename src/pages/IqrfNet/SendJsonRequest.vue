<template>
	<div>
		<h1>{{ $t('iqrfnet.sendJson.title') }}</h1>
		<CCard>
			<CCardHeader>
				<CButton 
					color='primary'
					size='sm' 
					href='https://docs.iqrf.org/iqrf-gateway/api.html'
					target='_blank'
				>
					{{ $t("iqrfnet.sendJson.documentation") }}
				</CButton>
			</CCardHeader>
			<CCardBody v-if='daemonAvailable'>
				<ValidationObserver v-slot='{ invalid }'>
					<CForm @submit.prevent='processSubmit'>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required|json|mType'
							:custom-messages='{
								required: "iqrfnet.sendJson.form.messages.missing",
								json: "iqrfnet.sendJson.form.messages.invalid",
								mType: "iqrfnet.sendJson.form.messages.mType"
							}'
						>
							<CTextarea
								v-model='json'
								v-autogrow
								:label='$t("iqrfnet.sendJson.form.json")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CButton color='primary' type='submit' :disabled='invalid'>
							{{ $t('forms.send') }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</CCardBody>
			<CCardBody v-else>
				<CAlert color='danger'>
					{{ $t('iqrfnet.sendJson.notAvailable', {attempt: reconnectAttempt}) }}
				</CAlert>
			</CCardBody>
		</CCard>
		<CRow>
			<CCol v-if='request !== null' md='6'>
				<CCard>
					<CCardHeader class='d-flex'>
						<span class='mr-auto'>
							{{ $t('iqrfnet.sendJson.request') }}
						</span>
						<CButton
							v-clipboard='request'
							v-clipboard:success='() => $toast.success($t("iqrfnet.sendJson.copy.messages.request").toString())'
							color='primary'
							size='sm'
						>
							{{ $t('iqrfnet.sendJson.copy.request') }}
						</CButton>
					</CCardHeader>
					<CCardBody>
						<prism-editor
							v-model='request'
							:highlight='highlighter'
							:readonly='true'
						/>
					</CCardBody>
				</CCard>
			</CCol>
			<CCol v-if='response !== null' md='6'>
				<CCard>
					<CCardHeader class='d-flex'>
						<span class='mr-auto'>
							{{ $t('iqrfnet.sendJson.response') }}
						</span>
						<CButton
							v-clipboard='response'
							v-clipboard:success='() => $toast.success($t("iqrfnet.sendJson.copy.messages.response").toString())'
							color='primary'
							size='sm'
						>
							{{ $t('iqrfnet.sendJson.copy.response') }}
						</CButton>
					</CCardHeader>
					<CCardBody>
						<prism-editor
							v-model='response'
							:highlight='highlighter'
							:readonly='true'
						/>
					</CCardBody>
				</CCard>
			</CCol>
		</CRow>
	</div>
</template>

<script>
import {CAlert, CButton, CCard, CCardBody, CCardHeader, CForm, CTextarea} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import {timeout} from '../../helpers/timeout';
import {TextareaAutogrowDirective} from 'vue-textarea-autogrow-directive/src/VueTextareaAutogrowDirective';
import {StatusMessages} from '../../iqrfNet/sendJson';
import IqrfNetService from '../../services/IqrfNetService';

import {PrismEditor} from 'vue-prism-editor';
import 'vue-prism-editor/dist/prismeditor.min.css';
import Prism from 'prismjs/components/prism-core';
import 'prismjs/components/prism-json';
import 'prismjs/themes/prism.css';

export default {
	name: 'SendJsonRequest',
	components: {
		CAlert,
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CTextarea,
		PrismEditor,
		ValidationObserver,
		ValidationProvider,
	},
	directives: {
		'autogrow': TextareaAutogrowDirective
	},
	data() {
		return {
			json: null,
			request: null,
			response: null,
			timeout: null,
			mType: null,
			daemonAvailable: true,
			reconnectAttempt: 0,
		};
	},
	created() {
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
		extend('required', required);
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONOPEN') {
				this.daemonAvailable = true;
			} else if (mutation.type === 'SOCKET_ONCLOSE' || 
				mutation.type === 'SOCKET_ONERROR') {
				this.daemonAvailable = false;
			} else if (mutation.type === 'SOCKET_RECONNECT') {
				this.reconnectAttempt = mutation.payload;
			} else if (mutation.type === 'SOCKET_ONSEND') {
				this.mType = mutation.payload.mType;
				this.timeout = timeout('iqrfnet.sendJson.form.messages.timeout', 30000);
			} else if (mutation.type === 'SOCKET_ONMESSAGE') {
				if ({}.hasOwnProperty.call(mutation.payload, 'mType')) {
					clearTimeout(this.timeout);
					this.$store.commit('spinner/HIDE');
					if (mutation.payload.mType === this.mType) {
						this.response = JSON.stringify(mutation.payload, null, 4);
						if (mutation.payload.data.status === 0) {
							this.$toast.success(
								this.$t('iqrfnet.sendJson.form.messages.success')
									.toString()
							);
						} else {
							if (mutation.payload.data.status in StatusMessages) {
								this.$toast.error(
									this.$t(StatusMessages[mutation.payload.data.status])
										.toString()
								);
							} else {
								this.$toast.error(
									this.$t('iqrfnet.sendJson.form.messages.error.fail')
										.toString()
								);
							}
						}
					} else if (mutation.payload.mType === 'messageError') {
						this.response = JSON.stringify(mutation.payload, null, 4);
						this.$toast.error(
							this.$t('iqrfnet.sendJson.form.messages.error.messageError')
								.toString()
						);
					}
				}
			}
		});
	},
	beforeDestroy() {
		this.unsubscribe();
	},
	methods: {
		/**
		 * JSON highlighter method
		 */
		highlighter(code) {
			return Prism.highlight(code, Prism.languages.json, 'json');
		},
		processSubmit() {
			this.$store.commit('spinner/SHOW');
			let json = JSON.parse(this.json);
			this.request = JSON.stringify(json, null, 4);
			this.response = null;
			IqrfNetService.sendJson(json);
		},
	},
	metaInfo: {
		title: 'iqrfnet.sendJson.title',
	},
};
</script>
