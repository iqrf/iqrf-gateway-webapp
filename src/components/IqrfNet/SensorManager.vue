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
			<table class='table'>
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
import Vue from 'vue';
import {MutationPayload} from 'vuex';
import {CButton, CCard, CCardBody, CCardFooter, CCardHeader, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {between, integer, required} from 'vee-validate/dist/rules';
import StandardSensorService from '../../services/DaemonApi/StandardSensorService';
import { WebSocketOptions } from '../../store/modules/webSocketClient.module';

export default Vue.extend({
	name: 'SensorManager',
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
	},
	data(): any {
		return {
			address: 1,
			allowedMTypes: [
				'iqrfSensor_Enumerate',
				'iqrfSensor_ReadSensorsWithTypes'
			],
			responseType: null,
			sensors: null,
			msgId: null,
		};
	},
	created() {
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
					default:
						this.$toast.error(
							this.$t('iqrfnet.standard.sensor.messages.failure').toString()
						);
						break;
				}
			}
		});
	},
	beforeDestroy() {
		this.$store.dispatch('removeMessage', this.msgId);
		this.unsubscribe();
	},
	methods: {
		parseEnumerate(sensors: any) {
			this.sensors = [];
			sensors.forEach((item: any) => {
				if (item.id === 'BINARYDATA7' || item.id === 'BINARYDATA30') {
					item = item.breakdown[0];
				}
				this.sensors.push({'type': item.name, 'unit': item.unit});
			});
		},
		parseReadAll(sensors: any) {
			this.sensors = [];
			sensors.forEach((item: any) => {
				if (item.id === 'BINARYDATA7' || item.id === 'BINARYDATA30') {
					item = item.breakdown[0];
				}
				this.sensors.push({'type': item.name, 'value': item.value, 'unit': item.unit});
			});
		},
		buildOptions() {
			return new WebSocketOptions(null, 30000, 'iqrfnet.standard.sensor.messages.timeout', () => this.msgId = null);
		},
		submitReadAll() {
			this.$store.dispatch('spinner/show', {timeout: 30000});
			StandardSensorService.readAll(this.address, this.buildOptions())
				.then((msgId: string) => this.msgId = msgId);
		},
		submitEnumerate() {
			this.$store.dispatch('spinner/show', {timeout: 30000});
			StandardSensorService.enumerate(this.address, this.buildOptions())
				.then((msgId: string) => this.msgId = msgId);
		},
	}
});
</script>

<style scoped>
td {
	text-align: center;
}
</style>
