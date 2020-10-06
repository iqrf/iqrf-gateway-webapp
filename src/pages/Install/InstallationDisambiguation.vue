<template>
	<CCard>
		<CCardBody>
			{{ $t('install.invitation') }}
		</CCardBody>
		<CCardFooter>
			<CButton color='primary' to='/install/user'>
				{{ $t('install.createUser.title') }}
			</CButton>
			<CButton color='primary' to='/install/gateway-info'>
				{{ $t('install.gwInfo.button') }}
			</CButton>
		</CCardFooter>
	</CCard>
</template>

<script>
import {CButton, CCard, CCardBody, CCardFooter} from '@coreui/vue/src';

export default {
	name: 'InstallationDisambiguation',
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardFooter
	},
	metaInfo: {
		title: 'install.title'
	}
};
</script>
