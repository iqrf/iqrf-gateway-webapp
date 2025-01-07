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
	<v-card flat tile>
		<v-card-title>{{ $t('iqrfnet.networkManager.maintenance.rfSignal.title') }}</v-card-title>
		<v-card-text>
			<ValidationObserver v-slot='{invalid}'>
				<v-form>
					<v-radio-group
						v-model='target'
						column
						dense
					>
						<v-radio
							v-for='(type, idx) of targets'
							:key='idx'
							:label='type.text'
							:value='type.value'
						/>
					</v-radio-group>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						:rules='rfChannelRules.rule'
						:custom-messages='rfChannelMessages'
					>
						<v-text-field
							v-model.number='params.rfChannel'
							type='number'
							:min='rfChannelRules.min'
							:max='rfChannelRules.max'
							:label='$t("iqrfnet.networkManager.maintenance.rfSignal.rfChannel")'
							:success='touched ? valid : null'
							:error-messages='errors'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='integer|between:0,64|required'
						:custom-messages='{
							between: $t("iqrfnet.networkManager.maintenance.rfSignal.errors.rxFilter"),
							integer: $t("iqrfnet.networkManager.maintenance.rfSignal.errors.rxFilter"),
							required: $t("iqrfnet.networkManager.maintenance.rfSignal.errors.rxFilter"),
						}'
					>
						<v-text-field
							v-model.number='params.rxFilter'
							type='number'
							min='0'
							max='64'
							:label='$t("iqrfnet.networkManager.maintenance.rfSignal.rxFilter")'
							:success='touched ? valid : null'
							:error-messages='errors[0]'
						/>
					</ValidationProvider>
					<v-select
						v-model='params.measurementTime'
						:label='$t("iqrfnet.networkManager.maintenance.rfSignal.timeLabel")'
						:items='measurementTimes'
					/>
					<v-btn
						class='mr-1'
						color='primary'
						:disabled='invalid'
						@click='testRf'
					>
						{{ $t('iqrfnet.networkManager.maintenance.rfSignal.test') }}
					</v-btn>
					<v-btn
						color='primary'
						:disabled='results.length === 0'
						@click='showResults'
					>
						{{ $t('iqrfnet.networkManager.maintenance.rfSignal.showResults') }}
					</v-btn>
				</v-form>
			</ValidationObserver>
		</v-card-text>
		<RfSignalTestResultModal ref='result' />
	</v-card>
</template>

<script lang='ts'>
import {Component, Prop, Vue, Watch} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import RfSignalTestResultModal from '@/components/IqrfNet/NetworkManager/Maintenance/RfSignalTestResultModal.vue';

import {between, integer, required} from 'vee-validate/dist/rules';
import {getRfChannelRules, getRfChannelValidationMessages} from '@/iqrfNet/Iqmesh';
import {RfSignalMeasurementTimes, RfSignalTargets} from '@/enums/IqrfNet/Maintenance';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import IqmeshNetworkService from '@/services/DaemonApi/IqmeshNetworkService';

import {IRfSignalTestParams, IRfSignalTestResult} from '@/interfaces/DaemonApi/Iqmesh/Maintenance';
import {ISelectItem} from '@/interfaces/Vuetify';
import {MutationPayload} from 'vuex';

@Component({
	components: {
		RfSignalTestResultModal,
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * Maintenance RF signal test component
 */
export default class RfSignalTest extends Vue {
	/**
	 * @property {number} rfBand RF band
	 */
	@Prop({required: true, default: 868}) rfBand!: number;

	/**
	 * @var {string} msgId Message ID
	 */
	private msgId = '';

	/**
	 * @var {IRfSignalTestParams} params RF signal test parameters
	 */
	private params: IRfSignalTestParams = {
		deviceAddr: 0,
		rfChannel: 52,
		rxFilter: 0,
		measurementTime: RfSignalMeasurementTimes.MS5160,
	};

	/**
	 * @var {Record<string, string|number>} rfChannelRules RF channel validation rules
	 */
	private rfChannelRules: Record<string, string|number> = {
		rule: 'integer|between:0,67|required',
		min: 0,
		max: 67
	};

	/**
	 * @var {Record<string, string>} rfChannelMessages RF channel validation messages
	 */
	private rfChannelMessages: Record<string, string> = {
		between: this.$t('iqrfnet.trConfiguration.form.messages.rfChannel.868').toString(),
		integer: this.$t('iqrfnet.trConfiguration.form.messages.rfChannel.868').toString(),
		required: this.$t('iqrfnet.trConfiguration.form.messages.rfChannel.868').toString(),
	};

	/**
	 * @var {Array<IRfSignalTestResult>} results RF Signal Test results
	 */
	private results: Array<IRfSignalTestResult> = [];

	/**
	 * @var {RfSignalTargets} target RF signal test target
	 */
	private target: RfSignalTargets = RfSignalTargets.COORDINATOR;

	/**
	 * @constant {Array<ISelectItem>} targets RF signal test targets
	 */
	private readonly targets: Array<ISelectItem> = [
		{
			value: RfSignalTargets.COORDINATOR,
			text: this.$t('iqrfnet.networkManager.maintenance.rfSignal.options.coordinator').toString(),
		},
		{
			value: RfSignalTargets.ALL_NODES,
			text: this.$t('iqrfnet.networkManager.maintenance.rfSignal.options.allNodes').toString(),
		},
	];

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Generates measurement times for select component
	 * @returns {Array<ISelectItem>} Measurement time select options
	 */
	get measurementTimes(): Array<ISelectItem> {
		const measurementTimes: Array<ISelectItem> = [];
		const times: Array<number> = Object.values(RfSignalMeasurementTimes).filter((v): v is number => Number.isInteger(v));
		times.forEach((value: number) => {
			measurementTimes.push({
				text: this.$t('iqrfnet.networkManager.maintenance.rfSignal.time', {time: value}).toString(),
				value: value,
			});
		});
		return measurementTimes;
	}

	/**
	 * Initializes validation rules and registers mutation handling
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'daemonClient/SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			this.$store.dispatch('spinner/hide');
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			if (mutation.payload.mType === 'iqmeshNetwork_MaintenanceTestRF') {
				this.handleTestRf(mutation.payload.data);
			}
		});
	}

	/**
	 * Unregisters mutation handling
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Sets RF channel validation rules and messages based on RF band
	 * @param {number} rfBand RF band
	 */
	@Watch('rfBand')
	private setRfChannelRulesMessages(): void {
		if (this.rfBand === 433) {
			this.params.rfChannel = 16;
		} else if (this.rfBand === 868) {
			this.params.rfChannel = 52;
		} else {
			this.params.rfChannel = 255;
		}
		this.rfChannelRules = getRfChannelRules(this.rfBand);
		this.rfChannelMessages = getRfChannelValidationMessages(this.rfBand);
	}

	/**
	 * Tests RF signal
	 */
	private testRf(): void {
		this.results = [];
		this.params.deviceAddr = this.target === RfSignalTargets.COORDINATOR ? 0 : 255;
		this.$store.dispatch('spinner/show', {
			timeout: 330000,
			text: this.$t('iqrfnet.networkManager.maintenance.rfSignal.messages.progress').toString()
		});
		const options = new DaemonMessageOptions(null, 330000, null, () => this.msgId = '');
		IqmeshNetworkService.maintenanceTestRfSignal(this.params, options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles MaintenanceTestRF response
	 * @param response Response
	 */
	private handleTestRf(response): void {
		if (response.status === 0) {
			this.results = response.rsp.testRfResult;
			this.showResults();
		} else if (response.status === 1003) {
			this.$toast.info(
				this.$t('forms.messages.noBondedNodes').toString()
			);
		} else {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.maintenance.rfSignal.messages.failed').toString()
			);
		}
	}

	/**
	 * Passes results to the modal window and renders it
	 */
	private showResults(): void {
		(this.$refs.result as RfSignalTestResultModal).showModal(this.results);
	}
}
</script>
