<template>
	<div>
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
	</div>
</template>

<script>
import {CButton, CCardBody, CCardFooter} from '@coreui/vue/src';

export default {
	name: 'InstallationDisambiguation',
	components: {
		CButton,
		CCardBody,
		CCardFooter
	},
	metaInfo: {
		title: 'install.title'
	}
};
</script>
