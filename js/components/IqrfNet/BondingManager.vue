<template>
	<CCard>
		<CCardHeader>{{ $t('iqrfnet.networkManager.bonding.title') }}</CCardHeader>
		<CCardBody>
			<CForm @submit.prevent='processSubmit'>
				<CSelect
					:value.sync='bondMethod'
					:label='$t("iqrfnet.networkManager.bonding.form.bondMethod")'
					:options='[
						{value: "local", label: $t("iqrfnet.networkManager.bonding.form.bondMethodLocal")},
						{value: "sc", label: $t("iqrfnet.networkManager.bonding.form.bondMethodSmart")}
					]'
				/>
				<CInput
					v-model='address'
					type='number'
					min='1'
					max='239'
					:label='$t("iqrfnet.networkManager.bonding.form.address")'
					:disabled='autoAddress'
				/>
				<CInputCheckbox
					:checked.sync='autoAddress'
					:label='$t("iqrfnet.networkManager.bonding.form.autoAddress")'
				/>
				<CInput
					v-model='bondingRetries'
					type='number'
					:label='$t("iqrfnet.networkManager.bonding.form.bondingRetries")'
				/>
				<CInput 
					v-if='bondMethod === "sc"'
					v-model='scCode'
					:label='$t("iqrfnet.networkManager.bonding.form.smartConnect")'
				/>
				<CInputCheckbox
					:checked.sync='unbondCoordOnly'
					:label='$t("iqrfnet.networkManager.bonding.form.unbondCoordOnly")'
				/>
				<CButton color='primary' type='submit'>
					{{ $t('forms.bond') }}
				</CButton>
				<CButton color='secondary' type='submit'>
					{{ $t('forms.unbond') }}
				</CButton>
				<CButton color='secondary' type='submit'>
					{{ $t('forms.clearBonds') }}
				</CButton>
			</CForm>
		</CCardBody>
	</CCard>
</template>

<script>
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput, CInputCheckbox, CSelect} from '@coreui/vue';

export default {
	name: 'BondingManager',
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		CInputCheckbox,
		CSelect
	},
	data() {
		return {
			address: 1,
			autoAddress: false,
			bondMethod: 'local',
			bondingRetries: 1,
			unbondCoordOnly: false,
			scCode: ''
		};
	},
	created() {

	},
	methods: {
		processSubmit() {
			//
		}
	}
};
</script>

<style>

</style>