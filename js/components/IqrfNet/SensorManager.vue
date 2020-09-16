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
						<td v-for='i in sensors' :key='i.key'>
							{{ i.type }}
						</td>
					</tr>
					<tr v-if='responseType === "enum"'>
						<th>{{ $t('iqrfnet.standard.sensor.unit') }}</th>
						<td v-for='j in sensors' :key='j.key'>
							{{ j.unit }}
						</td>
					</tr>
					<tr v-else-if='responseType === "read"'>
						<th>{{ $t('iqrfnet.standard.sensor.value') }}</th>
						<td v-for='k in sensors' :key='k.key'>
							{{ k.value + ' ' + k.unit }}
						</td>
					</tr>
				</tbody>
			</table>
		</CCardFooter>
	</CCard>
</template>

<script>
import {CButton, CCard, CCardBody, CCardFooter, CCardHeader, CForm, CInput} from '@coreui/vue';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {between, integer, required} from 'vee-validate/dist/rules';
import IqrfStandardService from '../../services/IqrfStandardService';

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
			timeoutVar: null
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
				this.timeoutVar = setTimeout(() => {this.timedOut();}, 10000);
			}
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (!this.allowedMTypes.includes(mutation.payload.mType)) {
					return;
				}
				clearTimeout(this.timeoutVar);
				this.$store.commit('spinner/HIDE');
				switch(mutation.payload.data.status) {
					case -1:
						this.$toast.error(this.$t('iqrfnet.standard.sensor.messages.timeout'));
						break;
					case 0:
						this.$toast.success(this.$t('iqrfnet.standard.sensor.messages.success'));
						if (mutation.payload.mType === 'iqrfSensor_Enumerate') {
							this.parseEnumerate(mutation.payload.data.rsp.result.sensors);
							this.responseType = 'enum';
						} else if (mutation.payload.mType === 'iqrfSensor_ReadSensorsWithTypes') {
							this.$toast.success(this.$t('iqrfnet.standard.sensor.messages.success'));
							this.parseReadAll(mutation.payload.data.rsp.result.sensors);
							this.responseType = 'read';
						}
						break;
					case 3:
						this.$toast.error(this.$t('iqrfnet.standard.sensor.messages.pnum'));
						break;
					default:
						this.$toast.error(this.$t('iqrfnet.standard.sensor.messages.failure'));
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
				if (item.unit === '?') {
					item.unit = '-';
				}
				this.sensors.push({'type': item.name, 'unit': item.unit});
			});
		},
		parseReadAll(sensors) {
			this.sensors = [];
			sensors.forEach(item => {
				if (item.unit === '?') {
					item.unit = '';
				}
				this.sensors.push({'type': item.name, 'value': item.value, 'unit': item.unit});
			});
		},
		submitReadAll() {
			this.$store.commit('spinner/SHOW');
			IqrfStandardService.sensorReadAll(this.address);
		},
		submitEnumerate() {
			this.$store.commit('spinner/SHOW');
			IqrfStandardService.sensorEnumerate(this.address);
		},
		timedOut() {
			this.$store.commit('spinner/HIDE');
			this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.timeout'));
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
