<template>
	<div class='c-app flex-row align-items-center'>
		<CContainer>
			<CRow class='justify-content-center'>
				<CCol lg='10'>
					<div class='py-5'>
						<LogoBlue :alt='$t("core.title")' width='100%' height='32pt' />
					</div>
					<CCard class='p-4'>
						<router-view />
					</CCard>
				</CCol>
			</CRow>
		</CContainer>
	</div>
</template>

<script>
import {CContainer, CCard, CCol, CRow} from '@coreui/vue/src';
import LogoBlue from '../../assets/logo-blue.svg';

export default {
	name: 'InstallationBase',
	components: {
		CContainer,
		CCard,
		CCol,
		CRow,
		LogoBlue,
	},
};
</script>
