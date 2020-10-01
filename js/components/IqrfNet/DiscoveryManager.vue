<template>
	<CCard class='border-0'>
		<CCardHeader>
			{{ $t('iqrfnet.networkManager.discovery.title') }}
		</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm @submit.prevent='processSubmit'>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='integer|required|between:0,7'
						:custom-messages='{
							integer: "iqrfnet.networkManager.messages.invalid.integer",
							required: "iqrfnet.networkManager.messages.discovery.txPower",
							between: "iqrfnet.networkManager.messages.discovery.txPower"
						}'
					>
						<CInput
							v-model.number='txPower'
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
						rules='integer|required|between:0,239'
						:custom-messages='{
							integer: "iqrfnet.networkManager.messages.invalid.integer",
							required: "iqrfnet.networkManager.messages.discovery.maxAddr",
							between: "iqrfnet.networkManager.messages.discovery.maxAddr"
						}'
					>
						<CInput
							v-model.number='maxAddr'
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
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {timeout} from '../../helpers/timeout';
import {between, integer, required} from 'vee-validate/dist/rules';
import IqrfNetService from '../../services/IqrfNetService';

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
			responseReceived: false,
			timeout: null,
		};
	},
	created() {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONSEND' &&
				mutation.payload.mType === 'iqrfEmbedCoordinator_Discovery') {
				this.timeout = timeout('iqrfnet.networkManager.messages.submit.timeout', 30000);
			}
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (mutation.payload.mType === 'iqrfEmbedCoordinator_Discovery') {
					clearTimeout(this.timeout);
					this.$store.commit('spinner/HIDE');
					switch (mutation.payload.data.status) {
						case -1:
							this.$toast.error(
								this.$t('iqrfnet.networkManager.messages.submit.timeout')
									.toString()
							);
							break;
						case 0:
							this.$toast.success(
								this.$t('iqrfnet.networkManager.messages.submit.discovery.success')
									.toString()
							);
							this.$emit('update-devices');
							break;
						default:
							this.$toast.success(
								this.$t('iqrfnet.networkManager.messages.submit.discovery.error_fail')
									.toString()
							);
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
			this.$store.commit('spinner/SHOW');
			IqrfNetService.discovery(this.txPower, this.maxAddr);
		},
	}
};
</script>
