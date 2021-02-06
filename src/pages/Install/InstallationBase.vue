<template>
	<div class='c-app flex-row align-items-center'>
		<CContainer>
			<CRow class='justify-content-center'>
				<CCol lg='10'>
					<div class='py-5'>
						<LogoBlue :alt='$t("core.title")' width='100%' height='32pt' />
					</div>
					<router-view />
				</CCol>
			</CRow>
		</CContainer>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CContainer, CCol, CRow} from '@coreui/vue/src';
import LogoBlue from '../../assets/logo-blue.svg';

@Component({
	components: {
		CContainer,
		CCol,
		CRow,
		LogoBlue,
	},
})

/**
 * Installation base page component
 */
export default class InstallationBase extends Vue {

	/**
	 * Retrieves enabled features
	 */
	created(): void {
		this.$store.dispatch('features/fetch');
	}
}
</script>
