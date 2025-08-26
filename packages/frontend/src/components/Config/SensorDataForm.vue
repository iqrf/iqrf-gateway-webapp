<template>
	<div>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{ invalid }'>
					<CForm>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							:rules='{
								required: true,
								integer: true,
								min: 4,
							}'
							:custom-messages='{
								required: $t("config.daemon.sensor-data.errors.period.required"),
								integer: $t("config.daemon.sensor-data.errors.period.integer"),
								min: $t("config.daemon.sensor-data.errors.period.min"),
							}'
						>
							<CInput
								v-model.number='config.period'
								type='number'
								min='4'
								:label='$t("config.daemon.sensor-data.form.period")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							:rules='{
								required: true,
								integer: true,
								min: 1,
							}'
							:custom-messages='{
								required: $t("config.daemon.sensor-data.errors.retryPeriod.required"),
								integer: $t("config.daemon.sensor-data.errors.retryPeriod.integer"),
								min: $t("config.daemon.sensor-data.errors.retryPeriod.min"),
							}'
						>
							<CInput
								v-model.number='config.retryPeriod'
								type='number'
								min='4'
								:label='$t("config.daemon.sensor-data.form.period")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<div class='form-group'>
							<label>
								<strong>{{ $t('config.daemon.scheduler.form.messages.messaging') }}</strong>
							</label>
							<VueSelect
								v-model='config.messagingList'
								multiple
								:options='messagings'
								:reduce='option => option.value'
								label='label'
								:filterable='true'
								:autoscroll='true'
							/>
						</div>
						<CInputCheckbox
							:checked.sync='config.autoRun'
							:label='$t("config.daemon.sensor-data.form.autoRun")'
						/>
						<CInputCheckbox
							:checked.sync='config.autoRun'
							:label='$t("config.daemon.sensor-data.form.asyncReports")'
						/>
						<CButton
							color='primary'
							:disabled='invalid'
							@click='saveConfig()'
						>
							{{ $t('forms.save') }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import { Component, Vue } from 'vue-property-decorator';
import { CButton, CCard, CCardBody, CElementCover, CForm, CInput, CInputCheckbox, CSpinner } from '@coreui/vue/src';
import { AxiosError } from 'axios';
import { extend, ValidationObserver, ValidationProvider } from 'vee-validate';
import { integer, min_value, required } from 'vee-validate/dist/rules';
import { SensorDataGetConfigResult, SensorDataSetConfigParams } from '@iqrf/iqrf-gateway-daemon-utils/types';
import VueSelect from 'vue-select';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';
import { extendedErrorToast } from '@/helpers/errorToast';

import { IOption } from '@/interfaces/Coreui';
import { IqrfGatewayDaemonSchedulerMessagings } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { MessagingType, SensorDataMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { MutationPayload } from 'vuex';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';
import SensorDataService from '@/services/DaemonApi/SensorDataService';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CElementCover,
		CForm,
		CInput,
		CInputCheckbox,
		CSpinner,
		ValidationObserver,
		ValidationProvider,
		VueSelect,
	},
})

/**
 * Sensor data service configuration form component
 */
export default class SensorDataForm extends Vue {

	/**
	 * @var {string} msgId Daemon API message ID
	 */
	private msgId = '';

	/**
	 * @var {SensorDataSetConfigParams} config Sensor data configuration
	 */
	private config: SensorDataSetConfigParams = {
		autoRun: false,
		period: 10,
		retryPeriod: 1,
		asyncReports: true,
		messagingList: [],
	};

	/**
	 * @var {Array<IOption>} messagings Array of available messaging component instances
	 */
	private messagings: Array<IOption> = [];

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'daemonClient/SOCKET_ONMESSAGE') {
				if (mutation.payload.data.msgId !== this.msgId) {
					return;
				}
				this.$store.commit('spinner/HIDE');
				this.$store.dispatch('daemonClient/removeMessage', mutation.payload.data.msgId);
				if (mutation.payload.mType === SensorDataMessages.GetConfig) {
					this.handleGetConfig(mutation.payload.data);
				} else if (mutation.payload.mType === SensorDataMessages.SetConfig) {
					this.handleSetConfig(mutation.payload.data);
				} else {
					this.$toast.error(
						this.$t('iqrfnet.messages.genericError').toString()
					);
				}
			}
		});
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Retrieves instances of Daemon messaging components
	 */
	private getMessagings(): void {
		DaemonConfigurationService.getMessagingInstances()
			.then((data: IqrfGatewayDaemonSchedulerMessagings) => {
				const mqttOptions = data.mqtt.map((item: string): IOption => ({
					label: `[MQTT] ${item}`,
					value: {
						type: MessagingType.Mqtt,
						instance: item,
					},
				}));
				const wsOptions = data.ws.map((item: string): IOption => ({
					label: `[WS] ${item}`,
					value: {
						type: MessagingType.Websocket,
						instance: item,
					},
				}));
				this.messagings = [
					...mqttOptions,
					...wsOptions,
				];
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.sensor-data.messages.messagingsFetch.failed');
			});
	}

	/**
	 * Get configuration
	 */
	private getConfig(): void {
		this.$store.dispatch('spinner/show', {
			timeout: 10000,
		});
		const options = new DaemonMessageOptions(null, 10000, 'config.daemon.sensor-data.messages.get.timeout', () => this.msgId = '');
		SensorDataService.getConfig(options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handle get configuration response
	 * @param response Response
	 */
	private handleGetConfig(response): void {
		if (response.status !== 0) {
			this.$toast.error(
				this.$t('config.daemon.sensor-data.messages.get.failed').toString(),
			);
			return;
		}
		this.config = response.rsp as SensorDataGetConfigResult;
		this.getMessagings();
	}

	/**
	 * Save configuration
	 */
	private saveConfig(): void {
		this.$store.dispatch('spinner/show', {
			timeout: 10000,
		});
		const options = new DaemonMessageOptions(null, 10000, 'config.daemon.sensor-data.messages.save.timeout', () => this.msgId = '');
		SensorDataService.setConfig(this.config, options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handle set configuration response
	 * @param response Response
	 */
	private handleSetConfig(response): void {
		if (response.status !== 0) {
			this.$toast.error(
				this.$t('config.daemon.sensor-data.messages.save.failed').toString(),
			);
			return;
		}
		this.$toast.success(
			this.$t('config.daemon.sensor-data.messages.save.success').toString(),
		);
	}
}


</script>
