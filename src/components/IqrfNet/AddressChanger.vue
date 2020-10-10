<template>
	<CCard>
		<CCardHeader>
			{{ $t('iqrfnet.addressChange.title') }}
		</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm @submit.prevent='changeAddress'>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='integer|required|between:0,239'
						:custom-messages='{
							between: "iqrfnet.addressChange.messages.address",
							integer: "iqrfnet.addressChange.messages.address",
							required: "iqrfnet.addressChange.messages.address",
						}'
					>
						<CInput
							v-model.number='address'
							type='number'
							min='0'
							max='239'
							:label='$t("iqrfnet.addressChange.address")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CButton color='primary' type='submit' :disabled='invalid'>
						{{ $t('forms.read') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import Vue from 'vue';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {between, integer, required} from 'vee-validate/dist/rules';

interface IAddressChanger {
	address: number|null
}

export default Vue.extend({
	name: 'AddressChanger',
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		ValidationObserver,
		ValidationProvider,
	},
	props: {
		currentAddress: {
			type: Number,
			required: true
		},
	},
	data(): IAddressChanger {
		return {
			address: null,
		};
	},
	created() {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		this.address = this.currentAddress;
	},
	methods: {
		changeAddress(): void {
			this.$router.push('/iqrfnet/tr-config/' + this.address);
		},
	},
});
</script>
