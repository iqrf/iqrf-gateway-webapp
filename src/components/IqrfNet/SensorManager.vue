<template>
	<CCard>
		<CCardHeader>{{ $t('iqrfnet.standard.sensor.title') }}</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='integer|required|between:1,239'
						:custom-messages='{
							integer: "forms.messages.integer",
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
					<CButton
						color='primary'
						:disabled='invalid'
						@click.prevent='submitEnumerate'
					>
						{{ $t('forms.enumerate') }}
					</CButton> <CButton
						color='secondary'
						:disabeld='invalid'
						@click.prevent='submitReadAll'
					>
						{{ $t('iqrfnet.standard.sensor.readAll') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
		<CCardFooter v-if='responseType != null'>
			<table class='table d-block overflow-auto text-nowrap'>
				<thead>
					{{ $t('iqrfnet.standard.sensor.sensors') }}
				</thead>
				<tbody>
					<tr>
						<th>{{ $t('iqrfnet.standard.sensor.type') }}</th>
						<td v-for='i of sensors.length' :key='i'>
							{{ sensors[i-1].type }}
						</td>
					</tr>
					<tr v-if='responseType === "enum"'>
						<th>{{ $t('iqrfnet.standard.sensor.unit') }}</th>
						<td v-for='j of sensors.length' :key='j'>
							{{ sensors[j-1].unit }}
						</td>
					</tr>
					<tr v-else-if='responseType === "read"'>
						<th>{{ $t('iqrfnet.standard.sensor.value') }}</th>
						<td v-for='k of sensors.length' :key='k'>
							{{ sensors[k-1].value + ' ' + sensors[k-1].unit }}
						</td>
					</tr>
				</tbody>
			</table>
		</CCardFooter>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {MutationPayload} from 'vuex';
import {CButton, CCard, CCardBody, CCardFooter, CCardHeader, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {between, integer, required} from 'vee-validate/dist/rules';
import StandardSensorService from '../../services/DaemonApi/StandardSensorService';
import { WebSocketOptions } from '../../store/modules/webSocketClient.module';

interface Sensor {
	type: string
	value?: number
	unit: string
}

interface StandardSensor {
	breakdown?: StandardSensor
	decimalPlaces: number
	frcs: Array<number>
	id: string
	name: string
	shortName: string
	type: number
	unit: string
	value: number
}

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardFooter,
		CCardHeader,
		CForm,
		CInput,
		ValidationObserver,
		ValidationProvider
	}
})

/**
 * Sensor manager card for Standard Manager
 */
export default class SensorManager extends Vue {
	/**
	 * @var {number} address Address of device implementing the Sensor standard
	 */
	private address = 1

	/**
	 * @constant {Array<string>} allowedMTypes Array of allowed daemon api messages
	 */
	private allowedMTypes: Array<string> = [
		'iqrfSensor_Enumerate',
		'iqrfSensor_ReadSensorsWithTypes'
	]

	/**
	 * @var {string|null} msgId Daemon api message id
	 */
	private msgId: string|null = null

	/**
	 * @var {string|null} responseType Type of Sensor standard message
	 */
	private responseType: string|null = null

	/**
	 * @var {Array<Sensor>} sensors Array of Sensor standard objects
	 */
	private sensors: Array<Sensor> = []

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (!this.allowedMTypes.includes(mutation.payload.mType)) {
					return;
				}
				if (mutation.payload.data.msgId !== this.msgId) {
					return;
				}
				this.$store.dispatch('spinner/hide');
				this.$store.dispatch('removeMessage', this.msgId);
				switch(mutation.payload.data.status) {
					case -1:
						this.$toast.error(
							this.$t('iqrfnet.standard.sensor.messages.timeout').toString()
						);
						break;
					case 0:
						this.$toast.success(
							this.$t('iqrfnet.standard.sensor.messages.success').toString()
						);
						if (mutation.payload.mType === 'iqrfSensor_Enumerate') {
							this.parseEnumerate(mutation.payload.data.rsp.result.sensors);
							this.responseType = 'enum';
						} else if (mutation.payload.mType === 'iqrfSensor_ReadSensorsWithTypes') {
							this.parseReadAll(mutation.payload.data.rsp.result.sensors);
							this.responseType = 'read';
						}
						break;
					case 3:
						this.$toast.error(
							this.$t('iqrfnet.standard.sensor.messages.pnum').toString()
						);
						break;
					case 8:
						this.$toast.error(
							this.$t('forms.messages.noDevice', {address: this.address}).toString()
						);
						break;
					default:
						this.$toast.error(
							this.$t('iqrfnet.standard.sensor.messages.failure').toString()
						);
						break;
				}
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
	 * Parses Sensor enumerate request response
	 * @var {Array<StandardSensor>} sensors Array of Sensor standard objects from response
	 */
	private parseEnumerate(sensors: Array<StandardSensor>): void {
		this.sensors = [];
		sensors.forEach((item: StandardSensor) => {
			if (item.breakdown !== undefined && (item.id === 'BINARYDATA7' || item.id === 'BINARYDATA30')) {
				item = item.breakdown[0];
			}
			this.sensors.push({'type': item.name, 'unit': item.unit});
		});
	}

	/**
	 * Parses Sensor read request response
	 * @param {Array<StandardSensor>} sensors Array of Sensor standard objects from response
	 */
	private parseReadAll(sensors: Array<StandardSensor>): void {
		this.sensors = [];
		sensors.forEach((item: StandardSensor) => {
			if (item.breakdown !== undefined && (item.id === 'BINARYDATA7' || item.id === 'BINARYDATA30')) {
				item = item.breakdown[0];
			}
			this.sensors.push({'type': item.name, 'value': item.value, 'unit': item.unit});
		});
	}

	/**
	 * Creates WebSocket request options object
	 * @returns {WebSocketOptions} WebSocket request options
	 */
	private buildOptions(): WebSocketOptions {
		return new WebSocketOptions(null, 30000, 'iqrfnet.standard.sensor.messages.timeout', () => this.msgId = null);
	}

	/**
	 * Reads information from all sensors implemented by a device
	 */
	private submitReadAll(): void {
		this.$store.dispatch('spinner/show', {timeout: 30000});
		StandardSensorService.readAll(this.address, this.buildOptions())
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Enumerates all sensors implemented by a device
	 */
	private submitEnumerate(): void {
		this.$store.dispatch('spinner/show', {timeout: 30000});
		StandardSensorService.enumerate(this.address, this.buildOptions())
			.then((msgId: string) => this.msgId = msgId);
	}
}
</script>

<style scoped>
td {
	text-align: center;
}
</style>
