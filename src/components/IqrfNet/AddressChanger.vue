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
							:label='$t("forms.fields.address")'
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
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {between, integer, required} from 'vee-validate/dist/rules';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		ValidationObserver,
		ValidationProvider,
	}
})

/**
 * Address Changer card for TrConfiguration component
 */
export default class AddressChanger extends Vue {
	/**
	 * @var {number|null} device Device address
	 */
	private address: number|null = null

	/**
	 * @property {number} currentAddress Currently selected device address
	 */
	@Prop({required: true}) currentAddress!: number

	@Prop({required: true}) loaded!: boolean 

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		this.address = this.currentAddress;
	}

	/**
	 * Changes device address used to retrieve transciever configuration
	 */
	private changeAddress(): void {
		if (!this.loaded && this.address === this.currentAddress) {
			this.$emit('reload-configuration');
			return;
		}
		this.$router.push('/iqrfnet/tr-config/' + this.address);
	}
}
</script>
