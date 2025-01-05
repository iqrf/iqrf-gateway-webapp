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
	<CCard class='border-top-0 border-left-0 border-right-0 card-margin-bottom'>
		<CCardBody>
			<CCardTitle>{{ $t('iqrfnet.networkManager.maintenance.rfSignal.title') }}</CCardTitle>
			<ValidationObserver v-slot='{invalid}'>
				<CForm>
					<CInputRadioGroup
						:checked.sync='target'
						:options='targets'
					/>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						:rules='rfChannelRules.rule'
						:custom-messages='rfChannelMessages'
					>
						<CInput
							v-model.number='params.rfChannel'
							type='number'
							:min='rfChannelRules.min'
							:max='rfChannelRules.max'
							:label='$t("iqrfnet.networkManager.maintenance.rfSignal.rfChannel")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='integer|between:0,64|required'
						:custom-messages='{
							between: "iqrfnet.networkManager.maintenance.rfSignal.errors.rxFilter",
							integer: "iqrfnet.networkManager.maintenance.rfSignal.errors.rxFilter",
							required: "iqrfnet.networkManager.maintenance.rfSignal.errors.rxFilter",
						}'
					>
						<CInput
							v-model.number='params.rxFilter'
							type='number'
							min='0'
							max='64'
							:label='$t("iqrfnet.networkManager.maintenance.rfSignal.rxFilter")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CSelect
						:label='$t("iqrfnet.networkManager.maintenance.rfSignal.timeLabel")'
						:value.sync='params.measurementTime'
						:options='measurementTimes'
					/>
					<CButton
						class='mr-1'
						color='primary'
						:disabled='invalid'
						@click='testRf'
					>
						{{ $t('iqrfnet.networkManager.maintenance.rfSignal.test') }}
					</CButton>
					<CButton
						color='primary'
						:disabled='results.length === 0'
						@click='showResults'
					>
						{{ $t('iqrfnet.networkManager.maintenance.rfSignal.showResults') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
		<RfSignalTestResultModal ref='result' />
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardTitle, CForm, CInput, CInputRadioGroup, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import RfSignalTestResultModal from './RfSignalTestResultModal.vue';

import {between, integer, required} from 'vee-validate/dist/rules';
import {getRfChannelRules, getRfChannelValidationMessages} from '@/iqrfNet/Iqmesh';
import {RfSignalMeasurementTimes, RfSignalTargets} from '@/enums/IqrfNet/Maintenance';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import IqmeshNetworkService from '@/services/DaemonApi/IqmeshNetworkService';

import {IRfSignalTestParams, IRfSignalTestResult} from '@/interfaces/DaemonApi/Iqmesh/Maintenance';
import {IOption} from '@/interfaces/Coreui';
import {MutationPayload} from 'vuex';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardTitle,
		CForm,
		CInput,
		CInputRadioGroup,
		CSelect,
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
	 * @var {string} msgId
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
	 * @var {RfSignalTargets} target RF signal test target
	 */
	private target: RfSignalTargets = RfSignalTargets.COORDINATOR;

	/**
	 * @const {Array<IOption>} targets RF signal test targets
	 */
	private targets: Array<IOption> = [
		{
			value: RfSignalTargets.COORDINATOR,
			label: this.$t('iqrfnet.networkManager.maintenance.rfSignal.options.coordinator').toString(),
		},
		{
			value: RfSignalTargets.ALL_NODES,
			label: this.$t('iqrfnet.networkManager.maintenance.rfSignal.options.allNodes').toString(),
		},
	];

	/**
	 * @var {Array<IRfSignalTestResult>} results RF Signal Test results
	 */
	private results: Array<IRfSignalTestResult> = [];

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Generates measurement times for select component
	 * @returns {Array<IOption>} Measurement time select options
	 */
	get measurementTimes(): Array<IOption> {
		const measurementTimes: Array<IOption> = [];
		const times: Array<number> = Object.values(RfSignalMeasurementTimes).filter((v): v is number => Number.isInteger(v));
		times.forEach((value: number) => {
			measurementTimes.push({
				label: this.$t('iqrfnet.networkManager.maintenance.rfSignal.time', {time: value}).toString(),
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
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			this.$store.dispatch('spinner/hide');
			if (mutation.payload.mType === 'iqmeshNetwork_MaintenanceTestRF') {
				this.handleTestRf(mutation.payload.data);
			} else {
				this.$toast.error(
					this.$t('iqrfnet.messages.genericError').toString()
				);
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
	public setRfChannelRulesMessages(rfBand: number): void {
		if (rfBand === 433) {
			this.params.rfChannel = 16;
		} else if (rfBand === 868) {
			this.params.rfChannel = 52;
		} else {
			this.params.rfChannel = 255;
		}
		this.rfChannelRules = getRfChannelRules(rfBand);
		this.rfChannelMessages = getRfChannelValidationMessages(rfBand);
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
		(this.$refs.result as RfSignalTestResultModal).activateModal(this.results);
	}
}

</script>
