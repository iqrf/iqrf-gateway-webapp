<template>
	<CCard>
		<CCardHeader>{{ $t('iqrfnet.standard.binaryOutput.title') }}</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='integer|required|between:1,239'
						:custom-messages='{
							integer: "forms.errors.integer",
							required: "iqrfnet.standard.form.messages.address",
							between: "iqrfnet.standard.form.messages.address"
						}'
					>
						<CInput
							v-model.number='address'
							type='number'
							min='1'
							max='239'
							:label='$t("iqrfnet.standard.form.address")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='integer|required|between:0,31'
						:custom-messages='{
							integer: "forms.errors.integer",
							required: "iqrfnet.standard.binaryOutput.form.messages.index",
							between: "iqrfnet.standard.binaryOutput.form.messages.index"
						}'
					>
						<CInput
							v-model.number='index'
							type='number'
							min='0'
							max='31'
							:label='$t("iqrfnet.standard.binaryOutput.form.index")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<div class='form-group'>
						<label for='formStateSwitch'>
							{{ $t('iqrfnet.standard.binaryOutput.form.state') }}
						</label><br>
						<CSwitch
							id='formStateSwitch'
							:checked.sync='state'
							color='primary'
							size='lg'
							shape='pill'
							:label-on='$t("iqrfnet.standard.binaryOutput.form.enabled")'
							:label-off='$t("iqrfnet.standard.binaryOutput.form.disabled")'
						/>
					</div>
					<CButton
						color='primary'
						:disabled='invalid'
						@click='submitEnumerate'
					>
						{{ $t('forms.enumerate') }}
					</CButton> <CButton
						color='secondary'
						:disabled='invalid'
						@click='submitGetStates'
					>
						{{ $t('iqrfnet.standard.binaryOutput.form.getStates') }}
					</CButton> <CButton
						color='secondary'
						:disabled='invalid'
						@click='submitSetState'
					>
						{{ $t('iqrfnet.standard.binaryOutput.form.setState') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
		<CCardFooter v-if='responseType !== null'>
			<table v-if='responseType === "enum"' class='table'>
				<thead>
					{{ $t('iqrfnet.standard.binaryOutput.enum') }}
				</thead>
				<tbody>
					<tr>
						<th>{{ $t('iqrfnet.standard.binaryOutput.outputs') }}</th>
						<td>{{ numOutputs }}</td>
					</tr>
				</tbody>
			</table>
			<table v-if='responseType === "set"' class='table scroll-table'>
				<thead>
					{{ $t('iqrfnet.standard.binaryOutput.prev') }}
				</thead>
				<tbody>
					<tr>
						<th>{{ $t('iqrfnet.standard.binaryOutput.index') }}</th>
						<td v-for='col of Array(32).keys()' :key='col'>
							{{ col }}
						</td>
					</tr>
					<tr>
						<th>{{ $t('iqrfnet.standard.binaryOutput.state') }}</th>
						<td v-for='ind of Array(32).keys()' :key='ind'>
							<CIcon
								v-if='states[ind] === true'
								class='text-success'
								:content='icons.on'
							/>
							<CIcon
								v-if='states[ind] === false'
								class='text-danger'
								:content='icons.off'
							/>
						</td>
					</tr>
				</tbody>
			</table>
		</CCardFooter>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CIcon, CInput, CSwitch} from '@coreui/vue/src';
import {cilCheckAlt, cilX} from '@coreui/icons';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {between, integer, required} from 'vee-validate/dist/rules';
import StandardBinaryOutputService, {StandardBinaryOutput} from '../../services/DaemonApi/StandardBinaryOutputService';
import { WebSocketOptions } from '../../store/modules/webSocketClient.module';
import { Dictionary } from 'vue-router/types/router';
import { MutationPayload } from 'vuex';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CIcon,
		CInput,
		CSwitch,
		ValidationObserver,
		ValidationProvider
	}
})

/**
 * BinaryOutput card for Standard Manager
 */
export default class BinaryOutputManager extends Vue {
	/**
	 * @var {number} address Address of device implementing BinaryOutput standard
	 */
	private address = 1
	
	/**
	 * @constant {Dictionary<Array<string>} icons Dictionary of CoreUI icons
	 */
	private icons: Dictionary<Array<string>> = {
		on: cilCheckAlt,
		off: cilX
	}

	/**
	 * @var {number} index Index of binary output
	 */
	private index = 0

	/**
	 * @var {string|null} msgId Daemon api message id
	 */
	private msgId: string|null = null

	/**
	 * @var {number} numOutputs Number of binary outputs implemented by the device
	 */
	private numOutputs = 0

	/**
	 * @var {string|null} responseType BinaryOutput response type
	 */
	private responseType: string|null = null

	/**
	 * @var {boolean} state Sets state of binary output specified by index
	 */
	private state = false

	/**
	 * @var {Array<number>} states Array of binary output states
	 */
	private states: Array<number> = []

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('integer', integer);
		extend('required', required);
		extend('between', between);
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'SOCKET_ONSEND') {
				this.responseType = null;
				return;
			}
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (mutation.payload.data.msgId !== this.msgId) {
					return;
				}
				this.$store.dispatch('spinner/hide');
				this.$store.dispatch('removeMessage', this.msgId);
				if (mutation.payload.mType === 'messageError') {
					this.$toast.error(
						this.$t('messageError', {error: mutation.payload.data.rsp.errorStr}).toString()
					);
				} else if (mutation.payload.mType === 'iqrfBinaryoutput_Enumerate') {
					this.handleEnumerateResponse(mutation.payload);
				} else if (mutation.payload.mType === 'iqrfBinaryoutput_SetOutput') {
					this.handleSetOutputResponse(mutation.payload);
				}
			}
		});
		this.generateStates();
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Fills array of states with default values
	 */
	private generateStates(): void {
		this.states = new Array(60).fill(false);
	}

	/**
	 * Reads states of binary outputs from Daemon api response
	 */
	private parseSetOutput(states: Array<number>): void {
		for(let i = 0; i < states.length; ++i) {
			this.states[i] = states[i];
		}
	}

	/**
	 * Creates WebSocketOptions object for Daemon api request
	 * @returns {WebSocketOptions} WebSocket request options
	 */
	private buildOptions(): WebSocketOptions {
		return new WebSocketOptions(null, 30000, 'iqrfnet.standard.binaryOutput.messages.timeout', () => this.msgId = null);
	}

	/**
	 * Performs enumeration of binary outputs
	 */
	private submitEnumerate(): void {
		this.$store.dispatch('spinner/show', {timeout: 30000});
		StandardBinaryOutputService.enumerate(this.address, this.buildOptions())
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles Enumerate response
	 * @param response Daemon API response
	 */
	private handleEnumerateResponse(response): void {
		if (response.data.status === 0) {
			this.numOutputs = response.data.rsp.result.binOuts;
			this.responseType = 'enum';
			this.$toast.success(
				this.$t('iqrfnet.standard.binaryOutput.messages.success').toString()
			);
		} else {
			this.handleError(response);
		}
	}

	/**
	 * Retrieves states of binary outputs
	 */
	private submitGetStates(): void {
		this.$store.dispatch('spinner/show', {timeout: 30000});
		StandardBinaryOutputService.getOutputs(this.address, this.buildOptions())
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Sets a new binary output state and retrieves previous states
	 */
	private submitSetState(): void {
		this.$store.dispatch('spinner/show', {timeout: 30000});
		const output = new StandardBinaryOutput(this.index, this.state);
		StandardBinaryOutputService.setOutputs(this.address, [output], this.buildOptions())
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles Set Output response
	 * @param response Daemon API response
	 */
	private handleSetOutputResponse(response): void {
		if (response.data.status === 0) {
			this.parseSetOutput(response.data.rsp.result.prevVals);
			this.responseType = 'set';
			this.$toast.success(
				this.$t('iqrfnet.standard.binaryOutput.messages.success').toString()
			);
		} else {
			this.handleError(response);
		}
	}

	/**
	 * Handles error response
	 * @param response Daemon API response
	 */
	private handleError(response): void {
		switch(response.data.status) {
			case -1:
				this.$toast.error(
					this.$t('iqrfnet.standard.binaryOutput.messages.timeout')
						.toString()
				);
				break;
			case 1:
				this.$toast.error(
					this.$t('iqrfnet.standard.binaryOutput.messages.fail')
						.toString()
				);
				break;
			case 3:
				this.$toast.error(
					this.$t('iqrfnet.standard.binaryOutput.messages.pnum')
						.toString()
				);
				break;
			case 8:
				this.$toast.error(
					this.$t('forms.messages.noDevice', {address: this.address}).toString()
				);
				break;
			default:
				this.$toast.error(
					this.$t('iqrfnet.standard.binaryOutput.messages.failure').toString()
				);
		}
	}
}
</script>

<style scoped>
.scroll-table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
}
</style>
