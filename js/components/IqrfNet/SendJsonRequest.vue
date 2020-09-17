<template>
	<div>
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
			<CCardBody>
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
		</CCard>
		<CRow>
			<CCol v-if='request !== null' md='6'>
				<CCard>
					<CCardHeader>
						{{ $t('iqrfnet.sendJson.request') }}
					</CCardHeader>
					<CCardBody>
						<prism-editor v-model='request' :highlight='highlighter' :readonly='true' />
					</CCardBody>
				</CCard>
			</CCol>
			<CCol v-if='response !== null' md='6'>
				<CCard>
					<CCardHeader>
						{{ $t('iqrfnet.sendJson.response') }}
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

import {CButton, CCard, CCardBody, CCardHeader, CForm, CTextarea} from '@coreui/vue';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import {timeout} from '../../helpers/timeout';
import {TextareaAutogrowDirective} from 'vue-textarea-autogrow-directive/src/VueTextareaAutogrowDirective';
import IqrfNetService from '../../services/IqrfNetService';

import {PrismEditor} from 'vue-prism-editor';
import 'vue-prism-editor/dist/prismeditor.min.css';
import Prism from 'prismjs/components/prism-core';
import 'prismjs/components/prism-json';
import 'prismjs/themes/prism.css';

export default {
	name: 'SendJsonRequest',
	components: {
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
			timeout: null
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
			if (mutation.type === 'SOCKET_ONSEND') {
				this.timeout = timeout('iqrfnet.sendJson.form.messages.timeout', 10000);
			}
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if ({}.hasOwnProperty.call(mutation.payload, 'mType')) {
					clearTimeout(this.timeout);
					this.$store.commit('spinner/HIDE');
					this.response = JSON.stringify(mutation.payload, null, 4);
					switch (mutation.payload.data.status) {
						case -1:
							this.$toast.error('Request error:' + this.$t('iqrfnet.sendJson.form.messages.error.timeout'));
							break;
						case 0:
							this.$toast.success(this.$t('iqrfnet.sendJson.form.messages.success'));
							break;
						case 1:
							this.$toast.error('Request error:' + this.$t('iqrfnet.sendJson.form.messages.error.fail'));
							break;
						case 2:
							this.$toast.error('Request error:' + this.$t('iqrfnet.sendJson.form.messages.error.pcmd'));
							break;
						case 3:
							this.$toast.error('Request error:' + this.$t('iqrfnet.sendJson.form.messages.error.pnum'));
							break;
						case 4:
							this.$toast.error('Request error:' + this.$t('iqrfnet.sendJson.form.messages.error.addr'));
							break;
						case 5:
							this.$toast.error('Request error:' + this.$t('iqrfnet.sendJson.form.messages.error.dataLen'));
							break;
						case 6:
							this.$toast.error('Request error:' + this.$t('iqrfnet.sendJson.form.messages.error.data'));
							break;
						case 7:
							this.$toast.error('Request error:' + this.$t('iqrfnet.sendJson.form.messages.error.hwpid'));
							break;
						case 8:
							this.$toast.error('Request error:' + this.$t('iqrfnet.sendJson.form.messages.error.nadr'));
							break;
						default:
							break;
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
	}
};
</script>
