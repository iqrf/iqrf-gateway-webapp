<template>
	<CCard>
		<CCardHeader>{{ $t('iqrfnet.networkManager.discovery.title') }}</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm @submit.prevent='processSubmit'>
					<ValidationProvider
						v-slot='{valid, errors}'
						rules='integer|required|txPower'
						:custom-messages='{
							integer: "iqrfnet.networkManager.discovery.messages.invalid.txPower",
							required: "iqrfnet.networkManager.discovery.messages.missing.txPower",
							txPower: "iqrfnet.networkManager.discovery.messages.invalid.txPower"
						}'
					>
						<CInput
							v-model='txPower'
							type='number'
							min='0'
							max='7'
							:label='$t("iqrfnet.networkManager.discovery.form.txPower")'
							:is-valid='valid'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{valid, errors}'
						rules='integer|required|maxAddr'
						:custom-messages='{
							integer: "iqrfnet.networkManager.discovery.messages.invalid.maxAddr",
							required: "iqrfnet.networkManager.discovery.messages.missing.maxAddr",
							maxAddr: "iqrfnet.networkManager.discovery.messages.invalid.maxAddr"
						}'
					>
						<CInput
							v-model='maxAddr'
							type='number'
							min='0'
							max='239'
							:label='$t("iqrfnet.networkManager.discovery.form.maxAddr")'
							:is-valid='valid'
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
			txPower: 6
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
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (mutation.payload.mType === 'iqrfEmbedCoordinator_Discovery') {
					if (mutation.payload.data.status === 0) {
						this.$toast.success(this.$t('iqrfnet.networkManager.discovery.messages.success'));
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
			IqmeshNetworkService.discovery(this.txPower, this.maxAddr);
		}
	}
};
</script>

<style>

</style>