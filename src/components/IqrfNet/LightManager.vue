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
					<CButton
						color='primary'
						:disabled='invalid'
						@click.prevent='submitEnumerate'
					>
						{{ $t('forms.enumerate') }}
					</CButton>
					<CButton
						color='secondary'
						:disabled='invalid'
						@click.prevent='submitGetPower'
					>
						{{ $t('iqrfnet.standard.light.form.getPower') }}
					</CButton>
					<CButton
						color='secondary'
						:disabled='invalid'
						@click.prevent='submitSetPower'
					>
						{{ $t('iqrfnet.standard.light.form.setPower') }}
					</CButton>
					<CButton
						color='secondary'
						:disabled='invalid'
						@click.prevent='submitIncrementPower'
					>
						{{ $t('iqrfnet.standard.light.form.increment') }}
					</CButton>
					<CButton
						color='secondary'
						:disabled='invalid'
						@click.prevent='submitDecrementPower'
					>
						{{ $t('iqrfnet.standard.light.form.decrement') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
		<CCardFooter v-if='responseType !== null'>
			<table class='table'>
				<thead v-if='responseType === "enum"'>
					{{ $t('iqrfnet.standard.light.enum') }}
				</thead>
				<thead v-else>
					{{ $t('iqrfnet.standard.light.powerInfo') }}
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
						<td>{{ lightIndex }}</td>
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

<script lang='ts'>
import Vue from 'vue';
import {MutationPayload} from 'vuex';
import {CButton, CCard, CCardBody, CCardFooter, CCardHeader, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {between, integer, required} from 'vee-validate/dist/rules';
import StandardLightService, {StandardLight} from '../../services/DaemonApi/StandardLightService';
import { WebSocketOptions } from '../../store/modules/webSocketClient.module';

export default Vue.extend({
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
	data(): any {
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
			lightIndex: 0,
			responseType: null,
			msgId: null,
		};
	},
	created() {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'SOCKET_ONSEND') {
				this.lightIndex = this.index;
				return;
			}
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
							this.$t('iqrfnet.standard.light.messages.timeout').toString()
						);
						break;
					case 0:
						this.$toast.success(
							this.$t('iqrfnet.standard.light.messages.success').toString()
						);
						if (mutation.payload.mType === 'iqrfLight_Enumerate') {
							this.numLights = mutation.payload.data.rsp.result.lights;
							this.responseType = 'enum';
						} else {
							this.prevPower = mutation.payload.data.rsp.result.prevVals[0];
							this.responseType = 'power';
						}
						break;
					case 3:
						this.$toast.error(
							this.$t('iqrfnet.standard.light.messages.pnum').toString()
						);
						break;
					default:
						this.$toast.error(
							this.$t('iqrfnet.standard.light.messages.failure').toString()
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
		buildOptions() {
			return new WebSocketOptions(null, 30000, 'iqrfnet.standard.light.messages.timeout', () => this.msgId = null);
		},
		submitEnumerate() {
			this.$store.dispatch('spinner/show', {timeout: 30000});
			StandardLightService.enumerate(this.address, this.buildOptions())
				.then((msgId: string) => this.msgId = msgId);
		},
		submitGetPower() {
			this.$store.dispatch('spinner/show', {timeout: 30000});
			StandardLightService.getPower(this.address, this.index, this.buildOptions())
				.then((msgId: string) => this.msgId = msgId);
		},
		submitSetPower() {
			this.$store.dispatch('spinner/show', {timeout: 30000});
			StandardLightService.setPower(this.address, [new StandardLight(this.index, this.power)], this.buildOptions())
				.then((msgId: string) => this.msgId = msgId);
		},
		submitIncrementPower() {
			this.$store.dispatch('spinner/show', {timeout: 30000});
			StandardLightService.incrementPower(this.address, [new StandardLight(this.index, this.power)], this.buildOptions())
				.then((msgId: string) => this.msgId = msgId);
		},
		submitDecrementPower() {
			this.$store.dispatch('spinner/show', {timeout: 30000});
			StandardLightService.decrementPower(this.address, [new StandardLight(this.index, this.power)], this.buildOptions())
				.then((msgId: string) => this.msgId = msgId);
		},
	}
});
</script>

<style scoped>

.btn {
  margin: 0 3px 0 0;
}

</style>
