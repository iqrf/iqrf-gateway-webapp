<template>
	<CCard>
		<CCardBody>
			{{ $t('install.invitation') }}
		</CCardBody>
		<CCardFooter>
			<CButton color='primary' to='/install/user'>
				{{ $t('install.createUser.title') }}
			</CButton> <CButton color='primary' to='/install/gateway-info'>
				{{ $t('install.gwInfo.button') }}
			</CButton>
		</CCardFooter>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardFooter} from '@coreui/vue/src';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardFooter
	},
	metaInfo: {
		title: 'install.title'
	},
})

/**
 * Installation disambiguation menu component
 */
export default class InstallationDisambiguation extends Vue {
}
</script>
