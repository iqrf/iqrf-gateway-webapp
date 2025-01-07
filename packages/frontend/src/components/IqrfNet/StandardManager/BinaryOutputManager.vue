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
	<v-card>
		<v-card-text>
			<ValidationObserver v-slot='{invalid}'>
				<form>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='integer|required|between:1,239'
						:custom-messages='{
							integer: $t("forms.errors.integer"),
							required: $t("iqrfnet.standard.form.messages.address"),
							between: $t("iqrfnet.standard.form.messages.address"),
						}'
					>
						<v-text-field
							v-model.number='address'
							type='number'
							min='1'
							max='239'
							:label='$t("iqrfnet.standard.form.address")'
							:success='touched ? valid : null'
							:error-messages='errors'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='integer|required|between:0,31'
						:custom-messages='{
							integer: $t("forms.errors.integer"),
							required: $t("iqrfnet.standard.binaryOutput.form.messages.index"),
							between: $t("iqrfnet.standard.binaryOutput.form.messages.index"),
						}'
					>
						<v-text-field
							v-model.number='index'
							type='number'
							min='0'
							max='31'
							:label='$t("iqrfnet.standard.binaryOutput.form.index")'
							:success='touched ? valid : null'
							:error-messages='errors'
						/>
					</ValidationProvider>
					<v-switch
						v-model='state'
						:label='$t("iqrfnet.standard.binaryOutput.form.state")'
						color='primary'
						inset
						dense
					/>
					<v-btn
						class='mr-1'
						color='primary'
						:disabled='invalid'
						@click='enumerate'
					>
						{{ $t('forms.enumerate') }}
					</v-btn>
					<v-btn
						class='mr-1'
						color='primary'
						:disabled='invalid'
						@click='getStates'
					>
						{{ $t('iqrfnet.standard.binaryOutput.form.getStates') }}
					</v-btn>
					<v-btn
						color='primary'
						:disabled='invalid'
						@click='setState'
					>
						{{ $t('iqrfnet.standard.binaryOutput.form.setState') }}
					</v-btn>
				</form>
			</ValidationObserver>
		</v-card-text>
		<v-card-text v-if='responseType !== StandardResponses.NONE'>
			<v-simple-table>
				<caption class='simpletable-caption'>
					{{ $t(`iqrfnet.standard.binaryOutput.${responseType === StandardResponses.ENUMERATE ? 'enum' : 'prev'}`) }}
				</caption>
				<tbody v-if='responseType === StandardResponses.ENUMERATE'>
					<tr>
						<th>{{ $t('iqrfnet.standard.binaryOutput.outputs') }}</th>
						<td>{{ numOutputs }}</td>
					</tr>
				</tbody>
				<tbody v-else>
					<tr>
						<th>{{ $t('iqrfnet.standard.binaryOutput.index') }}</th>
						<td v-for='col of Array(32).keys()' :key='col'>
							{{ col }}
						</td>
					</tr>
					<tr>
						<th>{{ $t('iqrfnet.standard.binaryOutput.state') }}</th>
						<td v-for='ind of Array(32).keys()' :key='ind'>
							<v-icon :color='states[ind] ? "success" : "error"'>
								{{ states[ind] ? 'mdi-check' : 'mdi-close' }}
							</v-icon>
						</td>
					</tr>
				</tbody>
			</v-simple-table>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {between, integer, required} from 'vee-validate/dist/rules';
import {StandardResponses} from '@/enums/IqrfNet/Standard';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import StandardBinaryOutputService, {StandardBinaryOutput} from '@/services/DaemonApi/StandardBinaryOutputService';

import {MutationPayload} from 'vuex';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	},
	data: () => ({
		StandardResponses,
	}),
})

/**
 * BinaryOutput card for Standard Manager
 */
export default class BinaryOutputManager extends Vue {
	/**
	 * @var {string} msgId Daemon API message ID
	 */
	private msgId = '';

	/**
	 * @var {number} address Address of device implementing BinaryOutput standard
	 */
	private address = 1;

	/**
	 * @var {number} index Index of binary output
	 */
	private index = 0;

	/**
	 * @var {number} numOutputs Number of binary outputs implemented by the device
	 */
	private numOutputs = 0;

	/**
	 * @var {StandardResponses} responseType BinaryOutput response type
	 */
	private responseType: StandardResponses = StandardResponses.NONE;

	/**
	 * @var {boolean} state Sets state of binary output specified by index
	 */
	private state = false;

	/**
	 * @var {Array<number>} states Array of binary output states
	 */
	private states: Array<number> = [];

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('integer', integer);
		extend('required', required);
		extend('between', between);
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'daemonClient/SOCKET_ONSEND') {
				this.responseType = StandardResponses.NONE;
				return;
			}
			if (mutation.type === 'daemonClient/SOCKET_ONMESSAGE') {
				if (mutation.payload.data.msgId !== this.msgId) {
					return;
				}
				this.$store.dispatch('spinner/hide');
				this.$store.dispatch('daemonClient/removeMessage', this.msgId);
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
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
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
	 * Creates DaemonMessageOptions object for Daemon api request
	 * @returns {DaemonMessageOptions} WebSocket request options
	 */
	private buildOptions(): DaemonMessageOptions {
		return new DaemonMessageOptions(null, 30000, 'iqrfnet.standard.binaryOutput.messages.timeout', () => this.msgId = '');
	}

	/**
	 * Performs enumeration of binary outputs
	 */
	private enumerate(): void {
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
			this.$toast.success(
				this.$t('iqrfnet.standard.binaryOutput.messages.success').toString()
			);
			this.responseType = StandardResponses.ENUMERATE;
		} else {
			this.handleError(response);
		}
	}

	/**
	 * Retrieves states of binary outputs
	 */
	private getStates(): void {
		this.$store.dispatch('spinner/show', {timeout: 30000});
		StandardBinaryOutputService.getOutputs(this.address, this.buildOptions())
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Sets a new binary output state and retrieves previous states
	 */
	private setState(): void {
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
			this.$toast.success(
				this.$t('iqrfnet.standard.binaryOutput.messages.success').toString()
			);
			this.responseType = StandardResponses.READ;
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
					this.$t('iqrfnet.standard.binaryOutput.messages.failure')
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

<style lang="scss" scoped>
td {
	padding: 0 !important;
}
</style>
