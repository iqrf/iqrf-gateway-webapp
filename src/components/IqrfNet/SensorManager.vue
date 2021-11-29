<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
	<CCard class='border-0 card-margin-bottom'>
		<CCardBody>
			<ValidationObserver v-slot='{invalid}'>
				<CForm>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
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
import {CButton, CCard, CCardBody, CCardFooter, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {between, integer, required} from 'vee-validate/dist/rules';

import StandardSensorService from '../../services/DaemonApi/StandardSensorService';

import {ISensor, IStandardSensor} from '../../interfaces/standard';
import {MutationPayload} from 'vuex';
import {WebSocketOptions} from '../../store/modules/webSocketClient.module';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardFooter,
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
	private address = 1;

	/**
	 * @var {string|null} msgId Daemon api message id
	 */
	private msgId: string|null = null;

	/**
	 * @var {string|null} responseType Type of Sensor standard message
	 */
	private responseType: string|null = null;

	/**
	 * @var {Array<ISensor>} sensors Array of Sensor standard objects
	 */
	private sensors: Array<ISensor> = [];

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
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
				} else if (mutation.payload.mType === 'iqrfSensor_Enumerate') {
					this.handleEnumerateResponse(mutation.payload);
				} else if (mutation.payload.mType === 'iqrfSensor_ReadSensorsWithTypes') {
					this.handleReadSensors(mutation.payload);
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
	 * @var {Array<IStandardSensor>} sensors Array of Sensor standard objects from response
	 */
	private parseEnumerate(sensors: Array<IStandardSensor>): void {
		this.sensors = [];
		sensors.forEach((item: IStandardSensor) => {
			if (item.breakdown !== undefined && (item.id === 'BINARYDATA7' || item.id === 'BINARYDATA30')) {
				item = item.breakdown[0];
			}
			this.sensors.push({'type': item.name, 'unit': item.unit});
		});
	}

	/**
	 * Parses Sensor read request response
	 * @param {Array<IStandardSensor>} sensors Array of Sensor standard objects from response
	 */
	private parseReadAll(sensors: Array<IStandardSensor>): void {
		this.sensors = [];
		sensors.forEach((item: IStandardSensor) => {
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
	 * Handles Read Sensors response
	 * @param response Daemon API response
	 */
	private handleReadSensors(response): void {
		if (response.data.status === 0) {
			this.parseReadAll(response.data.rsp.result.sensors);
			this.responseType = 'read';
			this.$toast.success(
				this.$t('iqrfnet.standard.sensor.messages.success').toString()
			);
		} else {
			this.handleError(response);
		}
	}

	/**
	 * Enumerates all sensors implemented by a device
	 */
	private submitEnumerate(): void {
		this.$store.dispatch('spinner/show', {timeout: 30000});
		StandardSensorService.enumerate(this.address, this.buildOptions())
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles Enumerate response
	 * @param response Daemon API response
	 */
	private handleEnumerateResponse(response): void {
		if (response.data.status === 0) {
			this.parseEnumerate(response.data.rsp.result.sensors);
			this.responseType = 'enum';
			this.$toast.success(
				this.$t('iqrfnet.standard.sensor.messages.success').toString()
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
					this.$t('iqrfnet.standard.sensor.messages.timeout').toString()
				);
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
}
</script>

<style scoped>
td {
	text-align: center;
}
</style>
