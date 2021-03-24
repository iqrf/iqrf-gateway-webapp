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
				<CAlert v-if='validatorErrors !== ""' color='danger'>
					{{ $t('iqrfnet.sendJson.messages.error.validatorErrors') }}<br>
					<span class='validation-errors'>{{ validatorErrors }}</span>
				</CAlert>
				<CElementCover 
					v-if='!isSocketConnected'
					style='z-index: 1;'
					:opacity='0.85'
				>
					{{ $t('iqrfnet.messages.socketError') }}
				</CElementCover>
				<ValidationObserver v-slot='{invalid}'>
					<CForm @submit.prevent='processSubmit'>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|json|mType'
							:custom-messages='{
								required: "iqrfnet.sendJson.messages.missing",
								json: "iqrfnet.sendJson.messages.invalid",
								mType: "iqrfnet.sendJson.messages.mType"
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
		<div>
			<CRow>
				<CCol v-if='request !== ""' md='6'>
					<JsonMessage :message='request' type='request' source='sendJson' />
				</CCol>
				<CCol v-if='response !== ""' md='6'>
					<JsonMessage :message='response' type='response' source='sendJson' />
				</CCol>
			</CRow>
		</div>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CElementCover, CForm, CTextarea} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import JsonMessage from '../../components/IqrfNet/JsonMessage.vue';

import {AdditionalPropertiesParams, ErrorObject} from 'ajv';
import {required} from 'vee-validate/dist/rules';
import {StatusMessages} from '../../iqrfNet/sendJson';
import {TextareaAutogrowDirective} from 'vue-textarea-autogrow-directive/src/VueTextareaAutogrowDirective';
import {v4 as uuidv4} from 'uuid';

import IqrfNetService from '../../services/IqrfNetService';
import validate from '../../helpers/validate_daemonRequest';

import {mapGetters, MutationPayload} from 'vuex';
import {WebSocketOptions} from '../../store/modules/webSocketClient.module';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CElementCover,
		CForm,
		CTextarea,
		JsonMessage,
		ValidationObserver,
		ValidationProvider,
	},
	computed: {
		...mapGetters({
			isSocketConnected: 'isSocketConnected',
		}),
	},
	directives: {
		'autogrow': TextareaAutogrowDirective
	},
	metaInfo: {
		title: 'iqrfnet.sendJson.title',
	},
})

/**
 * Send daemon json api message page component
 */
export default class SendJsonRequest extends Vue {

	/**
	 * @var {string|null} json Daemon api json message
	 */
	private json: string|null = null

	/**
	 * @var {string|null} msgId Daemon api message id
	 */
	private msgId: string|null = null

	/**
	 * @var {string} request Daemon api request message, used in message card
	 */
	private request = ''

	/**
	 * @constant {string} requestSchema Daemon API request JSON schema for validator
	 */
	private requestSchema = require('../../schemas/genericDaemonRequest.json')

	/**
	 * @var {string} response Daemon api response message, used in message card
	 */
	private response = ''

	/**
	 * @var validator JSON schema validator function
	 */
	private validator: any = null 

	/**
	 * @var {string} validatorErrors String containing JSON schema violations
	 */
	private validatorErrors = ''

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		this.validator = validate;
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
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'SOCKET_ONCLOSE' || mutation.type === 'SOCKET_ONERROR') {
				this.$store.commit('spinner/HIDE');
				this.$store.dispatch('removeMessage', this.msgId);
			}
			if (mutation.type !== 'SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.mType === 'messageError') {
				this.$store.dispatch('removeMessage', this.msgId);
				this.handleMessageError(mutation.payload);
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			if (mutation.payload.mType === 'iqmeshNetwork_AutoNetwork') {
				this.handleAutoNetworkResponse(mutation.payload);
			} else {
				this.$store.dispatch('removeMessage', this.msgId);
				this.handleResponse(mutation.payload);
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
	 * Sends daemon api json message
	 */
	private processSubmit(): void {
		if (this.json === null) {
			return;
		}
		if (this.validator === null) {
			return;
		}
		this.request = '';
		this.response = '';
		this.validatorErrors = '';
		const json = JSON.parse(this.json);
		if (json.data.msgId === undefined) {
			Object.assign(json.data, {msgId: uuidv4()});
		}
		if (this.validator(json)) {
			this.sendRequest(json);
		} else {
			this.buildViolationString(this.validator.errors);
		}
	}

	/**
	 * Creates a JSON schema violation string message
	 * @param {Array<ErrorObject>} errors Array of violations
	 */
	private buildViolationString(errors: Array<ErrorObject>): void {
		let message = '';
		for (let error of errors) {
			if (error.keyword === 'type') {
				message += 'Type violation: Property "' + error.dataPath + '" ' + error.message + '. Current value: ' + error.data + ' (' + typeof error.data + '). [' + error.schemaPath + ']\n';
			} else if (error.keyword === 'additionalProperties') {
				message += 'Additional property violation: Property "' + error.dataPath + '" ' + error.message + '. Additional property: ' + (error.params as AdditionalPropertiesParams).additionalProperty + '. [' + error.schemaPath + ']\n';
			} else if (error.keyword === 'required') {
				message += 'Required property violation: Property "' + error.dataPath + '" ' + error.message!.replace(/'/g, '"') + '. [' + error.schemaPath + ']\n';
			} else if (error.keyword === 'minimum' || error.keyword === 'maximum') {
				message += 'Value range violation: Property "' + error.dataPath + '" ' + error.message + '. Current value: ' + error.data + '. [' + error.schemaPath + ']\n';
			}
		}
		this.validatorErrors = message.trimRight();
	}

	/**
	 * Sets request options and sends Daemon API request
	 * @param request Daemon API request
	 */
	private sendRequest(request): void {
		let options = new WebSocketOptions(request);
		if ({}.hasOwnProperty.call(request.data.req, 'nAdr') && request.data.req.nAdr === 255) { // if a message is broadcasted, do not wait for proper response
			options.timeout = 1000;
		} else if (request.mType === 'iqrfEmbedOs_Batch' || request.mType === 'iqrfEmbedOs_SelectiveBatch') { // batch and selective batch requests do not have proper responses, do not wait
			options.timeout = 1000;
		} else if (request.mType === 'iqmeshNetwork_AutoNetwork') { // autonetwork request has multiple responses, do not timeout
			this.$toast.info(
				this.$t('iqrfnet.sendJson.messages.autoNetworkStart').toString()
			);
		} else { // regular messages have a minute timeout
			options.timeout = 60000;
			options.message = 'iqrfnet.sendJson.messages.error.fail';
			this.$store.commit('spinner/SHOW');
		}
		options.callback = () => this.msgId = null;
		this.request = JSON.stringify(request, null, 4);
		this.response = '';
		IqrfNetService.sendJson(options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles messageError Daemon API response
	 * @param response Daemon API response
	 */
	private handleMessageError(response): void {
		this.response = JSON.stringify(response, null, 4);
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
		this.response = JSON.stringify(response, null, 4);
		this.$store.commit('spinner/HIDE');
		if (response.data.rsp.lastWave && response.data.rsp.progress === 100) { // autonetwork finished
			this.$store.dispatch('removeMessage', this.msgId);
			this.$toast.info(
				this.$t('iqrfnet.sendJson.messages.autoNetworkFinish').toString()
			);
		}
	}

	/**
	 * Handles other Daemon API responses
	 * @param response Daemon API response
	 */
	private handleResponse(response): void {
		this.response = JSON.stringify(response, null, 4);
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

<style scoped>
.validation-errors {
	white-space: pre-wrap;
}
</style>
