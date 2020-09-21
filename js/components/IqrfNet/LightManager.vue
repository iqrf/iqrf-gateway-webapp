<template>
	<CCard>
		<CCardHeader>{{ $t('iqrfnet.standard.light.title') }}</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required|integer|between:1,239'
						:custom-messages='{
							between: "iqrfnet.standard.form.messages.address",
							integer: "forms.messages.integer",
							required: "iqrfnet.standard.form.messages.address"
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
						rules='required|integer|between:0,31'
						:custom-messages='{
							between: "iqrfnet.standard.light.form.messages.index",
							integer: "forms.messages.integer",
							required: "iqrfnet.standard.light.form.messages.index"
						}'
					>
						<CInput
							v-model.number='index'
							type='number'
							min='0'
							max='31'
							:label='$t("iqrfnet.standard.light.form.index")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required|integer|between:0,100'
						:custom-messages='{
							between: "iqrfnet.standard.light.form.messages.power",
							integer: "forms.messages.integer",
							required: "iqrfnet.standard.light.form.messages.power"
						}'
					>
						<CInput
							v-model.number='power'
							type='number'
							min='0'
							max='100'
							:label='$t("iqrfnet.standard.light.form.power")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CButton color='primary' :disabled='invalid' @click.prevent='submitEnumerate'>
						{{ $t('forms.enumerate') }}
					</CButton>
					<CButton color='secondary' :disabled='invalid' @click.prevent='submitGetPower'>
						{{ $t('iqrfnet.standard.light.form.getPower') }}
					</CButton>
					<CButton color='secondary' :disabled='invalid' @click.prevent='submitSetPower'>
						{{ $t('iqrfnet.standard.light.form.setPower') }}
					</CButton>
					<CButton color='secondary' :disabled='invalid' @click.prevent='submitIncrementPower'>
						{{ $t('iqrfnet.standard.light.form.increment') }}
					</CButton>
					<CButton color='secondary' :disabled='invalid' @click.prevent='submitDecrementPower'>
						{{ $t('iqrfnet.standard.light.form.decrement') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
		<CCardFooter v-if='responseType !== null'>
			<table class='table'>
				<thead>
					<span v-if='responseType === "enum"'>{{ $t('iqrfnet.standard.light.enum') }}</span>
					<span v-else>{{ $t('iqrfnet.standard.light.powerInfo') }}</span>
				</thead>
				<tbody v-if='responseType === "enum"'>
					<tr>
						<th>{{ $t('iqrfnet.standard.light.lights') }}</th>
						<td>{{ numLights }}</td>
					</tr>	
				</tbody>
				<tbody v-else>
					<tr>
						<th>{{ $t('iqrfnet.standard.light.index') }}</th>
						<td>{{ index }}</td>
					</tr>
					<tr>
						<th>{{ $t('iqrfnet.standard.light.power') }}</th>
						<td>{{ prevPower }}</td>
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
import {timeout} from '../../helpers/timeout';
import StandardLightService from '../../services/DaemonApi/StandardLightService';

export default {
	name: 'LightManager',
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
				'iqrfLight_Enumerate',
				'iqrfLight_SetPower',
				'iqrfLight_IncrementPower',
				'iqrfLight_DecrementPower'
			],
			index: 0,
			lights: null,
			numLights: 0,
			power: 0,
			prevPower: 0,
			responseType: null,
			timeout: null,
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
						this.$toast.error(this.$t('iqrfnet.standard.light.messages.timeout'));
						break;
					case 0:
						this.$toast.success(this.$t('iqrfnet.standard.light.messages.success'));
						if (mutation.payload.mType === 'iqrfLight_Enumerate') {
							this.numLights = mutation.payload.data.rsp.result.lights;
							this.responseType = 'enum';
						} else {
							this.prevPower = mutation.payload.data.rsp.result.prevVals[0];
							this.responseType = 'power';
						}
						break;
					case 3:
						this.$toast.error(this.$t('iqrfnet.standard.light.messages.pnum'));
						break;
					default:
						this.$toast.error(this.$t('iqrfnet.standard.light.messages.failure'));
						break;
				}
			}
		});
	},
	beforeDestroy() {
		this.unsubscribe();
	},
	methods: {
		submitEnumerate() {
			this.$store.commit('spinner/SHOW');
			StandardLightService.enumerate(this.address);
		},
		submitGetPower() {
			this.$store.commit('spinner/SHOW');
			StandardLightService.getPower(this.address, this.index);
		},
		submitSetPower() {
			this.$store.commit('spinner/SHOW');
			StandardLightService.setPower(this.address, [{'index': this.index, 'power': this.power}]);
		},
		submitIncrementPower() {
			this.$store.commit('spinner/SHOW');
			StandardLightService.incrementPower(this.address, [{'index': this.index, 'power': this.power}]);
		},
		submitDecrementPower() {
			this.$store.commit('spinner/SHOW');
			StandardLightService.decrementPower(this.address, [{'index': this.index, 'power': this.power}]);
		},
	}
};
</script>
