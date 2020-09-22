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
					<CButton color='primary' :disabled='invalid' @click.prevent='submitEnumerate'>
						{{ $t('forms.enumerate') }}
					</CButton>
					<CButton color='secondary' :disabeld='invalid' @click.prevent='submitReadAll'>
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

<script>
import {CButton, CCard, CCardBody, CCardFooter, CCardHeader, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {between, integer, required} from 'vee-validate/dist/rules';
import {timeout} from '../../helpers/timeout';
import StandardSensorService from '../../services/DaemonApi/StandardSensorService';

export default {
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
	data() {
		return {
			address: 1,
			allowedMTypes: [
				'iqrfSensor_Enumerate',
				'iqrfSensor_ReadSensorsWithTypes'
			],
			responseType: null,
			sensors: null,
			timeout: null
		};
	},
	created() {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONSEND') {
				if (!this.allowedMTypes.includes(mutation.payload.mType)) {
					return;
				}
				this.responseType = null;
				this.timeout = timeout('iqrfnet.networkManager.messages.submit.timeout', 10000);
			}
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (!this.allowedMTypes.includes(mutation.payload.mType)) {
					return;
				}
				clearTimeout(this.timeout);
				this.$store.commit('spinner/HIDE');
				switch(mutation.payload.data.status) {
					case -1:
						this.$toast.error(this.$t('iqrfnet.standard.sensor.messages.timeout').toString());
						break;
					case 0:
						this.$toast.success(this.$t('iqrfnet.standard.sensor.messages.success').toString());
						if (mutation.payload.mType === 'iqrfSensor_Enumerate') {
							this.parseEnumerate(mutation.payload.data.rsp.result.sensors);
							this.responseType = 'enum';
						} else if (mutation.payload.mType === 'iqrfSensor_ReadSensorsWithTypes') {
							this.parseReadAll(mutation.payload.data.rsp.result.sensors);
							this.responseType = 'read';
						}
						break;
					case 3:
						this.$toast.error(this.$t('iqrfnet.standard.sensor.messages.pnum').toString());
						break;
					default:
						this.$toast.error(this.$t('iqrfnet.standard.sensor.messages.failure').toString());
						break;
				}
			}
		});
	},
	beforeDestroy() {
		this.unsubscribe();
	},
	methods: {
		parseEnumerate(sensors) {
			this.sensors = [];
			sensors.forEach(item => {
				if (item.id === 'BINARYDATA7') {
					item = item.breakdown[0];
				}
				this.sensors.push({'type': item.name, 'unit': item.unit});
			});
		},
		parseReadAll(sensors) {
			this.sensors = [];
			sensors.forEach(item => {
				if (item.id === 'BINARYDATA7') {
					item = item.breakdown[0];
				}
				this.sensors.push({'type': item.name, 'value': item.value, 'unit': item.unit});
			});
		},
		submitReadAll() {
			this.$store.commit('spinner/SHOW');
			StandardSensorService.readAll(this.address);
		},
		submitEnumerate() {
			this.$store.commit('spinner/SHOW');
			StandardSensorService.enumerate(this.address);
		},
	}
};
</script>

<style scoped>
.scroll-table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
}

td {
	text-align: center;
}
</style>
