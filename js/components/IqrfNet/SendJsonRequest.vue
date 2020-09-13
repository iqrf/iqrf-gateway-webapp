<template>
	<CCard>
		<CCardHeader>
			<CButton 
				color='primary'
				size='sm' 
				href='https://docs.iqrf.org/iqrf-gateway/api.html'
			>
				{{ $t("iqrfnet.sendJson.documentation") }}
			</CButton>
		</CCardHeader>
	</CCard>
</template>

<script>

import {CButton, CCard, CCardBody, CCardHeader, CForm, CTextarea} from '@coreui/vue';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
export default {
	name: 'SendJsonRequest',
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CTextarea,
	},
	created() {
		extend('json', (json) => {
			try {
				JSON.parse(json);
			} catch (error) {
				return this.$t('iqrfnet.sendJson.invalid');
			}
		});
	}
};
</script>

<style>

</style>