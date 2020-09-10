<template>
	<CCard>
		<CCardHeader>{{ $t('iqrfnet.networkManager.discovery.title') }}</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm @submit.prevent='processSubmit'>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='integer|required|txPower'
						:custom-messages='{
							integer: "iqrfnet.networkManager.messages.invalid.discovery.txPower",
							required: "iqrfnet.networkManager.messages.missing.discovery.txPower",
							txPower: "iqrfnet.networkManager.messages.invalid.discovery.txPower"
						}'
					>
						<CInput
							v-model='txPower'
							type='number'
							min='0'
							max='7'
							:label='$t("iqrfnet.networkManager.discovery.form.txPower")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='integer|required|maxAddr'
						:custom-messages='{
							integer: "iqrfnet.networkManager.messages.invalid.discovery.maxAddr",
							required: "iqrfnet.networkManager.messages.missing.discovery.maxAddr",
							maxAddr: "iqrfnet.networkManager.messages.invalid.discovery.maxAddr"
						}'
					>
						<CInput
							v-model='maxAddr'
							type='number'
							min='0'
							max='239'
							:label='$t("iqrfnet.networkManager.discovery.form.maxAddr")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CButton color='primary' type='submit' :disabled='invalid'>
						{{ $t("forms.discovery") }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script>

import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput} from '@coreui/vue';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {integer, required} from 'vee-validate/dist/rules';
import IqmeshNetworkService from '../../services/IqmeshNetworkService';

export default {
	name: 'DiscoveryManager',
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		ValidationObserver,
		ValidationProvider
	},
	data() {
		return {
			maxAddr: 239,
			txPower: 6,
			responseReceived: false
		};
	},
	created() {
		extend('required', required);
		extend('integer', integer);
		extend('txPower', (txPower) => {
			return ((txPower >= 0) && (txPower <= 7));
		});
		extend('maxAddr', (addr) => {
			return ((addr >= 0) && (addr <= 239));
		});
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONSEND' &&
				mutation.payload.mType === 'iqrfEmbedCoordinator_Discovery') {
				setTimeout(() => {this.timedOut();}, 10000);
			}
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (mutation.payload.mType === 'iqrfEmbedCoordinator_Discovery') {
					this.responseReceived = true;
					this.$store.commit('spinner/HIDE');
					switch (mutation.payload.data.status) {
						case 0:
							this.$toast.success(this.$t('iqrfnet.networkManager.messages.submit.discovery.success'));
							break;
						default:
							this.$toast.success(this.$t('iqrfnet.networkManager.messages.submit.discovery.error_fail'));
							break;
					}
				}
			}
		});
	},
	beforeDestroy() {
		this.unsubscribe();
	},
	methods: {
		processSubmit() {
			this.responseReceived = false;
			this.$store.commit('spinner/SHOW');
			IqmeshNetworkService.discovery(this.txPower, this.maxAddr);
		},
		timedOut() {
			if (!this.responseReceived) {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.timeout'));
			}
		}
	}
};
</script>

<style>

</style>